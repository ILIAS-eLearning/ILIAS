<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\MainMenu\isChild;

/**
 * Class ilMMItemRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemRepository {

	/**
	 * @var StorageFacade
	 */
	private $storage;
	/**
	 * @var \ILIAS\GlobalScreen\Collector\MainMenu\Main
	 */
	private $main_collector;
	/**
	 * @var \ILIAS\GlobalScreen\Provider\Provider[]
	 */
	private $providers = [];
	/**
	 * @var ilMMItemInformation
	 */
	private $sorting_and_translation;
	/**
	 * @var ilGSRepository
	 */
	private $gs;


	/**
	 * ilMainMenuCollector constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		global $DIC;
		$this->storage = $storage;
		$this->gs = new ilGSRepository($storage);
		$this->sorting_and_translation = new ilMMItemInformation($this->storage);
		$this->providers = $this->initProviders();
		$sorting_and_translation = new ilMMItemInformation($storage);
		$this->main_collector = $DIC->globalScreen()->collector()->mainmenu($this->providers, $sorting_and_translation, $sorting_and_translation);
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem|\ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem
	 */
	public function getStackedTopItemsForPresentation(): array {
		$this->sync();

		return $this->main_collector->getStackedTopItemsForPresentation();
	}


	/**
	 * @param \ILIAS\GlobalScreen\Identification\IdentificationInterface $identification
	 *
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem
	 */
	public function getSingleItem(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): \ILIAS\GlobalScreen\MainMenu\isItem {
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
		return $this;
	}


	/**
	 * @return array
	 */
	public function getTopItems(): array {
		// sync
		$this->sync();

		return ilMMItemStorage::where(" parent_identification = '' OR parent_identification IS NULL ")->orderBy('position')->getArray();
	}


	/**
	 * @return array
	 */
	public function getSubItems(): array {
		// sync
		$this->sync();
		$r = $this->storage->db()->query(
			"SELECT sub_items.*, top_items.position AS parent_position 
FROM il_mm_items AS sub_items 
JOIN il_mm_items AS top_items ON top_items.identification = sub_items.parent_identification
WHERE sub_items.parent_identification != '' ORDER BY top_items.position, sub_items.position ASC"
		);
		$return = [];
		while ($data = $this->storage->db()->fetchAssoc($r)) {
			$return[] = $data;
		}

		return $return;
	}


	/**
	 * @param \ILIAS\GlobalScreen\Identification\IdentificationInterface $identification
	 *
	 * @return ilMMItemFacade
	 */
	public function getItemFacade(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): ilMMItemFacade {
		return new ilMMItemFacade($identification, $this->providers);
	}


	public function getItemFacadeForIdentificationString(string $identification): ilMMItemFacade {
		global $DIC;
		$id = $DIC->globalScreen()->identification()->fromSerializedIdentification($identification);

		return $this->getItemFacade($id);
	}


	private function sync(): bool {
		$synced = false;
		if ($synced === false || $synced === null) {
			foreach ($this->gs->getIdentificationsForPurpose(ilGSRepository::PURPOSE_MAIN_MENU) as $identification) {
				$this->getItemFacadeForIdentificationString($identification->serialize());
			}
			$synced = true;
		}

		return $synced;
	}


	public function updateItem(ilMMItemFacade $item_facade) {
		$item_facade->update();
		$this->storage->cache()->flush();
	}


	public function createItem(ilMMItemFacade $item_facade) {
		$item_facade->create();
		$this->storage->cache()->flush();
	}


	private function findItem(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): \ILIAS\GlobalScreen\MainMenu\isItem {
		global $DIC;

		return $DIC->globalScreen()->collector()->mainmenu($this->providers, $this->sorting_and_translation, $this->sorting_and_translation)->getSingleItem($identification);
	}
}
