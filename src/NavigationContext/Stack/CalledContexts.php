<?php namespace ILIAS\NavigationContext\Stack;

use ILIAS\NavigationContext\ContextInterface;

/**
 * Class CalledContexts
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CalledContexts {

	/**
	 * @var ContextInterface[]
	 */
	protected $stack = [];


	/**
	 * @param ContextInterface $context
	 */
	public function push(ContextInterface $context) {
		array_push($this->stack, $context);
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
