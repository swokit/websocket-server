<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-27
 * Time: 9:27
 */

namespace Swokit\WebSocket\Server\DataParser;

use Monolog\Logger;
use Swokit\WebSocket\Server\Module\ModuleInterface;

/**
 * Class ComplexDataParser
 * @package Swokit\WebSocket\Server\DataParser
 */
class ComplexDataParser implements DataParserInterface
{
    public const JSON_TO_RAW = 1;
    public const JSON_TO_ARRAY = 2;
    public const JSON_TO_OBJECT = 3;

    public $jsonParseTo = 2;

    /**
     * @param string $data
     * @param int $index
     * @param ModuleInterface $module
     * @return array|false
     */
    public function parse(string $data, int $index, ModuleInterface $module)
    {
        // default format: [@command]data
        // eg:
        // [@test]hello
        // [@login]{"name":"john","pwd":123456}

        $command = '';

        if (preg_match('/^\[@([\w-]+)\](.+)/', $data, $matches)) {
            array_shift($matches);
            list($command, $realData) = $matches;

            // access default command
        } else {
            $realData = $data;
        }

        $to = $this->jsonParseTo ?: self::JSON_TO_RAW;
        $module->log("The #{$index} request Command: $command, To-format: $to, Data: $realData");

        if ($to !== self::JSON_TO_RAW) {
            $realData = json_decode(trim($realData), $to === self::JSON_TO_ARRAY);

            // parse error
            if (json_last_error() > 0) {
                $errMsg = json_last_error_msg();
                $module->log("Request data parse to json failed! Error: {$errMsg}, Data: {$data}", [], Logger::ERROR);
                return false;
            }
        }

        return [$command, $realData];
    }
}
