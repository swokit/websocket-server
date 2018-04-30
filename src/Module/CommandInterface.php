<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace SwooleKit\WebSocket\Server\Module;

/**
 * Interface CommandInterface
 * @package SwooleKit\WebSocket\Server\Module
 */
interface CommandInterface
{
    public function __invoke();
}
