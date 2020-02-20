<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assOrderingQuestion questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssOrderingQuestionFeedback extends ilAssMultiOptionQuestionFeedback
{
    /**
     * @var assOrderingQuestion
     */
    protected $questionOBJ;
    
    /**
     * returns the answer options mapped by answer index
     * (can be overwritten by concrete question type class)
     *
     * @return array $answerOptionsByAnswerIndex
     */
    public function getAnswerOptionsByAnswerIndex()
    {
        return $this->questionOBJ->getOrderingElementList()->getElements();
    }
    
    /**
     * builds an answer option label from given (mixed type) index and answer
     * (can be overwritten by concrete question types)
     *
     * @access protected
     * @param integer $position
     * @param ilAssOrderingElement $orderingElement
     * @return string $answerOptionLabel
     */
    protected function buildAnswerOptionLabel($position, $orderingElement)
    {
        return $orderingElement->getContent();
    }
}
