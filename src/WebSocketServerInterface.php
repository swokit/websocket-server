<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-08-28
 * Time: 13:49
 */

namespace Swokit\WebSocket\Server;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Interface WsServerInterface
 * @package Swokit\WebSocket\Server
 */
interface WebSocketServerInterface
{
    public const WS_VERSION    = 13;
    public const WS_KEY_PATTEN = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    public const SIGN_KEY      = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public const HANDSHAKE_OK   = 0;
    public const HANDSHAKE_FAIL = 25;

    /**
     * webSocket 连接上时
     * @param  Server  $server
     * @param  Request $request
     */
    public function onOpen(Server $server, Request $request): void;

    /**
     * webSocket 收到消息时
     * @param  Server $server
     * @param  Frame  $frame
     */
    public function onMessage(Server $server, Frame $frame): void;

    /**
     * webSocket 建立连接后进行握手。WebSocket服务器已经内置了handshake，
     * 如果用户希望自己进行握手处理，可以设置 onHandShake 事件回调函数。
     * 注意：设置了 onHandShake 处理后，不会再触发 onOpen
     * @param Request  $swRequest
     * @param Response $swResponse
     * @return mixed
     */
    public function onHandShake(Request $swRequest, Response $swResponse): void;

    /**
     * send message to client(s)
     * @param string    $data
     * @param int|array $receivers
     * @param int|array $expected
     * @param int       $sender
     * @return int
     */
    public function send(string $data, $receivers = 0, $expected = 0, int $sender = 0): int;
}
