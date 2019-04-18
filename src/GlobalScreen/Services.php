<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Collector\CoreStorageFacade;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\Layout\Definition\LayoutDefinitionFactory;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services {

	private static $instance = null;
	/**
	 * @var array
	 */
	private static $services = [];


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
	 * @return MetaBarItemFactory
	 */
	public function metaBar(): MetaBarItemFactory {
		return $this->get(MetaBarItemFactory::class);
	}


	/**
	 * @return LayoutServices
	 */
	public function layout(): LayoutServices {
		return $this->get(LayoutServices::class);
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
		if (!isset(self::$services[$class_name])) {
			self::$services[$class_name] = new $class_name();
		}

		return self::$services[$class_name];
	}
}
