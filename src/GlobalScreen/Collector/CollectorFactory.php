<?php namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Collector\MainMenu\ItemInformation;
use ILIAS\GlobalScreen\Collector\MainMenu\ItemSorting;
use ILIAS\GlobalScreen\Collector\MainMenu\ItemTranslation;
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
	 * @param array                $providers
	 * @param ItemInformation|null $information
	 *
	 * @return Main
	 */
	public function mainmenu(array $providers, ItemInformation $information = null): Main {
		if (!isset(self::$instances['mainmenu'])) {
			self::$instances['mainmenu'] = new Main($providers, $information);
		}

		return self::$instances['mainmenu'];
	}
}
