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
    const DATA_JSON = 'json';
    const DATA_TEXT = 'text';

    const ON_NO_MODULE = 'noModule';

    use WebSocketApplicationTrait;
}
