<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;

/**
 * Class ilMainMenuCollector
 *
 * This Collector will collect and then provide all available slates from the
 * providers in the whole system, stack them and enrich them will their content
 * based on the configuration in "Administration".
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMainMenuCollector {

	/**
	 * @var StorageFacade
	 */
	protected $storage;


	/**
	 * ilMainMenuCollector constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		$this->storage = $storage;
	}


	/**
	 * This will return all available slates, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Dividers to avoid double Dividers
	 * in the UI.
	 *
	 * @param bool $with_invisible
	 *
	 * @return TopParentItem[]
	 */
	public function getStackedTopItems(bool $with_invisible = false): array {
		global $DIC;
		$providers = [];
		foreach (ilGSProviderStorage::get() as $provider_storage) {
			/**
			 * @var $provider_storage ilGSProviderStorage
			 */
			$providers[] = $provider_storage->getInstance();
		}

		return $DIC->globalScreen()->collector()->mainmenu($providers)->getStackedTopItems($with_invisible);
	}
}
