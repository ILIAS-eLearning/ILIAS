<?php declare(strict_types=1);

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

    private array $items;

    /**
     * Listing constructor.
     */
    public function __construct(array $items)
    {
        $types = array('string',C\Component::class);
        $this->checkArgListElements("items", $items, $types);
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items) : C\Listing\Listing
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
    public function getItems() : array
    {
        return $this->items;
    }
}
