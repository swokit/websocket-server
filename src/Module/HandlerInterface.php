<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace SwooleLib\WebSocket\Module;

/**
 * Interface HandlerInterface
 * @package SwooleLib\WebSocket\Module
 */
interface HandlerInterface
{
    public function run(string $command);
}
