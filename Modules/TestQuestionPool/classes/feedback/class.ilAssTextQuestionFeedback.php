<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assTextQuestion questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssTextQuestionFeedback extends ilAssMultiOptionQuestionFeedback
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
        $caption = $ordinal = $index + 1;
        $caption .= '. ' . $answer->getAnswertext();
        
        return $caption;
    }
}
