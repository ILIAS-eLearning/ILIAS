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

declare(strict_types=1);

namespace ILIAS\Test\Questions\Properties;

class PropertyAggregatedResults implements Property
{
    public function __construct(
        private readonly int $question_id,
        private readonly int $number_of_answers = 0,
        private readonly float $available_points = 0.0,
        private readonly float $total_achieved_points = 0.0,
    ) {
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function getNumberOfAnswers(): int
    {
        return $this->number_of_answers;
    }

    public function getAveragePoints(): float
    {
        if ($this->number_of_answers === 0) {
            return 0.0;
        }
        return $this->total_achieved_points / $this->number_of_answers;
    }

    public function getPercentageOfPointsAchieved(): float
    {
        if ($this->number_of_answers === 0) {
            return 0.0;
        }
        return ($this->total_achieved_points / ($this->number_of_answers * $this->available_points)) * 100;
    }
}
