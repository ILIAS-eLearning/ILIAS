<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Speedo;

use ILIAS\UI\Component as C;
/**
 * Class Speedo
 * @package ILIAS\UI\Implementation\Component\Chart\Speedo
 */
class Standard extends Speedo implements C\Chart\Speedo\Standard {

    /**
     * @var string
     */
    protected $txt_score;

    /**
     * @var string
     */
    protected $txt_goal;

    /**
     * @inheritdoc
     */
    public function getDiagnostic($getAsPercent = true)
    {
        return $this->getSafe(($getAsPercent == true ? $this->getAsPercentage($this->diagnostic) : $this->diagnostic), $getAsPercent);
    }

    /**
     * @inheritdoc
     */
    public function withTxtScore($txt)
    {
        $this->checkStringArg("txt_score", $txt);

        $clone = clone $this;
        $clone->txt_score = $txt;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTxtScore()
    {
        return $this->txt_score;
    }

    /**
     * @inheritdoc
     */
    public function withTxtGoal($txt)
    {
        $this->checkStringArg("txt_goal", $txt);

        $clone = clone $this;
        $clone->txt_goal = $txt;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTxtGoal()
    {
        return $this->txt_goal;
    }

}