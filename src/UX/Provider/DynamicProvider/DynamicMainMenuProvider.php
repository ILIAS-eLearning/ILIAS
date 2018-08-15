<?php namespace ILIAS\UX\Provider\DynamicProvider;

use ILIAS\UX\Provider\StaticProvider;

/**
 * Interface IMainMenu
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicMainMenuProvider extends StaticProvider {

	/**
	 * @return \ILIAS\UX\MainMenu\Slate\SlateInterfaceInterface[]
	 */
	public function getDynamicSlates(): array;
}
