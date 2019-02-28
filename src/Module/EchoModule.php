<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/3/26 0026
 * Time: 15:34
 */

namespace Swokit\WebSocket\Server\Module;

use PhpComp\Http\Message\Response;
use PhpComp\Http\Message\ServerRequest as Request;

/**
 * Class EchoModule
 *
 * handle the root '/echo' webSocket request
 *
 * @package Swokit\WebSocket\Server\Module
 */
class EchoModule extends AbstractModule
{
    /**
     * @param Request  $request
     * @param Response $response
     */
    public function onHandshake(Request $request, Response $response): void
    {
        parent::onHandshake($request, $response);

        $response->setCookie('test', 'test-value');
        $response->setCookie('test1', 'test-value1');
    }

    /**
     * index command
     * the default command
     * @param $data
     */
    public function indexCommand($data): void
    {
        $this->respondText('you input: ' . $data);
    }
}
