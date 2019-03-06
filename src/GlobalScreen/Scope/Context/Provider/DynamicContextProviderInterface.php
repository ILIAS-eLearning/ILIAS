<?php

use ILIAS\GlobalScreen\Scope\Context\ContextInterface;

/**
 * Class DynamicContextProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicContextProviderInterface {

	/**
	 * @return ContextInterface[]
	 */
	public function getGeneralContextsForComponent(): array;
}
