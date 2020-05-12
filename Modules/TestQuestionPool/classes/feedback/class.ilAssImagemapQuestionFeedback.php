<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assImagemapQuestion questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssImagemapQuestionFeedback extends ilAssMultiOptionQuestionFeedback
{
    /**
     * builds an answer option label from given (mixed type) index and answer
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     *
     * @access protected
     * @param integer $index
     * @param mixed $answer
     * @return string $answerOptionLabel
     */
    protected function buildAnswerOptionLabel($index, $answer)
    {
        $text = $this->lng->txt('region') . " " . ($index + 1);
        if (strlen($answer->getAnswertext())) {
            $text = $answer->getAnswertext() . ": " . $text;
        }
        
        return $text;
    }
}
