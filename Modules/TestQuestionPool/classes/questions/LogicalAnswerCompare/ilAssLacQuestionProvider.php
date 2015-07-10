<?php

require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

/**
 * Class ilParserQuestionProvider
 *
 * Date: 04.12.13
 * Time: 15:04
 * @author Thomas Joußen <tjoussen@databay.de>
 *
 * @todo MAYBE CHANGE THE LOCATION OF THIS CLASS
 */ 
class ilAssLacQuestionProvider {

	protected $questionId;

	public function __construct($questionId)
	{
		$this->questionId = $questionId;
	}

	/**
	 * @return iQuestionCondition
	 */
	public function getQuestion()
	{
		return assQuestion::_instantiateQuestion($this->questionId);
	}
}
 