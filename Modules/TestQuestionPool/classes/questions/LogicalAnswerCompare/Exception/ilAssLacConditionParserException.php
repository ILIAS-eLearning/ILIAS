<?php

/**
 * Class ConditionParserException
 *
 * Date: 02.04.14
 * Time: 15:40
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacConditionParserException extends \RuntimeException
{

	/**
	 * @var int
	 */
	protected $column;

	/**
	 * @param int $column
	 */
	public function __construct($column)
	{
		$this->column = $column;

		parent::__construct(
			sprintf('The expression at position "%s" is not valid', $this->column)
		);
	}

	/**
	 * @return int
	 */
	public function getColumn()
	{
		return $this->column;
	}
}
 