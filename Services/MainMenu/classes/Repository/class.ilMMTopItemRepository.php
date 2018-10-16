<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;

/**
 * Class ilMMTopItemRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemRepository extends ilMMAbstractRepository {

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
	}


	/**
	 * @return array
	 */
	public function getTopItems(): array {
		// sync
		$this->sync();

		return ilMMItemStorage::where(['parent_identification' => ''])->getArray();
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
		$providers = [];
		/**
		 * @var $provider_storage ilGSProviderStorage
		 */
		foreach (ilGSProviderStorage::get() as $provider_storage) {
			$providers[] = $provider_storage->getInstance();
		}

		return $DIC->globalScreen()->collector()->mainmenu($providers)->getSingleItem($identification);
	}
}
