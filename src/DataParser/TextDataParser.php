<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-07
 * Time: 15:19
 */

namespace SwooleKit\WebSocket\Server\DataParser;


use SwooleKit\WebSocket\Server\Module\ModuleInterface;

/**
 * Class TextDataParser
 * @package SwooleKit\WebSocket\Server\DataParser
 */
class TextDataParser implements DataParserInterface
{
    /**
     * @param string $data
     * @param int $index
     * @param ModuleInterface $module
     * @return array|false
     */
    public function parse(string $data, int $index, ModuleInterface $module)
    {
        return [null, $data];
    }
}
