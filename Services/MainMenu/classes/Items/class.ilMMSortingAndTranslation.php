<?php

use ILIAS\GlobalScreen\Collector\MainMenu\ItemSorting;
use ILIAS\GlobalScreen\Collector\MainMenu\ItemTranslation;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class ilMMSortingAndTranslation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSortingAndTranslation implements ItemSorting, ItemTranslation {

	/**
	 * @var array
	 */
	private $positions;
	/**
	 * @var StorageFacade
	 */
	private $storage;


	/**
	 * ilMMSortingAndTranslation constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		$this->storage = $storage;
		$this->positions = ilMMItemStorage::getArray('identification', 'position');
	}


	/**
	 * @inheritDoc
	 */
	public function translateItemForUser(hasTitle $item): hasTitle {
		if ($item instanceof hasTitle) {
			// $item = $item->withTitle("LOREM");
		}

		return $item;
	}


	/**
	 * @inheritDoc
	 */
	public function getPositionOfSubItem(isChild $child): int {
		return $this->getPosition($child);
	}


	/**
	 * @inheritDoc
	 */
	public function getPositionOfTopItem(isTopItem $top_item): int {
		return $this->getPosition($top_item);
	}


	private function getPosition(\ILIAS\GlobalScreen\MainMenu\isItem $item): int {
		return (int)$this->positions[$item->getProviderIdentification()->serialize()];
	}
}
