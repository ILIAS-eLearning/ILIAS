<?php namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\MetaBarMainCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class CollectorFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorFactory {

	const SCOPE_MAINBAR = 'mainbar';
	/**
	 * @var array
	 */
	protected static $instances = [];


	/**
	 * @param array                $providers
	 * @param ItemInformation|null $information
	 *
	 * @return MainMenuMainCollector
	 * @throws \Throwable
	 */
	public function mainmenu(array $providers, ItemInformation $information = null): MainMenuMainCollector {
		if (!isset(self::$instances[self::SCOPE_MAINBAR])) {
			self::$instances[self::SCOPE_MAINBAR] = new MainMenuMainCollector($providers, $information);
		}

		return self::$instances[self::SCOPE_MAINBAR];
	}


	/**
	 * @return MetaBarMainCollector
	 */
	public function metaBar(): MetaBarMainCollector {
		if (!isset(self::$instances[StaticMetaBarProvider::PURPOSE_MBS])) {
			global $DIC;
			$providers = [new \ilSearchGSProvider($DIC), new \ilMMCustomProvider($DIC)];

			self::$instances[StaticMetaBarProvider::PURPOSE_MBS] = new MetaBarMainCollector($providers);
		}

		return self::$instances[StaticMetaBarProvider::PURPOSE_MBS];
	}
}
