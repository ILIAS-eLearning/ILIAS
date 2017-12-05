<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Gauge;

use ILIAS\UI\Component\Chart\Gauge as G;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Chart\Gauge
 */
class Factory implements \ILIAS\UI\Component\Chart\Gauge\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($maximum, $main, $required = null, $comparision = null)
    {
        return new Standard($maximum, $main, $required, $comparision);
    }
    /**
     * @inheritdoc
     */
    public function fixedSize($maximum, $main, $required = null, $comparision = null)
    {
        return new FixedSize($maximum, $main, $required, $comparision);
    }

    /**
     * @inheritdoc
     */
    public function mini($maximum, $main, $required = null)
    {
        return new Mini($maximum, $main, $required);
    }
}