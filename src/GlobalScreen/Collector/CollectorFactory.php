<?php namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class CollectorFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorFactory {

	/**
	 * @var array
	 */
	protected static $instances = [];


	/**
	 * @param Provider[] $providers
	 *
	 * @return Main
	 */
	public function mainmenu(array $providers): Main {
		if (!isset(self::$instances['mainmenu'])) {
			self::$instances['mainmenu'] = new Main($providers);
		}

		return self::$instances['mainmenu'];
	}
}
