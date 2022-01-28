<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Descriptive
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class Descriptive implements C\Listing\Descriptive
{
    use ComponentHelper;

    private array $items;

    public function __construct(array $items)
    {
        $this->checkArgList(
            "Descriptive List items",
            $items,
            function ($k, $v) {
                return is_string($k) && (is_string($v) || $v instanceof C\Component);
            },
            function ($k, $v) {
                return "expected keys of type string and values of type string|Component, got ($k => $v)";
            }
        );

        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items) : C\Listing\Descriptive
    {
        $this->checkArgList(
            "Descriptive List items",
            $items,
            function ($k, $v) {
                return is_string($k) && (is_string($v) || $v instanceof C\Component);
            },
            function ($k, $v) {
                return "expected keys of type string and values of type string|Component, got ($k => $v)";
            }
        );

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
