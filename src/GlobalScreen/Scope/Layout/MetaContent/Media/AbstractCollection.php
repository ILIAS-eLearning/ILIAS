<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

use Generator;

/**
 * Class Js
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractCollection
{

    /**
     * @var Js[]|Css[]|InlineCss[]|OnLoadCode[]
     */
    protected $items = [];


    public function clear()
    {
        $this->items = [];
    }


    /**
     * @return Generator
     */
    public function getItems() : Generator
    {
        yield from $this->items;
    }


    /**
     * @return array
     */
    public function getItemsInOrderOfDelivery() : array
    {
        return $this->items;
    }


    /**
     * @param string $path
     *
     * @return string
     */
    protected function stripPath(string $path) : string
    {
        if (strpos($path, '?') !== false) {
            return parse_url($path, PHP_URL_PATH);
        }

        return $path;
    }
}
