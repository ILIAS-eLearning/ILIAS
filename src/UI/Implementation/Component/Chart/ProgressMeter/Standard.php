<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\ProgressMeter;

use ILIAS\UI\Component as C;

/**
 * Class ProgressMeter
 * @package ILIAS\UI\Implementation\Component\Chart\ProgressMeter
 */
class Standard extends ProgressMeter implements C\Chart\ProgressMeter\Standard
{

    /**
     * @var string
     */
    protected $main_text;

    /**
     * @var string
     */
    protected $required_text;

    /**
     * @inheritdoc
     */
    public function getComparison()
    {
        return $this->getSafe($this->comparison);
    }

    /**
     * Get comparison value as percent
     *
     * @return int
     */
    public function getComparisonAsPercent()
    {
        return $this->getAsPercentage($this->comparison);
    }

    /**
     * @inheritdoc
     */
    public function withMainText($text)
    {
        $this->checkStringArg("main_value_text", $text);

        $clone = clone $this;
        $clone->main_text = $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getMainText()
    {
        return $this->main_text;
    }

    /**
     * @inheritdoc
     */
    public function withRequiredText($text)
    {
        $this->checkStringArg("required_value_text", $text);

        $clone = clone $this;
        $clone->required_text = $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getRequiredText()
    {
        return $this->required_text;
    }
}
