<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\MainMenu\MainMenuEntryFactory;

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
