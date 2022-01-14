<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

use Generator;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class Js
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractCollection
{

    /**
     * @var Js[]|Css[]|InlineCss[]|OnLoadCode[]
     */
    protected array $items = [];

    protected string $resource_version;

    /**
     * @param string $resource_version
     */
    public function __construct(string $resource_version)
    {
        $this->resource_version = $resource_version;
    }

    public function clear()
    {
        $this->items = [];
    }

    /**
     * @return \Iterator<\ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Css[]|\ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\InlineCss[]|\ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\Js[]|\ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\OnLoadCode[]>
     */
    public function getItems() : \Iterator
    {
        yield from $this->items;
    }

    /**
     * @return Js[]|Css[]|InlineCss[]|OnLoadCode[]
     */
    public function getItemsInOrderOfDelivery() : array
    {
        return $this->items;
    }

    /**
     * @param string $path
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
