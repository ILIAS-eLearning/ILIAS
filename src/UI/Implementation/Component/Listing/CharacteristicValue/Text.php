<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\CharacteristicValue;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Report
 * @package ILIAS\UI\Implementation\Component\Listing\Report
 */
class Text implements C\Listing\CharacteristicValue\Text
{
    use ComponentHelper;

    /**
     * @var	array
     */
    protected $items;

    /**
     * @inheritdoc
     */
    public function __construct(array $items)
    {
        $this->validateItems($items);
        $this->items = $items;
    }


    /**
     * @param array $items
     */
    private function validateItems(array $items)
    {
        if( !count($items) )
        {
            throw new \InvalidArgumentException('expected non empty array, got empty array');
        }

        $this->checkArgList("Characteristic Value List Items", $items,
            function($k, $v)
            {
                if( !is_string($k) || !strlen($k) )
                {
                    return false;
                }

                if( !is_string($v) && !strlen($v) )
                {
                    return false;
                }

                return true;
            },
            function($k, $v)
            {
                return "expected keys of type string and values of type string, got ($k => $v)";
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
