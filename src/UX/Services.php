<?php namespace ILIAS\UX;

use ILIAS\UX\Identification\IdentificationFactory;
use ILIAS\UX\MainMenu\MainMenuEntryFactory;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services {

	/**
	 * @see MainMenuEntryFactory
	 *
	 * @return MainMenuEntryFactory
	 */
	public function mainmenu(): MainMenuEntryFactory {
		return new MainMenuEntryFactory();
	}


	/**
	 * @see IdentificationFactory
	 *
	 * @return IdentificationFactory
	 */
	public function identification(): IdentificationFactory {
		return new IdentificationFactory();
	}
}
