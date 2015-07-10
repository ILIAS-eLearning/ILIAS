<?php

/**
 * Class ilAssLacQuestionNotReachable
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacAnswerValueNotExist extends \RuntimeException{

	/**
	 * @var int
	 */
	protected $question_index;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @var int
	 */
	protected $answer_index;

	/**
	 * @param int $question_index
	 * @param string $value
	 * @param int $answer_index
	 */
	public function __construct($question_index, $value, $answer_index = null)
	{
		$this->question_index = $question_index;
		$this->answer_index = $answer_index;
		$this->value = $value;

		$message = 'The value "%s" does not exist for the question Q%s[%s]';
		if($this->answer_index === null)
		{
			$message = 'The value "%s" does not exist for the question Q%s';
		}

		parent::__construct(
			sprintf($message, $this->question_index, $value, $this->answer_index)
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

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}