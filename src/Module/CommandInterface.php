<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace SwoKit\WebSocket\Server\Module;

/**
 * Interface CommandInterface
 * @package SwoKit\WebSocket\Server\Module
 */
interface CommandInterface
{
    public function __invoke();
}
