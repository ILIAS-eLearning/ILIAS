<?php

/**
 * Class AnswerIndexNotExist
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacAnswerIndexNotExist extends \RuntimeException{

	/**
	 * @var int
	 */
	protected $question_index;

	/**
	 * @var int
	 */
	protected $answer_index;

	/**
	 * @param int $question_index
	 * @param int $answer_index
	 */
	public function __construct($question_index, $answer_index)
	{
		$this->question_index = $question_index;
		$this->answer_index = $answer_index;

		parent::__construct(
			  sprintf('The Question with index "Q%s" does not have an answer with the index "%s" ', $this->question_index, $this->answer_index)
		);
	}

	/**
	 * @return int
	 */
	public function getQuestionIndex()
	{
		return $this->question_index;
	}

	/**
	 * @return int
	 */
	public function getAnswerIndex()
	{
		return $this->answer_index;
	}
}