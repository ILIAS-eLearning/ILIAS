<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Gauge;

use ILIAS\UI\Component as C;
/**
 * Class Gauge
 * @package ILIAS\UI\Implementation\Component\Chart\Gauge
 */
class Standard extends Gauge implements C\Chart\Gauge\Standard {

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
    public function getComparision()
    {
        return $this->getSafe($this->comparision);
    }

    /**
     * Get comparision value as percent
     */
    public function getComparisionAsPercent()
    {
        return $this->getAsPercentage($this->comparision);
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