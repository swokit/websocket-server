<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-01
 * Time: 9:49
 */

namespace SwooleLib\WebSocket\Module;

/**
 * Interface CommandInterface
 * @package SwooleLib\WebSocket\Module
 */
interface CommandInterface
{
    public function __invoke();
}
