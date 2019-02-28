<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-08-25
 * Time: 10:53
 */

namespace Swokit\WebSocket\Server;

use Swokit\Http\Server\Util\Psr7Http;
use Swoole\Http\Request as SwRequest;
use Swoole\Http\Response as SwResponse;
use Swoole\Websocket\Frame;
use Swoole\Websocket\Server;

/**
 * Class WebSocketServer
 * @package Swokit\WebSocket\Server
 * property-read \Swoole\WebSocket\Server $server
 */
trait WebSocketServerTrait
{
    /**
     * 连接的客户端id列表
     * @var int[]
     * [
     *  connection id => connection id
     * ]
     */
    protected $ids = [];

    /**
     * 连接的客户端信息列表
     * @var Connection[]
     * [
     *  cid => Connection
     * ]
     */
    private $connections = [];

    /** @var array */
    protected $messageParsers = [];

////////////////////// WS Server event //////////////////////

    /**
     * 这里还无法判断是否是 webSocket 或者 http
     * @param Server $server
     * @param int $fd
     * @param $fromId
     */
    public function onConnect($server, $fd, $fromId): void
    {
//        $info = $this->getClientInfo($fd);
//        $ctxKey = ServerHelper::genKey($fd);
//        $this->log("onConnect: context ID: $ctxKey, connection ID: $fd, form reactor ID: $fromId, info: " . var_export($info, 1));

        // 触发 connect 事件回调
        $this->fire(WSEvent::WS_CONNECT, $server, $fd, $fromId);
    }

    /**
     * webSocket 建立连接后进行握手。WebSocket服务器已经内置了handshake，
     * 如果用户希望自己进行握手处理，可以设置 onHandShake 事件回调函数。
     * @param SwRequest $swRequest
     * @param SwResponse $swResponse
     * @return bool
     */
    public function onHandShake(SwRequest $swRequest, SwResponse $swResponse): bool
    {
//        return $this->simpleHandshake($swRequest, $swResponse);

        $cid = $swRequest->fd;
        $info = $this->getClientInfo($cid);

        $this->log("onHandShake: Client #{$swRequest->fd} send handShake request, connection info: " . var_export($info, 1));

        $metaAry = [
            'id' => $cid,
            'ip' => $info['remote_ip'],
            'port' => $info['remote_port'],
            'path' => '/',
            'handshake' => false,
            'connectTime' => $info['connect_time'],
        ];

        // 初始化客户端信息
        $meta = new Connection($metaAry);
        $meta->setRequestResponse($swRequest, $swResponse);

        $request = $meta->getRequest();
        $secKey = $request->getHeaderLine('sec-websocket-key');

        $this->log("Handshake: Ready to shake hands with the #$cid client connection. request info:\n" . $request->toString());

        // sec-websocket-key 错误
        if (!$this->validateHeaders($cid, $secKey, $swResponse)) {
            return false;
        }

        $response = $meta->getResponse();
        $this->fire(WSEvent::HANDSHAKE_REQUEST, $request, $response, $cid);

        // 如果返回 false -- 拒绝连接，比如需要认证，限定路由，限定ip，限定domain等
        // 就停止继续处理。并返回信息给客户端
        if (false === $this->handleHandshake($request, $response, $cid)) {
            $this->log("The #$cid client handshake's callback return false, will close the connection");

            Psr7Http::respond($response, $swResponse);
            return false;
        }

        // setting response
        $response
            ->setStatus(101)
            ->setHeaders([
                'Upgrade' => 'websocket',
                'Connection' => 'Upgrade',
                'Sec-WebSocket-Accept' => WSHelper::genSign($secKey),
                'Sec-WebSocket-Version' => self::WS_VERSION,
            ]);

        if (isset($swRequest->header['sec-websocket-protocol'])) {
            $response->setHeader('Sec-WebSocket-Protocol', $swRequest->header['sec-websocket-protocol']);
        }
        $this->log("Handshake: response info:\n" . $response->toString());

        // 响应握手成功
        Psr7Http::respond($response, $swResponse);

        // 标记已经握手 更新路由 path
        $meta->handshake();
        $this->ids[$cid] = $cid;
        $this->connections[$cid] = $meta;

        $this->log("Handshake: The #{$cid} client handshake successful! ctxKey: {$meta->getKey()}, Meta:\n" . var_export($meta->all(), 1));
        $this->fire(WSEvent::HANDSHAKE_SUCCESS, $request, $response, $cid);
        $this->afterHandshake($meta);

        // 握手成功 触发 open 事件
        $this->server->defer(function () use ($swRequest) {
            $this->onOpen($this->server, $swRequest);
        });
        return true;
    }

    /**
     * custom handshake check.
     * @param $request
     * @param $response
     * @param $cid
     * @return bool
     */
    abstract protected function handleHandshake($request, $response, $cid): bool;

    /**
     * @param Connection $conn
     */
    protected function afterHandshake(Connection $conn): void
    {
        // ....
    }

    /**
     * webSocket 连接上时
     * @param  Server $server
     * @param  SwRequest $request
     */
    public function onOpen($server, SwRequest $request): void
    {
        $cid = $request->fd;
        $conn = $this->connections[$cid];

        $this->log("onOpen: The #{$cid} client open successful! ctxKey: {$conn->getKey()}");
        $this->fire(WSEvent::WS_OPEN, $this, $conn);

        $this->afterOpen($server, $conn);
    }

    /**
     * @param Server $server
     * @param Connection $conn
     */
    protected function afterOpen($server, Connection $conn): void
    {
        $server->push($conn->getId(), "hello, welcome\n");
    }

    /**
     * webSocket 收到消息时
     * @param  Server $server
     * @param  Frame $frame
     */
    public function onMessage($server, Frame $frame): void
    {
        $fd = $frame->fd;
        $conn = $this->connections[$fd];

        $this->log("onMessage: The #{$fd} client send a message to server, ctxKey: {$conn->getKey()}, data: {$frame->data}");

        // send message to all
        // $this->broadcast($server, $frame->data);

        // send message to fd.
        // $server->push($fd, "server: {$frame->data}");

        $this->handleWsMessage($server, $frame, $conn);
    }

    /**
     * @param Server $server
     * @param Frame $frame
     * @param Connection $conn
     */
    protected function handleWsMessage($server, Frame $frame, Connection $conn): void
    {
    }

    /**
     * webSocket断开连接
     * @param  Server $server
     * @param  int $fd
     */
    public function onClose($server, $fd): void
    {
        /*
        WEBSOCKET_STATUS_CONNECTION = 1，连接进入等待握手
        WEBSOCKET_STATUS_HANDSHAKE = 2，正在握手
        WEBSOCKET_STATUS_FRAME = 3，已握手成功等待浏览器发送数据帧
        */
        $fdInfo = $this->getClientInfo($fd);

        // is web socket request(websocket_status = 2)
        if ($fdInfo['websocket_status'] > 0) {
            $meta = $this->delConnection($fd);

            if (!$meta) {
                $this->log("the #$fd connection info has lost");

                return;
            }

            // call on close callback
            $this->fire(WSEvent::WS_CLOSE, $this, $fd, $meta);
            $this->logf(
                'onClose: The #%d client has been closed! workerId: %s ctxKey:%s, From %s:%d. Count: %d',
                $fd, $server->worker_id, $meta->getKey(), $meta['ip'], $meta['port'], $this->count()
            );
            $this->log("onClose: Client #{$fd} is closed. client-info:\n" . \var_export($fdInfo, 1));
        }
    }

    /*******************************************************************************
     * helpers
     ******************************************************************************/

    /**
     * @param int $cid
     * @param string $secKey
     * @param SwResponse $swResponse
     * @return bool
     */
    protected function validateHeaders($cid, $secKey, SwResponse $swResponse): bool
    {
        // sec-websocket-key 错误
        if (WSHelper::isInvalidSecKey($secKey)) {
            $this->log("handshake failed with client #{$cid}! [Sec-WebSocket-Key] not found OR is error in header.", [], 'error');

            $swResponse->status(404);
            $swResponse->write('<b>400 Bad Request</b><br>[Sec-WebSocket-Key] not found in request header.');
            $swResponse->end();
            return false;
        }

        return true;
    }

    /**
     * @param $cid
     */
    public function close($cid): void
    {
        $this->server->close($cid);
    }

////////////////////////////////////////////////////////////////////////
/// message send methods
////////////////////////////////////////////////////////////////////////

    /**
     * check it a accepted client and handshake completed  client
     * @param int $cid
     * @return bool
     */
    public function isHandshake(int $cid): bool
    {
        if ($this->hasConnection($cid)) {
            return $this->getConnection($cid)['handshake'];
        }

        return false;
    }

    /**
     * count handshake clients
     * @return int
     */
    public function countHandshake(): int
    {
        $count = 0;

        foreach ($this->connections as $info) {
            if ($info['handshake']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     *  check it is a exists client
     * @notice maybe don't complete handshake
     * @param $cid
     * @return bool
     */
    public function hasConnection(int $cid): bool
    {
        return isset($this->connections[$cid]);
    }

    /**
     * @param int $cid
     * @return bool|Connection
     */
    public function getConnection(int $cid)
    {
        if ($this->hasConnection($cid)) {
            return $this->connections[$cid];
        }

        return false;
    }

    /**
     * @param int $cid
     * @return bool|Connection
     */
    public function delConnection(int $cid)
    {
        if ($this->hasConnection($cid)) {
            $meta = $this->connections[$cid];

            unset($this->connections[$cid], $this->ids[$cid]);

            return $meta;
        }

        return false;
    }

////////////////////////////////////////////////////////////////////////
/// message send methods
////////////////////////////////////////////////////////////////////////

    /**
     * send message to client(s)
     * @param string $data
     * @param int|array $receivers
     * @param int|array $expected
     * @param int $sender
     * @return int
     */
    public function send(string $data, $receivers = 0, $expected = 0, int $sender = 0): int
    {
        if (!$data) {
            return 0;
        }

        $receivers = (array)$receivers;
        $expected = (array)$expected;

        // only one receiver
        if (1 === \count($receivers)) {
            return $this->sendTo(\array_shift($receivers), $data, $sender);
        }

        // to all
        if (!$expected && !$receivers) {
            $this->sendToAll($data, $sender);

            // to some
        } else {
            $this->sendToSome($data, $receivers, $expected, $sender);
        }

        return $this->getErrorNo();
    }

    /**
     * Send a message to the specified user 发送消息给指定的用户
     * @param int $receiver 接收者
     * @param string $data
     * @param int $sender 发送者
     * @return int
     */
    public function sendTo(int $receiver, string $data, int $sender = -1): int
    {
        $finish = true;
        $opcode = 1;
        $fromUser = $sender < 0 ? 'SYSTEM' : $sender;
        $this->log("(private)The #{$fromUser} send message to the user #{$receiver}. Data: {$data}");

        return $this->server->push($receiver, $data, $opcode, $finish) ? 0 : -500;
    }

    /**
     * broadcast message 广播消息
     * @param string $data 消息数据
     * @param int $sender 发送者
     * @param int[] $receivers 指定接收者们
     * @param int[] $expected 要排除的接收者
     * @return int   Return socket last error number code.  gt 0 on failure, eq 0 on success
     */
    public function broadcast(string $data, array $receivers = [], array $expected = [], int $sender = -1): int
    {
        if (!$data) {
            return 0;
        }

        // only one receiver
        if (1 === \count($receivers)) {
            return $this->sendTo(array_shift($receivers), $data, $sender);
        }

        // to all
        if (!$expected && !$receivers) {
            $this->sendToAll($data, $sender);

            // to some
        } else {
            $this->sendToSome($data, $receivers, $expected, $sender);
        }

        return $this->getErrorNo();
    }

    /**
     * @param string $data
     * @param int $sender
     * @return int
     */
    public function sendToAll(string $data, int $sender = -1): int
    {
        $startFd = 0;
        $count = 0;
        $fromUser = $sender < 1 ? 'SYSTEM' : $sender;

        $this->log("(broadcast)The #{$fromUser} send a message to all users. Data: {$data}");

        while (true) {
            $connList = $this->server->connection_list($startFd, 50);

            if ($connList === false || ($num = \count($connList)) === 0) {
                break;
            }

            $count += $num;
            $startFd = end($connList);

            /** @var $connList array */
            foreach ($connList as $fd) {
                $info = $this->getClientInfo($fd);

                if ($info && $info['websocket_status'] > 0) {
                    $this->server->push($fd, $data);
                }
            }
        }

        return $count;
    }

    /**
     * @param string $data
     * @param array $receivers
     * @param array $expected
     * @param int $sender
     * @return int
     */
    public function sendToSome(string $data, array $receivers = [], array $expected = [], int $sender = 0): int
    {
        $count = 0;
        $res = $data;
        $len = \strlen($res);
        $fromUser = $sender < 1 ? 'SYSTEM' : $sender;

        // to receivers
        if ($receivers) {
            $this->log("(broadcast)The #{$fromUser} gave some specified user sending a message. Data: {$data}");

            foreach ($receivers as $receiver) {
                if ($this->hasConnection($receiver)) {
                    $count++;
                    $this->server->push($receiver, $res, $len);
                }
            }

            return $count;
        }

        // to special users
        $startFd = 0;
        $this->log("(broadcast)The #{$fromUser} send the message to everyone except some people. Data: {$data}");

        while (true) {
            $connList = $this->server->connection_list($startFd, 50);

            if ($connList === false || ($num = \count($connList)) === 0) {
                break;
            }

            $count += $num;
            $startFd = end($connList);

            /** @var $connList array */
            foreach ($connList as $fd) {
                if (isset($expected[$fd])) {
                    continue;
                }

                if ($receivers && !isset($receivers[$fd])) {
                    continue;
                }

                $this->server->push($fd, $data);
            }
        }

        return $count;
    }

    /**
     * response data to client by socket connection
     * @param int $fd
     * @param string $data
     * param int $length
     * @return int   Return error number code. gt 0 on failure, eq 0 on success
     */
    public function writeTo($fd, string $data): int
    {
        return $this->server->send($fd, $data) ? 0 : 1;
    }

    /**
     * @param int $cid
     * @return bool
     */
    public function exist(int $cid): bool
    {
        return $this->server->exist($cid);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->ids);
    }

////////////////////////////////////////////////////////////////////////
/// get/set methods
////////////////////////////////////////////////////////////////////////

    /**
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param Connection[] $connections
     */
    public function setConnections(array $connections): void
    {
        $this->connections = $connections;
    }


}
