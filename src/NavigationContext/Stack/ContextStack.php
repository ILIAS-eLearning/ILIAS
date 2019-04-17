<?php namespace ILIAS\NavigationContext\Stack;

use ILIAS\NavigationContext\ContextInterface;

/**
 * Class ContextStack
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextStack {

	/**
	 * @var ContextInterface[]
	 */
	protected $stack = [];


	/**
	 * @param ContextInterface $context
	 */
	public function push(ContextInterface $context) {
		if (in_array($context, $this->stack)) {
			throw new \LogicException("A context can only be claimed once");
		}
		if (end($this->stack) instanceof ContextInterface) {
			$context->replaceView($this->getLast()->getView());
		}
		array_push($this->stack, $context);
	}


	/**
	 * @return ContextInterface
	 */
	public function getLast(): ContextInterface {
		return end($this->stack);
	}


	/**
	 * @return ContextInterface[]
	 */
	public function getStack(): array {
		return $this->stack;
	}


	public function getStackAsArray(): array {
		$return = [];
		foreach ($this->stack as $item) {
			$return[] = $item->getUniqueContextIdentifier();
		}

		return $return;
	}
}
