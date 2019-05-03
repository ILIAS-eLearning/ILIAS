<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\DynamicProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Tool\Tool;
use ILIAS\NavigationContext\Provider\ContextAwareDynamicProvider;
use ILIAS\NavigationContext\Stack\ContextCollection;
use ILIAS\NavigationContext\Stack\ContextStack;

/**
 * Interface DynamicMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicMainMenuProvider extends ContextAwareDynamicProvider {

	/**
	 * @return ContextCollection
	 */
	public function isInterestedInContexts(): ContextCollection;


	/**
	 * @see DynamicProvider
	 *
	 * @param ContextStack $called_contexts
	 *
	 * @return Tool[] These Slates
	 * can be passed to the MainMenu dynamic for a specific location/context.
	 */
	public function getToolsForContextStack(ContextStack $called_contexts): array;
}
