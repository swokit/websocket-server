<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-02-24
 * Time: 16:04
 */

namespace Swokit\WebSocket\Server;

use Swokit\Http\Server\HttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server as SwServer;
use Swoole\Websocket\Frame;
use Swoole\Websocket\Server;

/**
 * Class WebSocketServer
 * @package Swokit\Server\BuiltIn
 */
class WebSocketServer extends HttpServer implements WebSocketServerInterface
{
    use WebSocketServerTrait;

    public const OPCODE_CONTINUATION_FRAME = 0x0;
    public const OPCODE_TEXT_FRAME = 0x1;
    public const OPCODE_BINARY_FRAME = 0x2;
    public const OPCODE_CONNECTION_CLOSE = 0x8;
    public const OPCODE_PING = 0x9;
    public const OPCODE_PONG = 0xa;

    public const CLOSE_NORMAL = 1000;
    public const CLOSE_GOING_AWAY = 1001;
    public const CLOSE_PROTOCOL_ERROR = 1002;
    public const CLOSE_DATA_ERROR = 1003;
    public const CLOSE_STATUS_ERROR = 1005;
    public const CLOSE_ABNORMAL = 1006;
    public const CLOSE_MESSAGE_ERROR = 1007;
    public const CLOSE_POLICY_ERROR = 1008;
    public const CLOSE_MESSAGE_TOO_BIG = 1009;
    public const CLOSE_EXTENSION_MISSING = 1010;
    public const CLOSE_SERVER_ERROR = 1011;
    public const CLOSE_TLS = 1015;

    public const WEBSOCKET_VERSION = 13;

    public const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * frame list
     * @var array
     */
    public $frames = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config = [])
    {
        $this->options['response'] = [
            'gzip' => true,
            'keep_alive' => 1,
            'heart_time' => 1,
            'max_connect' => 10000,
            'max_frame_size' => 2097152,
        ];

        parent::__construct($config);
    }

    /**
     * {@inheritDoc}
     */
    // public function init()
    // {
    //     parent::init();
    // }

    ////////////////////// WS Server event //////////////////////

    /**
     * webSocket 连接上时
     * @param  Server $server
     * @param  Request $request
     */
    public function onOpen(Server $server, Request $request)
    {
        $this->log("onOpen: Client [fd:{$request->fd}] open connection.");

        // var_dump($request->fd, $request->get, $request->server);
        $server->push($request->fd, "hello, welcome\n");
    }

    /**
     * webSocket 收到消息时
     * @param  Server $server
     * @param  Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $this->log("onMessage: Client [fd:{$frame->fd}] send message: {$frame->data}");

        // send message to all
        // $this->broadcast($server, $frame->data);

        // send message to fd.
        $server->push($frame->fd, "server: {$frame->data}");
    }

    /**
     * webSocket 建立连接后进行握手。WebSocket服务器已经内置了handshake，
     * 如果用户希望自己进行握手处理，可以设置 onHandShake 事件回调函数。
     * 注意：设置了 onHandShake 处理后，不会再触发 onOpen
     * @param  Server $server
     * @param           $frame
     */
    public function onHandShake(Server $server, $frame)
    {
        $this->log("[fd: {$frame->fd}] Message: {$frame->data}");

        $this->server->defer([$this, 'onOpen']);
    }

    /**
     * webSocket断开连接
     * @param  Server $server
     * @param  int $fd
     */
    public function onClose($server, $fd)
    {
        /*
        返回数据：
        "websocket_status":0, // 此状态可以判断是否为WebSocket客户端。
        "server_port":9501,
        "server_fd":4,
        "socket_type":1,
        "remote_port":56554,
        "remote_ip":"127.0.0.1",
        "from_id":2,
        "connect_time":1487940465,
        "last_time":1487940465,
        "close_errno":0

        WEBSOCKET_STATUS_CONNECTION = 1，连接进入等待握手
        WEBSOCKET_STATUS_HANDSHAKE = 2，正在握手
        WEBSOCKET_STATUS_FRAME = 3，已握手成功等待浏览器发送数据帧
        */
        $fdInfo = $server->connection_info($fd);

        // is socket request
        if ($fdInfo['websocket_status'] > 0) {
            $this->log("onClose: Client #{$fd} is closed", $fdInfo);
        }
    }

    /**
     * custom handshake check.
     * @param $request
     * @param $response
     * @param $cid
     * @return bool
     */
    protected function handleHandshake($request, $response, $cid)
    {
        // TODO: Implement handleHandshake() method.
    }
}
