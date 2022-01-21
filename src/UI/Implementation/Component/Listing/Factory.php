<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Component\Listing as L;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements L\Factory
{
    /**
     * @inheritdoc
     */
    public function unordered(array $items) : L\Unordered
    {
        return new Unordered($items);
    }

    /**
     * @inheritdoc
     */
    public function ordered(array $items) : L\Ordered
    {
        return new Ordered($items);
    }

    /**
     * @inheritdoc
     */
    public function descriptive(array $items) : L\Descriptive
    {
        return new Descriptive($items);
    }

    /**
     * @inheritdoc
     */
    public function workflow() : L\Workflow\Factory
    {
        return new Workflow\Factory();
    }

    /**
     * @inheritdoc
     */
    public function characteristicValue() : L\CharacteristicValue\Factory
    {
        return new CharacteristicValue\Factory();
    }
}
