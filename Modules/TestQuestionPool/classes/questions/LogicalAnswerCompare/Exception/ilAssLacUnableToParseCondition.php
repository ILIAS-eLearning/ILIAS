<?php

/**
 * Class UnableToParseCondition
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacUnableToParseCondition extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $condition;


	/**
	 * @param string $expression
	 * @param int    $question_index
	 */
	public function __construct($condition)
	{
		$this->condition = $condition;

		parent::__construct(
			  sprintf('The parser is unable to parse the condition "%s"', $this->condition)
		);
	}

	/**
	 * @return string
	 */
	public function getCondition()
	{
		return $this->condition;
	}
}