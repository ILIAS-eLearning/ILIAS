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

namespace ILIAS\Questions\Question\Views;

use ILIAS\Questions\Question\Question;

class Participant
{
    private bool $async = false;
    private bool $interactive = true;
    private bool $show_marks = false;
    private bool $show_correct_solution = false;

    public function __construct(
        private readonly Question $question
    ) {
    }

    public function withIsAsync(bool $async): self
    {
        foreach ($this->question->getAnswerForms() as $form) {
            if (!$form->getType()->isAsyncPresentationAvailable()) {
                throw \Exception('This QuestionType has no async presentation.');
            }
        }
        $clone = clone $this;
        $clone->async = $async;
        return $clone;
    }

    public function withIsInteractive(bool $interactive): self
    {
        $clone = clone $this;
        $clone->interactive = $interactive;
        return $clone;
    }

    public function withShowMarks(bool $show_marks): self
    {
        foreach ($this->question->getAnswerForms() as $form) {
            if (!$form->getType()->isMarkable()) {
                throw \Exception('This QuestionType cannot be marked.');
            }
        }

        $clone = clone $this;
        $clone->show_marks = $show_marks;
        return $clone;
    }

    public function withShowCorrectSolution(bool $show_correct_solution): self
    {
        foreach ($this->question->getAnswerForms() as $form) {
            if (!$form->getType()->isMarkable()) {
                throw \Exception('This QuestionType cannot be marked.');
            }
        }

        $clone = clone $this;
        $clone->show_correct_solution = $show_correct_solution;
        return $clone;
    }
}
