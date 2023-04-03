<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

use Iterator;

/**
 * Class Js
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractCollection
{
    /**
     * @var Js[]|Css[]|InlineCss[]|OnLoadCode[]
     */
    protected $items = [];

    /**
     * @var string
     */
    protected $resource_version;

    /**
     * @param string $resource_version
     */
    public function __construct(string $resource_version)
    {
        $this->resource_version = $resource_version;
    }

    public function clear() : void
    {
        $this->items = [];
    }

    /**
     * @return Iterator <Css[]|InlineCss[]|Js[]|OnLoadCode[]>
     */
    public function getItems() : Iterator
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
