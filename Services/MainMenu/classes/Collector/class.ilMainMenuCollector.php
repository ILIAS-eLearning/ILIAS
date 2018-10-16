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
	 * @var ilMMItemRepository
	 */
	private $repository;
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
		$sorting_and_translation = new ilMMSortingAndTranslation($storage);
		$this->main_collector = $DIC->globalScreen()->collector()->mainmenu($this->providers, $sorting_and_translation, $sorting_and_translation);
		$this->repository = new ilMMItemRepository($storage);
	}


	/**
	 * @return array
	 */
	public function getStackedTopItems(): array {
		return $this->main_collector->getStackedTopItemsForPresentation();
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


	/**
	 * @return ilMMItemRepository
	 */
	public function repository(): ilMMItemRepository {
		return $this->repository;
	}
}
