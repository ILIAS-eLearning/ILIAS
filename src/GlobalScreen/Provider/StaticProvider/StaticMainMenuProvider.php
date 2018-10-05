<?php namespace ILIAS\GlobalScreen\Provider\StaticProvider;

use ILIAS\GlobalScreen\MainMenu\isEntry;
use ILIAS\GlobalScreen\MainMenu\Slate\Slate;
use ILIAS\GlobalScreen\Provider\StaticProvider;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StaticMainMenuProvider extends StaticProvider {

	/**
	 * @return Slate[] These are Slates which will be
	 * available for configuration and will be collected once during a
	 * StructureReload.
	 */
	public function getStaticSlates(): array;


	/**
	 * @return isEntry[] These are Entries which will be available for
	 * configuration and will be collected once during a StructureReload
	 */
	public function getStaticEntries(): array;
}
