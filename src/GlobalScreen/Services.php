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
	 * @var bool
	 */
	private static $constructed = false;


	/**
	 * Services constructor.
	 */
	public function __construct() {
		if (self::$constructed === true) {
			throw new \LogicException("Only one Instance of GlobalScreen-Services can be created, use it from \$DIC instead.");
		}
		self::$constructed = true;
	}


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
