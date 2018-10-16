<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Main;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isItem;
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
	 * @var Main
	 */
	private $main_collector;
	/**
	 * @var array
	 */
	private $providers;
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
		global $DIC;
		$this->storage = $storage;
		$this->providers = $this->initProviders();
		$this->main_collector = $DIC->globalScreen()->collector()->mainmenu($this->providers);
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
		return $this->main_collector->getStackedTopItems($with_invisible);
	}


	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return isItem
	 */
	public function getSingleItem(IdentificationInterface $identification): isItem {
		return $this->main_collector->getSingleItem($identification);
	}


	/**
	 * @return array
	 */
	private function initProviders(): array {
		$providers = [];
		foreach (ilGSProviderStorage::get() as $provider_storage) {
			/**
			 * @var $provider_storage ilGSProviderStorage
			 */
			$providers[] = $provider_storage->getInstance();
		}

		return $providers;
	}
}
