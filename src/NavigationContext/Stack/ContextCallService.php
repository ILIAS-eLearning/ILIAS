<?php namespace ILIAS\NavigationContext\Stack;

use ILIAS\NavigationContext\ContextInterface;

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
		if (in_array($context, $this->stack->getStack())) {
			throw new \LogicException("A context can only be claimed once");
		}
		$this->stack->push($context);
	}
}
