<?php namespace ILIAS\GlobalScreen\Scope\Tool\Context\Stack;

use ILIAS\GlobalScreen\Scope\Tool\Context\ToolContext;

/**
 * Class ContextStack
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextStack {

	/**
	 * @var ToolContext[]
	 */
	protected $stack = [];


	/**
	 * @param ToolContext $context
	 */
	public function push(ToolContext $context) {
		if (in_array($context, $this->stack)) {
			throw new \LogicException("A context can only be claimed once");
		}
		array_push($this->stack, $context);
	}


	/**
	 * @return ToolContext
	 */
	public function getLast(): ToolContext {
		return end($this->stack);
	}


	/**
	 * @return ToolContext[]
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
