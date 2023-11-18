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

/**
 * @package Modules/Test
 * Results for one question/user
 */
class ilQuestionResult
{
    public const CORRECT_FULL = 1;
    public const CORRECT_PARTIAL = 2;
    public const CORRECT_NONE = 3;

    public function __construct(
        protected int $id,
        protected string $type,
        protected string $title,
        protected float $question_score,
        protected float $usr_score,
        protected string $usr_solution,
        protected string $best_solution,
        protected string $feedback,
        protected bool $workedthrough,
        protected bool $answered,
        protected ?string $content_for_recapitulation
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getUserAnswer(): string
    {
        return $this->usr_solution;
    }
    public function getBestSolution(): string
    {
        return $this->best_solution;
    }
    public function getQuestionScore(): float
    {
        return $this->question_score;
    }
    public function getUserScore(): float
    {
        return $this->usr_score;
    }
    public function getUserScorePercent(): float
    {
        if ($this->getQuestionScore() === 0.0) {
            return 100;
        }

        return 100 / $this->getQuestionScore() * $this->getUserScore();
    }
    public function getCorrect(): int
    {
        if ($this->getUserScore() === 0.0) {
            return self::CORRECT_NONE;
        }
        if ($this->getUserScore() === $this->getQuestionScore()) {
            return self::CORRECT_FULL;
        }
        return self::CORRECT_PARTIAL;
    }
    public function getFeedback(): string
    {
        return $this->feedback;
    }
    public function isWorkedThrough(): bool
    {
        return $this->workedthrough;
    }
    public function isAnswered(): bool
    {
        return $this->answered;
    }
    public function getContentForRecapitulation(): ?string
    {
        return $this->content_for_recapitulation;
    }
}
