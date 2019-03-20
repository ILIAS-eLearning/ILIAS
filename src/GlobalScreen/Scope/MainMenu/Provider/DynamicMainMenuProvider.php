<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\DynamicProvider;
use ILIAS\NavigationContext\Stack\ContextStack;

/**
 * Interface DynamicMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicMainMenuProvider extends DynamicProvider, MainMenuProviderInterface {

	/**
	 * @see DynamicProvider
	 *
	 * @param ContextStack $called_contexts
	 *
	 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem[] These Slates
	 * can be passed to the MainMenu dynamicly for a specific location/context.
	 *
	 * This is currently not used for Core components but plugins may use it.
	 */
	public function getDynamicSlatesForContext(ContextStack $called_contexts): array;
}
