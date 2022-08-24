<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assOrderingHorizontal questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssOrderingHorizontalFeedback extends ilAssMultiOptionQuestionFeedback
{
    public function getAnswerOptionsByAnswerIndex(): array
    {
        if (strpos($this->questionOBJ->ordertext, '::')) {
            return explode('::', $this->questionOBJ->ordertext);
        }
        return explode(' ', $this->questionOBJ->ordertext);
    }

    protected function buildAnswerOptionLabel($index, $answer): string
    {
        return trim($answer);
    }
}
