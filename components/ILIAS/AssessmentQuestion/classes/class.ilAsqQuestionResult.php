<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilAsqResultCalculator
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components/ILIAS/AssessmentQuestion
 */
class ilAsqQuestionResult
{
    /**
     * @var float
     */
    protected $points;

    /**
     * @var bool
     */
    protected $correct;

    /**
     * @param float $points
     */
    public function setPoints(float $points)
    {
        $this->points = $points;
    }

    /**
     * @return float
     */
    public function getPoints(): float
    {
        return $this->points;
    }

    /**
     * @param bool $correct
     */
    public function setCorrect(bool $correct)
    {
        $this->correct = $correct;
    }

    /**
     * @return bool
     */
    public function isCorrect(): bool
    {
        return $this->correct;
    }
}
