<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Collector\CoreStorageFacade;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\MainMenu\MainMenuItemFactory;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services {

	/**
	 * @var bool
	 */
	protected static $constructed = false;


	/**
	 * Services constructor.
	 */
	public function __construct() {
		if (self::$constructed === true) {
			// throw new \LogicException("Only one Instance of GlobalScreen-Services can be created, use it from \$DIC instead.");
		}
		self::$constructed = true;
	}


	/**
	 * @see MainMenuItemFactory
	 *
	 * @return MainMenuItemFactory
	 */
	public function mainmenu(): MainMenuItemFactory {
		return new MainMenuItemFactory();
	}


	/**
	 * @return CollectorFactory
	 */
	public function collector(): CollectorFactory {
		return new CollectorFactory();
	}


	/**
	 * @return StorageFacade
	 */
	public function storage(): StorageFacade {
		return new CoreStorageFacade();
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
