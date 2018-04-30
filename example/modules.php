<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-08-22
 * Time: 17:00
 * @var $app Application
 */

use Sws\Application;
use SwooleKit\WebSocket\Server\DataParser\TextDataParser;
use SwooleKit\WebSocket\Server\Module\EchoModule;
use SwooleKit\WebSocket\Server\Module\ModuleInterface;
use SwooleKit\WebSocket\Server\Module\RootModule;

//$app = \Sws::$app;

$echoModule = $app->module('/echo', new EchoModule(['dataType' => 'text'], null, new TextDataParser()));

$rootModule = $app->module('/', new RootModule());

// commands
$rootModule->add('test', function ($data, $index, ModuleInterface $module) {
    return 'hello';
});

// if use `$app->jsonDataParser()` client send: {"_cmd":"login","name":"john","pwd":123456}
// if use `$app->complexDataParser()` client send: [@login]{"name":"john","pwd":123456}
$rootModule->add('login', function ($data, $cid, ModuleInterface $handler) {

    $name = $data['name'] ?? 'Please input your name.';

    $handler->respond("hello, $name. you login success, welcome!", '', 0, false)
        ->to($cid)
        ->send();

    // 1. will return text
    // return "hello, $name";

    // 2. will return formatted json
    // return $app->fmtJson("hello, $name");

    // 3. will return data type by `Application::isJsonType()`.
    // `Application::isJsonType() === true`  return formatted json.
    // `Application::isJsonType() === false` return raw text.
    // **it is recommended**
    $handler->respond("welcome new friend: $name join us.");
});

$rootModule->add('logout', function ($data, $id, Application $app) {
    $user = $app->getUser($id);

    return $app->respondText("goodbye, {$user['name']}");
});
