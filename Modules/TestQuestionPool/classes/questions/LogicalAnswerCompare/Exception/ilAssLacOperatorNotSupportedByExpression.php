<?php

/**
 * Class OperatorNotSupportedByExpression
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacOperatorNotSupportedByExpression extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $operator;

	/**
	 * @var string
	 */
	protected $expression;

	/**
	 * @param string $expression
	 * @param string $operator
	 */
	public function __construct($expression, $operator)
	{
		$this->expression = $expression;
		$this->operator = $operator;

		parent::__construct(
			  sprintf('The expression "%s" is not supported by the operator "%s"', $this->expression, $this->operator)
		);
	}

	/**
	 * @return string
	 */
	public function getExpression()
	{
		return $this->expression;
	}

	/**
	 * @return string
	 */
	public function getOperator()
	{
		return $this->operator;
	}
}