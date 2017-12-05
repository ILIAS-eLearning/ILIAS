<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Speedo;

use ILIAS\UI\Component\Chart\Speedo as S;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Chart\Speedo
 */
class Factory implements \ILIAS\UI\Component\Chart\Speedo\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($maximum, $score, $minimum = null, $diagnostic = null)
    {
        return new Standard($maximum, $score, $minimum, $diagnostic);
    }
    /**
     * @inheritdoc
     */
    public function responsive($maximum, $score, $minimum = null, $diagnostic = null)
    {
        return new Responsive($maximum, $score, $minimum, $diagnostic);
    }

    /**
     * @inheritdoc
     */
    public function mini($maximum, $score, $minimum = null)
    {
        return new Mini($maximum, $score, $minimum);
    }
}