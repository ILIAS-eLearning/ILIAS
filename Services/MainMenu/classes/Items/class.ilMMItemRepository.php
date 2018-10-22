<?php

use ILIAS\GlobalScreen\Collector\MainMenu\ItemInformation;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isChild;

/**
 * Class ilMMItemRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemRepository {

	/**
	 * @var bool
	 */
	private $synced = false;
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
	private $information;
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
		$this->information = new ilMMItemInformation($this->storage);
		$this->providers = $this->initProviders();
		$this->main_collector = $DIC->globalScreen()->collector()->mainmenu($this->providers, $this->information);
		$this->sync();
	}


	/**
	 * @return ItemInformation
	 */
	public function information(): ItemInformation {
		return $this->information;
	}


	/**
	 * @param string $class_name
	 *
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem
	 */
	public function getEmptyItemForTypeString(string $class_name): \ILIAS\GlobalScreen\MainMenu\isItem {
		global $DIC;

		return $DIC->globalScreen()->mainmenu()->custom($class_name, new  \ILIAS\GlobalScreen\Identification\NullIdentification());
	}


	public function clearCache() {
		$this->storage->cache()->flush();
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem|\ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem
	 * @throws Throwable
	 */
	public function getStackedTopItemsForPresentation(): array {
		$this->sync();

		$top_items = $this->main_collector->getStackedTopItemsForPresentation();

		return $top_items;
	}


	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem
	 * @throws Throwable
	 */
	public function getSingleItem(IdentificationInterface $identification): \ILIAS\GlobalScreen\MainMenu\isItem {
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
	public function getSubItemsForTable(): array {
		// sync
		$this->sync();
		$r = $this->storage->db()->query(
			"SELECT sub_items.*, top_items.position AS parent_position 
FROM il_mm_items AS sub_items 
LEFT JOIN il_mm_items AS top_items ON top_items.identification = sub_items.parent_identification
WHERE sub_items.parent_identification != '' ORDER BY top_items.position, parent_identification, sub_items.position ASC"
		);
		$return = [];
		while ($data = $this->storage->db()->fetchAssoc($r)) {
			$return[] = $data;
		}

		return $return;
	}


	/**
	 * @param IdentificationInterface|null $identification
	 *
	 * @return ilMMItemFacadeInterface
	 * @throws Throwable
	 */
	public function getItemFacade(IdentificationInterface $identification = null): ilMMItemFacadeInterface {
		if ($identification === null) {
			return new ilMMNullItemFacade(new \ILIAS\GlobalScreen\Identification\NullIdentification(), $this->main_collector);
		}
		if ($identification->getClassName() === ilMMCustomProvider::class) {
			return new ilMMCustomItemFacade($identification, $this->main_collector);
		}

		return new ilMMItemFacade($identification, $this->main_collector);
	}


	/**
	 * @param string $identification
	 *
	 * @return ilMMItemFacadeInterface
	 * @throws Throwable
	 */
	public function getItemFacadeForIdentificationString(string $identification): ilMMItemFacadeInterface {
		global $DIC;
		$id = $DIC->globalScreen()->identification()->fromSerializedIdentification($identification);

		return $this->getItemFacade($id);
	}


	private function sync(): bool {
		if ($this->synced === false || $this->synced === null) {
			$this->storage->db()->manipulate(
				"DELETE il_mm_items FROM il_mm_items 
  						LEFT JOIN il_gs_identifications  ON il_gs_identifications.identification= il_mm_items.identification 
      					WHERE il_gs_identifications.identification IS NULL"
			);
			foreach ($this->gs->getIdentificationsForPurpose(ilGSRepository::PURPOSE_MAIN_MENU) as $identification) {
				$this->getItemFacadeForIdentificationString($identification->serialize());
			}
			$this->synced = true;
		}

		return $this->synced;
	}


	public function getPossibleParentsForFormAndTable(): array {
		static $parents;
		if (is_null($parents)) {
			global $DIC;
			$parents = [];
			foreach ($this->getTopItems() as $top_item_identification => $data) {
				$identification = $DIC->globalScreen()->identification()->fromSerializedIdentification($top_item_identification);
				$item = $this->getSingleItem($identification);
				if ($item instanceof \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem) {
					$parents[$top_item_identification] = $this->getItemFacade($identification)
						->getDefaultTitle();
				}
			}
		}

		return $parents;
	}


	/**
	 * FSX get from Main
	 *
	 * @return array
	 */
	public function getPossibleSubItemTypesForForm(): array {
		return [
			\ILIAS\GlobalScreen\MainMenu\Item\Link::class => "Link",
			// \ILIAS\GlobalScreen\MainMenu\Item\RepositoryLink::class => "RepositoryLink",
		];
	}


	/**
	 * FSX get from Main
	 *
	 * @return array
	 */
	public function getPossibleTopItemTypesForForm(): array {
		return [
			\ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem::class => "TopParentItem",
			\ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem::class   => "TopLinkItem",
		];
	}


	public function updateItem(ilMMItemFacadeInterface $item_facade) {
		$item_facade->update();
		$this->storage->cache()->flush();
	}


	public function createItem(ilMMItemFacadeInterface $item_facade) {
		$item_facade->create();
		$this->storage->cache()->flush();
	}


	public function deleteItem(ilMMItemFacadeInterface $item_facade) {
		if ($item_facade->isCustom()) {
			$item_facade->delete();
			$this->storage->cache()->flush();
		}
	}
}
