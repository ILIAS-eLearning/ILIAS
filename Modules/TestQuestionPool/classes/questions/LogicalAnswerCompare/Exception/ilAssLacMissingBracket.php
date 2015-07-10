<?php

/**
 * Class ilAssLacAnswerIndexNotExist
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacMissingBracket extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $bracket;

	/**
	 * @param string $bracket
	 */
	public function __construct($bracket)
	{
		$this->bracket = $bracket;

		parent::__construct(
			  sprintf('There is a bracket "%s" missing in the condition', $this->bracket)
		);
	}

	/**
	 * @return string
	 */
	public function getBracket()
	{
		return $this->bracket;
	}
}