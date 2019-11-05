<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Component\Chart\ProgressMeter as G;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Factory implements \ILIAS\UI\Component\Chart\ProgressMeter\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($maximum, $main, $required = null, $comparison = null)
    {
        return new Standard($maximum, $main, $required, $comparison);
    }
    /**
     * @inheritdoc
     */
    public function fixedSize($maximum, $main, $required = null, $comparison = null)
    {
        return new FixedSize($maximum, $main, $required, $comparison);
    }

    /**
     * @inheritdoc
     */
    public function mini($maximum, $main, $required = null)
    {
        return new Mini($maximum, $main, $required);
    }
}
