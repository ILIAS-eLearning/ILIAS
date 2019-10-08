<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Listing
 * @package ILIAS\UI\Implementation\Component\Listing\Listing
 */
class Listing implements C\Listing\Listing
{
    use ComponentHelper;

    /**
     * @var	array
     */
    private $items;


    /**
     * Listing constructor.
     * @param $items
     */
    public function __construct($items)
    {
        $types = array('string',C\Component::class);
        $this->checkArgListElements("items", $items, $types);
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items)
    {
        $types = array('string',C\Component::class);
        $this->checkArgListElements("items", $items, $types);

        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }
}
