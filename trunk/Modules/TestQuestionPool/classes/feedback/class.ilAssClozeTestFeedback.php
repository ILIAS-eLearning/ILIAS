<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

/**
 * feedback class for assClozeTest questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssClozeTestFeedback extends ilAssMultiOptionQuestionFeedback
{
	/**
	 * returns the answer options mapped by answer index
	 * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
	 * 
	 * @return array $answerOptionsByAnswerIndex
	 */
	protected function getAnswerOptionsByAnswerIndex()
	{
		return $this->questionOBJ->gaps;
	}
	
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
		$caption = 'Gap '.$ordinal = $index+1 .':<i> ';
		
		foreach( $answer->items as $item )
		{
			$caption .= '"' . $item->getAnswertext().'" / ';
		}
		
		$caption = substr($caption, 0, strlen($caption)-3);
		$caption .= '</i>';
		
		return $caption;
	}
}
