<?php namespace ILIAS\GlobalScreen\Provider\DynamicProvider;

use ILIAS\GlobalScreen\Provider\DynamicProvider;

/**
 * Interface DynamicMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicMainMenuProvider extends DynamicProvider {

	/**
	 * @see DynamicProvider
	 *
	 * @return \ILIAS\GlobalScreen\MainMenu\Slate\Slate[] These Slates
	 * can be passed to the MainMenu dynamicly for a specific location/context.
	 *
	 * This is currently not used for Core components but plugins may use it.
	 */
	public function getDynamicSlates(): array;
}
