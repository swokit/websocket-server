<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/3/26 0026
 * Time: 15:35
 */

namespace SwooleKit\WebSocket\Server\DataParser;

use SwooleKit\WebSocket\Server\Module\ModuleInterface;

/**
 * Interface DataParserInterface
 * @package SwooleKit\WebSocket\Server\DataParser
 *
 */
interface DataParserInterface
{


    /**
     * @param string $data
     * @param int $index
     * @param ModuleInterface $module
     * @return array|false
     */
    public function parse(string $data, int $index, ModuleInterface $module);
}
