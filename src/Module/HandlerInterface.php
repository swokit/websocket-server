<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace SwoKit\WebSocket\Server\Module;

/**
 * Interface HandlerInterface
 * @package SwoKit\WebSocket\Server\Module
 */
interface HandlerInterface
{
    public function run(string $command);
}
