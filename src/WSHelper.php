<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018-12-28
 * Time: 01:09
 */

namespace Swokit\WebSocket\Server;

use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Class WSHelper
 * @package Swokit\WebSocket\Server
 */
class WSHelper
{
    public const WS_VERSION    = 13;
    public const WS_KEY_PATTEN = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    public const SIGN_KEY      = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * Generate webSocket sign.(for server)
     * @param string $key
     * @return string
     */
    public static function genSign(string $key): string
    {
        return \base64_encode(\sha1(\trim($key) . self::SIGN_KEY, true));
    }

    /**
     * @param string $secWSKey 'sec-websocket-key: xxxx'
     * @return bool
     */
    public static function isInvalidSecKey($secWSKey): bool
    {
        return 0 === \preg_match(self::WS_KEY_PATTEN, $secWSKey) || 16 !== \strlen(\base64_decode($secWSKey));
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return bool
     */
    public static function quickHandshake(Request $request, Response $response): bool
    {
        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten          = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';

        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== \strlen(\base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }

        // echo $request->header['sec-websocket-key'];
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));

        $headers = [
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        $response->status(101);
        $response->end();

        return true;
    }
}
