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

namespace ILIAS\Test\Results\Data;

class AttemptResult
{
    /**
     * @param array<QuestionResult> $question_results
     */
    public function __construct(
        private readonly int $active_id,
        private readonly int $attempt_id,
        private readonly array $question_results
    ) {
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function getAttempt(): int
    {
        return $this->attempt_id;
    }

    /**
     * @return array<QuestionResult>;
     */
    public function getQuestionResults(): array
    {
        return $this->question_results;
    }
}
