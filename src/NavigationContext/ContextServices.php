<?php namespace ILIAS\NavigationContext;

use ILIAS\NavigationContext\Stack\ContextCallService;
use ILIAS\NavigationContext\Stack\ContextStack;

/**
 * Class ContextServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices {

	/**
	 * @var
	 */
	private $stack;


	/**
	 * ContextServices constructor.
	 */
	public function __construct() {
		$this->stack = new ContextStack();
	}


	/**
	 * @return ContextStack
	 */
	public function stack(): ContextStack {
		return $this->stack;
	}


	/**
	 * @param ContextInterface $context
	 */
	public function claim(ContextInterface $context) {
		if (in_array($context, $this->stack->getStack())) {
			throw new \LogicException("A context can only be claimed once");
		}
		$this->stack->push($context);
	}


	/**
	 * @return ContextRepository
	 */
	public function availableContexts(): ContextRepository {
		return new ContextRepository();
	}
}
