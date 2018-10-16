<?php namespace ILIAS\GlobalScreen\Collector;

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
	 * @param Provider[]      $providers
	 *
	 * @param ItemSorting     $sorting
	 * @param ItemTranslation $translation
	 *
	 * @return Main
	 */
	public function mainmenu(array $providers, ItemSorting $sorting = null, ItemTranslation $translation= null): Main {
		if (!isset(self::$instances['mainmenu'])) {
			self::$instances['mainmenu'] = new Main($providers, $sorting, $translation);
		}

		return self::$instances['mainmenu'];
	}
}
