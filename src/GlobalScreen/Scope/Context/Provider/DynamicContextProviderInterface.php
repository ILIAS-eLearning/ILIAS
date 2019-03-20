<?php

use ILIAS\NavigationContext\ContextInterface;

/**
 * Class DynamicContextProviderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicContextProviderInterface {

	/**
	 * return an array of all the contexts you component will need.
	 * new contexts MUST be accepted by the JF! because it could be other will
	 * need you context as well an then it should be moved to a more global
	 * context such as OnlineHelp or the PageEditor. UnitTests will enforce this.
	 *
	 * @return ContextInterface[]
	 */
	public function getGeneralContextsForComponent(): array;


	/**
	 * this method will be called whenever you context seems to active in the
	 * current situation. We will need to pass some specific data to the context
	 * which you need while providing a specific global screen item.
	 *
	 * @param ContextInterface $context
	 *
	 * @return ContextInterface
	 */
	public function enrichContextWithCurrentSituation(ContextInterface $context): ContextInterface;
}
