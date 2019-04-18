<?php

use ILIAS\GlobalScreen\Collector\CoreStorageFacade;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Handler\TypeHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Identification\NullPluginIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Complex;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\LinkList;

/**
 * Class ilMMItemRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemRepository {

	/**
	 * @var \ILIAS\GlobalScreen\Services
	 */
	private $services;
	/**
	 * @var bool
	 */
	private $synced = false;
	/**
	 * @var StorageFacade
	 */
	private $storage;
	/**
	 * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector
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
	 * ilMMItemRepository constructor.
	 *
	 * @throws Throwable
	 */
	public function __construct() {
		global $DIC;
		$this->storage = new CoreStorageFacade();
		$this->gs = new ilGSRepository();
		$this->information = new ilMMItemInformation($this->storage);
		$this->providers = $this->initProviders();
		$this->main_collector = $DIC->globalScreen()->collector()->mainmenu($this->providers, $this->information);
		$this->services = $DIC->globalScreen();
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
	 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem
	 */
	public function getEmptyItemForTypeString(string $class_name): isItem {
		return $this->services->mainBar()->custom($class_name, new  NullIdentification());
	}


	public function clearCache() {
		$this->storage->cache()->flush();
	}


	/**
	 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopLinkItem|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem
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
	 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem
	 * @throws Throwable
	 */
	public function getSingleItem(IdentificationInterface $identification): \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem {
		return $this->main_collector->getSingleItem($identification);
	}


	/**
	 * @return array
	 */
	private function initProviders(): array {
		$providers = [];
		// Core
		foreach (ilGSProviderStorage::where(['purpose' => 'mainmenu'])->get() as $provider_storage) {
			/**
			 * @var $provider_storage ilGSProviderStorage
			 */
			$providers[] = $provider_storage->getInstance();
		}
		foreach (ilPluginAdmin::getAllGlobalScreenProviders() as $provider) {
			$providers[] = $provider;
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
		if ($identification === null || $identification instanceof NullIdentification || $identification instanceof NullPluginIdentification) {
			return new ilMMNullItemFacade($identification ? $identification : new NullIdentification(), $this->main_collector);
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
		$id = $this->services->identification()->fromSerializedIdentification($identification);

		return $this->getItemFacade($id);
	}


	private function sync(): bool {
		if ($this->synced === false || $this->synced === null) {
			foreach (ilPluginAdmin::getAllGlobalScreenProviders() as $provider) {
				foreach ($provider->getAllIdentifications() as $identification) {
					ilGSIdentificationStorage::registerIdentification($identification, $provider);
				}
			}

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
			$parents = [];
			foreach ($this->getTopItems() as $top_item_identification => $data) {
				$identification = $this->services->identification()->fromSerializedIdentification($top_item_identification);
				$item = $this->getSingleItem($identification);
				if ($item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem) {
					$parents[$top_item_identification] = $this->getItemFacade($identification)
						->getDefaultTitle();
				}
			}
		}

		return $parents;
	}


	/**
	 * @deprecated
	 * @see getPossibleSubItemTypesWithInformation
	 *
	 * @return array
	 */
	public function getPossibleSubItemTypesForForm(): array {
		$types = [];
		foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
			if ($information->isCreationPrevented()) {
				continue;
			}
			if ($information->isChild()) {
				$types[$information->getType()] = $information->getTypeNameForPresentation();
			}
		}

		return $types;
	}


	/**
	 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation[]
	 */
	public function getPossibleSubItemTypesWithInformation(): array {
		$types = [];
		foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
			if ($information->isCreationPrevented()) {
				continue;
			}
			if ($information->isChild()) {
				$types[$information->getType()] = $information;
			}
		}

		return $types;
	}


	/**
	 * @deprecated
	 * @see getPossibleTopItemTypesWithInformation
	 * @return array
	 */
	public function getPossibleTopItemTypesForForm(): array {
		$types = [];
		foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
			if ($information->isTop()) {
				$types[$information->getType()] = $information->getTypeNameForPresentation();
			}
		}

		return $types;
	}


	/**
	 * @return \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation[]
	 */
	public function getPossibleTopItemTypesWithInformation(): array {
		$types = [];
		foreach ($this->main_collector->getTypeInformationCollection()->getAll() as $information) {
			if ($information->isTop()) {
				$types[$information->getType()] = $information;
			}
		}

		return $types;
	}


	/**
	 * @deprecated
	 *
	 * @param string $type
	 *
	 * @return TypeHandler
	 */
	public function getTypeHandlerForType(string $type): TypeHandler {
		$item = $this->services->mainBar()->custom($type, new NullIdentification());

		return $this->main_collector->getHandlerForItem($item);
	}


	/**
	 * @param ilMMItemFacadeInterface $item_facade
	 */
	public function updateItem(ilMMItemFacadeInterface $item_facade) {
		$item_facade->update();
		$this->storage->cache()->flush();
	}


	/**
	 * @param ilMMItemFacadeInterface $item_facade
	 */
	public function createItem(ilMMItemFacadeInterface $item_facade) {
		$item_facade->create();
		$this->storage->cache()->flush();
	}


	/**
	 * @param ilMMItemFacadeInterface $item_facade
	 */
	public function deleteItem(ilMMItemFacadeInterface $item_facade) {
		if ($item_facade->isCustom()) {
			$item_facade->delete();
			$this->storage->cache()->flush();
		}
	}
}
