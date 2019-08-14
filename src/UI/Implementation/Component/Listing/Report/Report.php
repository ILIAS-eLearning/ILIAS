<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Report;

use http\Exception\InvalidArgumentException;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Report
 * @package ILIAS\UI\Implementation\Component\Listing\Report
 */
abstract class Report implements C\Listing\Report\Report
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

    private function validateItems(array $items)
    {
        if( !count($items) )
        {
            throw new \InvalidArgumentException('expected non empty array, got empty array');
        }

        $this->checkArgList("Report List Items", $items,
            function($k, $v)
            {
                if( !is_string($k) || !strlen($k) )
                {
                    return false;
                }

                if( is_string($v) && strlen($v) )
                {
                    return true;
                }

                if( $v instanceof C\Component )
                {
                    return true;
                }

                return false;
            },
            function($k, $v)
            {
                if( is_object($v) )
                {
                    $v = get_class($v);
                }
                elseif( $v === null )
                {
                    $v = 'NULL';
                }

                return "expected keys of type string and values of type string|Component, got ($k => $v)";
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
