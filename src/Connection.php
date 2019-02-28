<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-08-25
 * Time: 16:18
 */

namespace Swokit\WebSocket\Server;

use Swokit\Http\Server\HttpContext;
use Swoole\Http\Request as SwRequest;
use Swoole\Http\Response as SwResponse;
use Toolkit\ObjUtil\Obj;

/**
 * Class Connection - client connection metadata
 * @package Swokit\WebSocket\Server
 */
class Connection extends HttpContext
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $path = '/';

    /**
     * @var int
     */
    private $connectTime;

    /**
     * @var bool
     */
    private $handshake = false;

    /**
     * @var int
     */
    private $handshakeTime = 0;

    /**
     * class constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::configure($this, $config);

        parent::__construct();

        $this->connectTime = time();

        // \Sws::getConnectionManager()->add($this);
    }

    /**
     * destroy
     */
    public function destroy(): void
    {
        // \Sws::getConnectionManager()->del($this->getId());
        $this->connectTime = 0;

        parent::destroy();
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestResponse(SwRequest $swRequest, SwResponse $swResponse): void
    {
        $this->setKey(self::genKey($swRequest->fd));

        parent::setRequestResponse($swRequest, $swResponse);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return \array_merge(parent::all(), [
            'ip'            => $this->ip,
            'port'          => $this->port,
            'path'          => $this->path,
            'handshake'     => $this->handshake,
            'connectTime'   => $this->connectTime,
            'handshakeTime' => $this->handshakeTime,
        ]);
    }

    /**
     * handshake
     */
    public function handshake(): void
    {
        $this->path          = $this->request->getPath();
        $this->handshake     = true;
        $this->handshakeTime = \time();
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getConnectTime(): int
    {
        return $this->connectTime;
    }

    /**
     * @param int $connectTime
     */
    public function setConnectTime(int $connectTime): void
    {
        $this->connectTime = $connectTime;
    }

    /**
     * @return bool
     */
    public function isHandshake(): bool
    {
        return $this->handshake;
    }

    /**
     * @param bool $handshake
     */
    public function setHandshake($handshake): void
    {
        $this->handshake = (bool)$handshake;
    }

    /**
     * @return int
     */
    public function getHandshakeTime(): int
    {
        return $this->handshakeTime;
    }
}
