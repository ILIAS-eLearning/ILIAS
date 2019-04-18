<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Collector\CoreStorageFacade;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services {

	const MAINBAR_SERVICES = 'mainbar_services';
	const COLLECTOR_SERVICES = 'collector_services';
	const STORAGE_FACADE = 'storage_facade';
	const IDENTIFICATION_SERVICES = 'identification_services';
	private static $instance = null;
	/**
	 * @var array
	 */
	private static $instances = [];


	/**
	 * @return Services
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @see MainMenuItemFactory
	 *
	 * @return MainMenuItemFactory
	 */
	public function mainBar(): MainMenuItemFactory {
		return $this->get(MainMenuItemFactory::class);
	}


	/**
	 * @return CollectorFactory
	 */
	public function collector(): CollectorFactory {
		return $this->get(CollectorFactory::class);
	}


	/**
	 * @see IdentificationFactory
	 *
	 * @return IdentificationFactory
	 */
	public function identification(): IdentificationFactory {
		return $this->get(IdentificationFactory::class);
	}


	/**
	 * @param string $class_name
	 *
	 * @return mixed
	 */
	private function get(string $class_name) {
		if (!isset(self::$instances[$class_name])) {
			self::$instances[$class_name] = new $class_name();
		}

		return self::$instances[$class_name];
	}
}
