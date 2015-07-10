<?php

/**
 * Class UnsupportedOperation
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacUnsupportedOperation extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $operator;

	/**
	 * @param string $operator
	 */
	public function __construct($operator)
	{
		$this->operator = $operator;

		parent::__construct(
			  sprintf('The operator "%s" is not supported', $this->operator)
		);
	}

	/**
	 * @return string
	 */
	public function getOperator()
	{
		return $this->operator;
	}
}