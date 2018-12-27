<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/28 0028
 * Time: 11:27
 */

namespace Swokit\WebSocket\Server;

/**
 * Class Application
 * @package Swokit\WebSocket\Server
 */
class Application
{
    public const DATA_JSON = 'json';
    public const DATA_TEXT = 'text';

    public const ON_NO_MODULE = 'noModule';

    use WebSocketApplicationTrait;
}
