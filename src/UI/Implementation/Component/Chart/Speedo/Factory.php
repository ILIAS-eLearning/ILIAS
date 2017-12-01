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
    public function standard(array $scores)
    {
        return new Standard($scores);
    }
    /**
     * @inheritdoc
     */
    public function responsive(array $scores)
    {
        return new Responsive($scores);
    }

    /**
     * @inheritdoc
     */
    public function mini(array $scores)
    {
        return new Mini($scores);
    }
}