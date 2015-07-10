<?php

/**
 * Class QuestionNotExist
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacQuestionNotExist extends \RuntimeException{

	/**
	 * @var int
	 */
	protected $question_index;

	/**
	 * @param int $question_index
	 */
	public function __construct($question_index)
	{
		$this->question_index = $question_index;

		parent::__construct(
			  sprintf('The Question with index "Q%s" does not exist "', $this->question_index)
		);
	}

	/**
	 * @return int
	 */
	public function getQuestionIndex()
	{
		return $this->question_index;
	}
}