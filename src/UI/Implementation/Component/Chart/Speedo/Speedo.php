<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart\Speedo;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
/**
 * Class Speedo
 * @package ILIAS\UI\Implementation\Component\Chart\Speedo
 */
class Speedo implements C\Chart\Speedo\Speedo {
    use ComponentHelper;

    /**
     * @var int
     */
    protected $goal;

    /**
     * @var int
     */
    private $minimum;

    /**
     * @var int
     */
    protected $score;

    /**
     * @var int
     */
    protected $diagnostic;

    /**
     * @inheritdoc
     */
    public function __construct($scores)
    {
        $this->checkIntArg("goal", $scores['goal']);
        $this->goal = $scores['goal'];
        $this->checkIntArg("score", $scores['score']);
        $this->score = $this->getSafe($scores['score']);

        if(array_key_exists('minimum', $scores)) {
            $this->checkIntArg("minimum", $scores['minimum']);
            $this->minimum = $this->getSafe($scores['minimum']);
        } else {
            $this->checkIntArg("minimum", $scores['goal']);
            $this->minimum = $this->getSafe($scores['goal']);
        }
        if(array_key_exists('diagnostic', $scores)) {
            $this->checkIntArg("diagnostic", $scores['diagnostic']);
            $this->diagnostic = $this->getSafe($scores['diagnostic']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getGoal()
    {
        return (isset($this->goal) ? $this->goal : 100);
    }

    /**
     * @inheritdoc
     */
    public function getMinimum($getAsPercent = true)
    {
        return $this->getSafe(($getAsPercent == true ? $this->getAsPercentage($this->minimum) : $this->minimum), $getAsPercent);
    }

    /**
     * @inheritdoc
     */
    public function getScore($getAsPercent = true)
    {
        return $this->getSafe(($getAsPercent == true ? $this->getAsPercentage($this->score) : $this->score), $getAsPercent);
    }

    /**
     * Get integer value "1" if a value is negative or "100" if value is more then goal
     *
     * @param int $a_int
     * @return int
     */
    protected function getSafe($a_int, $getAsPercent = true)
    {
        return (($a_int < 0) ? 0 : ($getAsPercent == true ? ($this->getAsPercentage($a_int) > 100 ? 100 : $a_int) : $a_int));
    }

    /**
     * get an integer score value as percent value
     *
     * @param int $score
     * @return int
     */
    protected function getAsPercentage($score)
    {
        return round(100 / $this->getGoal() * $score, 0 , PHP_ROUND_HALF_UP);
    }

    /**
     * @inheritdoc
     */
    public function hasDiagnostic()
    {
        return isset($this->diagnostic);
    }
}