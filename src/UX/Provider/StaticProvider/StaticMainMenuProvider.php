<?php namespace ILIAS\UX\Provider\StaticProvider;

use ILIAS\UX\MainMenu\EntryInterface;
use ILIAS\UX\MainMenu\Slate\SlateInterfaceInterface;
use ILIAS\UX\Provider\StaticProvider;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StaticMainMenuProvider extends StaticProvider {

	/**
	 * @return SlateInterfaceInterface[] These are Slates which will be
	 * available for configuration and will be collected once during a
	 * StructureReload.
	 */
	public function getStaticSlates(): array;


	/**
	 * @return EntryInterface[] These are Entries which will be available for
	 * configuration and will be collected once during a StructureReload
	 */
	public function getStaticEntries(): array;
}
