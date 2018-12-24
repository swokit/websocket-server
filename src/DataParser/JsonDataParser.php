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
 * Class JsonDataParser
 * @package Swokit\WebSocket\Server\DataParser
 */
class JsonDataParser implements DataParserInterface
{
    // default cmd key in the request json data.
    const DEFAULT_CMD_KEY = '_cmd';
    const JSON_TO_RAW = 1;
    const JSON_TO_ARRAY = 2;
    const JSON_TO_OBJECT = 3;

    /**
     * @var string
     */
    public $cmdKey = '_cmd';

    /**
     * data decode to
     *
     * @var integer
     */
    public $jsonParseTo = 2;

    /**
     * @param string $data
     * @param int $index
     * @param ModuleInterface $module
     * @return array|false
     */
    public function parse(string $data, int $index, ModuleInterface $module)
    {
        // json parser
        // format: {"_cmd": "value", ... ...}
        // eg: {"_cmd": "login", "name":"john","pwd":123456}
        $temp = $data;
        $command = '';
        $to = $this->jsonParseTo ?: self::JSON_TO_RAW;
        $cmdKey = $this->cmdKey ?: self::DEFAULT_CMD_KEY;

        $module->log("The #{$index} request command: $command, data: $data");

        $data = json_decode(trim($data), $toAssoc = $to === self::JSON_TO_ARRAY);

        // parse error
        if (json_last_error() > 0) {
            $errMsg = json_last_error_msg();

            $module->log("The #{$index} request data parse to json failed! MSG: {$errMsg}, Data: {$temp}", [], Logger::ERROR);

            return false;
        }

        if ($toAssoc) {
            if (isset($data[$cmdKey]) && $data[$cmdKey]) {
                $command = $data[$cmdKey];
                unset($data[$cmdKey]);
            }
        } elseif ($to === self::JSON_TO_OBJECT) {
            if (isset($data->{$cmdKey}) && $data->{$cmdKey}) {
                $command = $data->{$cmdKey};
                unset($data->{$cmdKey});
            }
        } else {
            // revert
            $data = $temp;
        }

        unset($temp);
        return [$command, $data];
    }
}
