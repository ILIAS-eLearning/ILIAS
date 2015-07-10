<?php

/**
 * Class DuplicateElement
 * @package 
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 */ 
class ilAssLacDuplicateElement extends \RuntimeException{

	/**
	 * @var string
	 */
	protected $element;

	/**
	 * @param string $bracket
	 */
	public function __construct($element)
	{
		$this->element = $element;

		parent::__construct(
			  sprintf('Duplicate key "%s" in condition', $this->element)
		);
	}

	/**
	 * @return string
	 */
	public function getElement()
	{
		return $this->element;
	}
}