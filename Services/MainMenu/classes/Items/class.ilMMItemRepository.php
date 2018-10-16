<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;

/**
 * Class ilMMItemRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemRepository extends ilMMAbstractRepository {

	/**
	 * @var \ILIAS\GlobalScreen\Provider\Provider[]
	 */
	private $providers = [];
	/**
	 * @var ilMMSortingAndTranslation
	 */
	private $sorting_and_translation;
	/**
	 * @var ilGSRepository
	 */
	private $gs;


	/**
	 * @inheritDoc
	 */
	public function __construct(StorageFacade $storage) {
		parent::__construct($storage);
		$this->gs = new ilGSRepository($storage);
		$this->sorting_and_translation = new ilMMSortingAndTranslation($this->storage);
		$this->providers = [];
		/**
		 * @var $provider_storage ilGSProviderStorage
		 */
		foreach (ilGSProviderStorage::get() as $provider_storage) {
			$this->providers[] = $provider_storage->getInstance();
		}
	}


	/**
	 * @return array
	 */
	public function getTopItems(): array {
		// sync
		$this->sync();

		return ilMMItemStorage::where(['parent_identification' => ''])->orderBy('position')->getArray();
	}


	/**
	 * @param \ILIAS\GlobalScreen\Identification\IdentificationInterface $identification
	 *
	 * @return ilMMItemFacade
	 */
	public function getItemFacade(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): ilMMItemFacade {
		return new ilMMItemFacade($identification, $this->providers);
	}


	/**
	 * @return array in the way of identification -> position
	 */
	public function getPositionsOfAllItems(): array {
		return ilMMItemStorage::getArray('identification', 'position');
	}


	private function sync(): bool {
		$synced = false;
		if ($synced === false || $synced === null) {
			foreach ($this->gs->getIdentificationsForPurpose(ilGSRepository::PURPOSE_MAIN_MENU) as $identification) {
				$item_storage = ilMMItemStorage::find($identification->serialize());
				/**
				 * @var $item \ILIAS\GlobalScreen\MainMenu\isChild|\ILIAS\GlobalScreen\MainMenu\isParent
				 */
				$item = $this->findItem($identification);
				if ($item_storage === null) {
					$item_storage = new ilMMItemStorage();
					$item_storage->setIdentification($identification->serialize());
					$item_storage->create();
				}
				if ($item instanceof \ILIAS\GlobalScreen\MainMenu\isChild) {
					$item_storage->setParentIdentification($item->getParent()->serialize());
				}
				$item_storage->update();
			}
			$synced = true;
		}

		return $synced;
	}


	private function findItem(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification): \ILIAS\GlobalScreen\MainMenu\isItem {
		global $DIC;

		return $DIC->globalScreen()->collector()->mainmenu($this->providers, $this->sorting_and_translation, $this->sorting_and_translation)->getSingleItem($identification);
	}
}
