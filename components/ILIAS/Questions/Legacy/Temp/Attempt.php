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

namespace ILIAS\Questions\Temp;

use ILIAS\Data\UUID\Uuid;

class Attempt
{
    public function __construct(
        private readonly Uuid $attempt_id,
        private array $solved_questions
    ) {
    }

    public function getAttemptId(): Uuid
    {
        return $this->attempt_id;
    }

    public function isQuestionSolved(
        Uuid $question_id
    ): bool {
        return in_array($question_id->toString(), $this->solved_questions);
    }

    public function getSolvedQuestionsForStorage(): string
    {
        return implode(',', $this->solved_questions);
    }

    public function withAdditionalSolvedQuestion(
        Uuid $question_id
    ): self {
        if ($this->isQuestionSolved($question_id)) {
            return $this;
        }

        $clone = clone $this;
        $clone->solved_questions[] = $question_id->toString();
        return $clone;
    }

    public function withQuestionRemovedFromSolved(
        Uuid $question_id
    ): self {
        if (!$this->isQuestionSolved($question_id)) {
            return $this;
        }

        $clone = clone $this;
        unset(
            $clone->solved_questions[array_search($question_id->toString(), $clone->solved_questions)]
        );
        return $clone;
    }
}
