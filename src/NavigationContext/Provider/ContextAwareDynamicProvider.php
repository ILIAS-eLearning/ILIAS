<?php namespace ILIAS\NavigationContext\Provider;

use ILIAS\NavigationContext\ContextInterface;

/**
 * Class ContextAwareDynamicProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ContextAwareDynamicProvider {

	/**
	 * this method will be called whenever you context seems to be active in the
	 * current situation. We will need to pass some specific data to the context
	 * which you need while providing a specific global screen item.
	 *
	 * @param ContextInterface $context
	 *
	 * @return ContextInterface
	 */
	public function enrichContextWithCurrentSituation(ContextInterface $context): ContextInterface;
}
