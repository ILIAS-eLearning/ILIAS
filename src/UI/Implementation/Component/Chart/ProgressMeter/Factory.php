<?php declare(strict_types=1);

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Component\Chart\ProgressMeter;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Factory implements ProgressMeter\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($maximum, $main, $required = null, $comparison = null) : ProgressMeter\Standard
    {
        return new Standard($maximum, $main, $required, $comparison);
    }
    /**
     * @inheritdoc
     */
    public function fixedSize($maximum, $main, $required = null, $comparison = null) : ProgressMeter\FixedSize
    {
        return new FixedSize($maximum, $main, $required, $comparison);
    }

    /**
     * @inheritdoc
     */
    public function mini($maximum, $main, $required = null) : ProgressMeter\Mini
    {
        return new Mini($maximum, $main, $required);
    }
}
