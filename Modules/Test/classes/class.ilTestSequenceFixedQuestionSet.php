<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSequence.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSequenceFixedQuestionSet extends ilTestSequence
{
	public function removeQuestion($questionId)
	{
		$this->sequencedata['sequence'] = $this->removeArrayValue($this->sequencedata['sequence'], $questionId);
		$this->sequencedata['postponed'] = $this->removeArrayValue($this->sequencedata['postponed'], $questionId);
		$this->sequencedata['hidden'] = $this->removeArrayValue($this->sequencedata['hidden'], $questionId);
		
		$this->optionalQuestions = $this->removeArrayValue($this->optionalQuestions, $questionId);
		
		$this->alreadyPresentedQuestions = $this->removeArrayValue($this->alreadyPresentedQuestions, $questionId);
		
		$this->alreadyCheckedQuestions = $this->removeArrayValue($this->alreadyCheckedQuestions, $questionId);
	}
	
	private function removeArrayValue($array, $value)
	{
		foreach($array as $key => $val)
		{
			if( $val == $value )
			{
				unset($array[$key]);
			}
		}
		
		return $array;
	}
} 