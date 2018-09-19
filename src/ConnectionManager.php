<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/9/3
 * Time: 上午1:09
 */

namespace SwoKit\WebSocket\Server;

use SwoKit\Context\ContextManager;

/**
 * Class ConnectionManager
 * @package SwoKit\WebSocket\Server
 */
class ConnectionManager extends ContextManager
{
    /**
     * @return int|string
     */
    protected function getDefaultId()
    {
        // return Coroutine::tid();
    }

    /**
     * @return array
     */
    public function getConnections()
    {
        return $this->getContextList();
    }
}
