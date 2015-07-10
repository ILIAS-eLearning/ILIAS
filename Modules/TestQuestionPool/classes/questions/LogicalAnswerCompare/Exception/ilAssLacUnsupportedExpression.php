<?php

/**
 * Class UnsupportedExpression
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacUnsupportedExpression extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $expression;

	/**
	 * @param string $expression
	 */
	public function __construct($expression)
	{
		$this->expression = $expression;
		parent::__construct(
			  sprintf('The expression "%s" is not supported', $this->expression)
		);
	}

	/**
	 * @return string
	 */
	public function getExpression()
	{
		return $this->expression;
	}
}