<?php namespace ILIAS\GlobalScreen\Scope\Context\Stack;

use ILIAS\GlobalScreen\Scope\Context\ContextInterface;

/**
 * Class ContextCallService
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextCallService {

	/**
	 * @var CalledContexts
	 */
	private $stack;


	/**
	 * ContextCallService constructor.
	 *
	 * @param CalledContexts $stack
	 */
	public function __construct(CalledContexts $stack) {
		$this->stack = $stack;
	}


	/**
	 * @param ContextInterface $context
	 */
	public function currentComponentClaimsContext(ContextInterface $context) {
		$this->stack->push($context);
	}
}
