<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/9/3
 * Time: 上午1:16
 */

namespace SwooleKit\WebSocket\Server\Chat;

/**
 * Class User
 * @package SwooleKit\WebSocket\Server\Chat
 */
class User
{
    /**
     * @var int
     */
    private $cid;

    /**
     * @return int
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * @param int $cid
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    }


}
