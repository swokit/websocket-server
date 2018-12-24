<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace Swokit\WebSocket\Server\Module;

/**
 * Interface HandlerInterface
 * @package Swokit\WebSocket\Server\Module
 */
interface HandlerInterface
{
    public function run(string $command);
}
