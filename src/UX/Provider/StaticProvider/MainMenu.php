<?php namespace ILIAS\UX\Provider\StaticProvider;

use ILIAS\UX\MainMenu\EntryInterface;
use ILIAS\UX\Provider\StaticProvider;

/**
 * Interface IMainMenu
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface MainMenu extends StaticProvider {

	/**
	 * @return \ILIAS\UX\MainMenu\Slate\SlateInterfaceInterface[]
	 */
	public function getStaticSlates(): array;


	/**
	 * @return EntryInterface[]
	 */
	public function getStaticEntries(): array;
}
