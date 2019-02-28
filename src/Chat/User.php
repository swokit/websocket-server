<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/9/3
 * Time: 上午1:16
 */

namespace Swokit\WebSocket\Server\Chat;

/**
 * Class User
 * @package Swokit\WebSocket\Server\Chat
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
    public function getCid(): int
    {
        return $this->cid;
    }

    /**
     * @param int $cid
     */
    public function setCid($cid): void
    {
        $this->cid = $cid;
    }


}
