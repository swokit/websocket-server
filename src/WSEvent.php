<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018-12-28
 * Time: 00:36
 */

namespace Swokit\WebSocket\Server;

/**
 * Class WSEvent
 * @package Swokit\WebSocket\Server
 */
final class WSEvent
{
    // for ws request
    public const WS_CONNECT        = 'ws.connect';
    public const WS_OPEN           = 'ws.open';
    public const WS_DISCONNECT     = 'ws.disconnect';
    public const HANDSHAKE_REQUEST = 'ws.handshake';
    public const HANDSHAKE_SUCCESS = 'ws.handshakeOk';
    public const WS_MESSAGE        = 'ws.message';
    public const WS_CLOSE          = 'ws.close';
    public const WS_ERROR          = 'ws.error';

    // for ws application
    public const NO_MODULE   = 'app.noModule';
    public const PARSE_ERROR = 'app.parseError';

}
