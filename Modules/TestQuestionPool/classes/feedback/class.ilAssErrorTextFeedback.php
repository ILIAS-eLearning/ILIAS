<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assErrorText questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssErrorTextFeedback extends ilAssMultiOptionQuestionFeedback
{
    /**
     * returns the answer options mapped by answer index
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     *
     * @return string[] $answerOptionsByAnswerIndex
     */
    public function getAnswerOptionsByAnswerIndex(): array
    {
        return $this->questionOBJ->getErrorData();
    }

    /**
     * builds an answer option label from given (mixed type) index and answer
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     * @param integer $index
     * @param mixed $answer
     */
    protected function buildAnswerOptionLabel(int $index, $answer): string
    {
        $caption = $ordinal = $index + 1;
        $caption .= '. <br />"' . $answer->text_wrong . '" =&gt; ';
        $caption .= '"' . $answer->text_correct . '"';
        $caption .= '</i>';

        return $caption;
    }
}
