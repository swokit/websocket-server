<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/1/28 0028
 * Time: 11:19
 */

namespace SwooleKit\WebSocket\Server;

use Toolkit\PhpUtil\PhpHelper;
use Inhere\Http\Response;
use Inhere\Http\ServerRequest;
use SwooleKit\WebSocket\Server\Module\ModuleInterface;

/**
 * Trait WebSocketApplicationTrait
 * @package SwooleKit\WebSocket\Server
 */
trait WebSocketApplicationTrait
{
    /**
     * @var ModuleInterface[]
     * [
     *  // path => ModuleInterface,
     *  '/'  => RootHandler,
     * ]
     */
    private $modules;

    /** @var array */
    protected $options = [
        'debug' => false,

        'name' => 'application',

        // request and response data type: json text
        'dataType' => 'json',

        // allowed accessed Origins. e.g: [ 'localhost', 'site.com' ]
        'allowedOrigins' => '*',

        // for http
    ];

    /*******************************************************************************
     * websocket handle
     ******************************************************************************/

    /**
     * webSocket 只会在连接握手时会有 request, response
     * @param ServerRequest $request
     * @param Response $response
     * @param int $cid
     * @return bool
     */
    public function handleHandshake(ServerRequest $request, Response $response, int $cid)
    {
        $path = $request->getPath();

        try {
            // check module. if not exists, response 404 error
            if (!$module = $this->getModule($path, false)) {
                Sws::error("The #$cid request's path [$path] route handler module not exists.");

                $this->fire(self::ON_NO_MODULE, [$cid, $path, $this]);

                $response
                    ->setStatus(404)
                    ->setHeaders(['Connection' => 'close'])
                    ->write("You request route path [$path] not found!");

                return false;
            }

            // check request
            if (!$module->validateRequest($request, $response)) {
                return false;
            }

            // application/json
            // text/plain
            $response->setHeader('Server', $this->getName() . '-websocket-server');
            // $response->setHeader('Access-Control-Allow-Origin', '*');

            $module->setApp($this)->onHandshake($request, $response);

            return true;
        } catch (\Throwable $e) {
            $response
                ->setStatus(500)
                ->setHeaders(['Connection' => 'close'])
                ->write('Error on handshake: ' . $e->getMessage());

            $error = PhpHelper::exceptionToString($e, 1, __METHOD__);
            Sws::error($error);

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function handleWsMessage($server, Frame $frame, Connection $conn)
    {
        // dispatch command

        try {
            if ($module = $this->getModule($conn->getPath())) {
                $result = $module->dispatch($frame->data, $conn, $server);

                if ($result && \is_string($result)) {
                    $this->server->send($result);
                }
            }
        } catch (\Throwable $e) {
            $this->handleWsException($e, $conn, __METHOD__);
        }
//        return;
    }

    /**
     * @param \Throwable|\Exception $e
     * @param Connection $conn
     * @param $catcher
     */
    public function handleWsException($e, Connection $conn, $catcher)
    {
        $error = PhpHelper::exceptionToString($e, 1, $catcher);

        // Sws::error($error);

        $this->server->sendFormatted('', $e->getMessage(), __LINE__)->to($conn->getId())->send();
    }

    /*******************************************************************************
     * handle ws request route module
     ******************************************************************************/

    /**
     * register a route and it's handler module
     * @param string $path route path
     * @param ModuleInterface $module the route path module
     * @param bool $replace replace exists's route
     * @return ModuleInterface
     */
    public function addModule(string $path, ModuleInterface $module, $replace = false)
    {
        return $this->module($path, $module, $replace);
    }

    public function module(string $path, ModuleInterface $module, $replace = false)
    {
        $path = trim($path) ?: '/';
        $pattern = '/^\/[a-zA-Z][\w-]+$/';

        if ($path !== '/' && 1 !== preg_match($pattern, $path)) {
            throw new \InvalidArgumentException("The route path[$path] format must be match: $pattern");
        }

        if (!$replace && $this->hasModule($path)) {
            throw new \InvalidArgumentException("The route path[$path] have been registered!");
        }

        // Sws::info("register the ws module for path: $path, module: {$module->getName()}, class: " . \get_class($module));

        $this->modules[$path] = $module;

        return $module;
    }

    /**
     * @param $path
     * @return bool
     */
    public function hasModule(string $path): bool
    {
        return isset($this->modules[$path]);
    }

    /**
     * @param string $path
     * @param bool $throwError
     * @return ModuleInterface
     */
    public function getModule(string $path = '/', $throwError = true)
    {
        if (!$this->hasModule($path)) {
            if ($throwError) {
                throw new \RuntimeException("The route handler not exists for the path: $path");
            }

            return null;
        }

        return $this->modules[$path];
    }

    /**
     * @return array
     */
    public function getModulePaths(): array
    {
        return array_keys($this->modules);
    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param array $modules
     */
    public function setModules(array $modules)
    {
        foreach ($modules as $route => $module) {
            $this->module($route, $module);
        }
    }

    /*******************************************************************************
     * a very simple's user storage
     ******************************************************************************/

    /**
     * @var array
     */
    private $users = [];

    public function getUser($index)
    {
        return $this->users[$index] ?? null;
    }

    public function userLogin($index, $data)
    {

    }

    public function userLogout($index, $data)
    {

    }
}
