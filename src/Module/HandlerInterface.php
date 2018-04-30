<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace SwooleKit\WebSocket\Server\Module;

/**
 * Interface HandlerInterface
 * @package SwooleKit\WebSocket\Server\Module
 */
interface HandlerInterface
{
    public function run(string $command);
}
