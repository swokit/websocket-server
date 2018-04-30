<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/3/26 0026
 * Time: 15:34
 */

namespace SwooleKit\WebSocket\Server\Module;

use Inhere\Http\ServerRequest as Request;
use Inhere\Http\Response;

/**
 * Class EchoModule
 *
 * handle the root '/echo' webSocket request
 *
 * @package SwooleKit\WebSocket\Server\Module
 */
class EchoModule extends AbstractModule
{
    /**
     * @param Request $request
     * @param Response $response
     */
    public function onHandshake(Request $request, Response $response)
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
    public function indexCommand($data)
    {
        $this->respondText('you input: ' . $data);
    }
}
