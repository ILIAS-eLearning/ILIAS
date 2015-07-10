<?php

/**
 * Class ExpressionNotSupportedByQuestion
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacExpressionNotSupportedByQuestion extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $expression;

	/**
	 * @var int
	 */
	protected $question_index;

	/**
	 * @param string $expression
	 * @param int    $question_index
	 */
	public function __construct($expression, $question_index)
	{
		$this->expression = $expression;
		$this->question_index = $question_index;

		parent::__construct(
			  sprintf('The expression "%s" is not supported by the question with index "Q%s"', $this->expression, $this->question_index)
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
	 * @return string
	 */
	public function getExpression()
	{
		return $this->expression;
	}
}