<?php namespace ILIAS\UX\Provider\DynamicProvider;

use ILIAS\UX\Provider\DynamicProvider;

/**
 * Interface DynamicMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicMainMenuProvider extends DynamicProvider {

	/**
	 * @see DynamicProvider
	 *
	 * @return \ILIAS\UX\MainMenu\Slate\SlateInterfaceInterface[] These Slates
	 * can be passed to the MainMenu dynamicly for a specific location/context.
	 *
	 * This is currently not used for Core components but plugins may use it.
	 */
	public function getDynamicSlates(): array;
}
