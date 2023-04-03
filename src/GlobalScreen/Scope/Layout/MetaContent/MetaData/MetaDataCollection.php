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

namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaData;

use Iterator;

/**
 * Class MetaDataCollection
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaDataCollection
{
    /**
     * @var MetaDatum[]
     */
    protected $items = [];

    public function add(MetaDatum $meta_datum) : void
    {
        $this->items[] = $meta_datum;
    }

    public function clear() : void
    {
        $this->items = [];
    }

    /**
     * @return Iterator|MetaDatum[]
     */
    public function getItems() : Iterator
    {
        yield from $this->items;
    }

    /**
     * @return array
     */
    public function getItemsAsKeyValuePairs() : array
    {
        $key_value_pairs = [];
        array_walk($this->items, function (MetaDatum $d) use (&$key_value_pairs) : void {
            $key_value_pairs[$d->getKey()] = $d->getValue();
        });
        return $key_value_pairs;
    }
}
