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
 * Environment/settings to control result presentation
 */
class ilTestResultsSettings
{
    protected bool $show_hidden_questions = false;
    protected bool $show_optional_questions = false;
    protected bool $show_best_solution = true;
    protected bool $show_feedback = true;
    protected bool $question_text_only = false;


    public function __construct(
    ) {
    }

    public function withShowHiddenQuestions(bool $flag): self
    {
        $clone = clone $this;
        $clone->show_hidden_questions = $flag;
        return $clone;
    }
    public function getShowHiddenQuestions(): bool
    {
        return $this->show_hidden_questions;
    }

    public function withShowOptionalQuestions(bool $flag): self
    {
        $clone = clone $this;
        $clone->show_optional_questions = $flag;
        return $clone;
    }
    public function getShowOptionalQuestions(): bool
    {
        return $this->show_optional_questions;
    }

    public function withShowBestSolution(bool $flag): self
    {
        $clone = clone $this;
        $clone->show_best_solution = $flag;
        return $clone;
    }

    public function getShowBestSolution(): bool
    {
        return $this->show_best_solution;
    }

    public function withShowFeedback(bool $flag): self
    {
        $clone = clone $this;
        $clone->show_feedback = $flag;
        return $clone;
    }
    public function getShowFeedback(): bool
    {
        return $this->show_feedback;
    }

    public function withQuestionTextOnly(bool $flag): self
    {
        $clone = clone $this;
        $clone->question_text_only = $flag;
        return $clone;
    }
    public function getQuestionTextOnly(): bool
    {
        return $this->question_text_only;
    }


}
