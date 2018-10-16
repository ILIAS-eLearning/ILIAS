<?php

use ILIAS\GlobalScreen\Collector\MainMenu\ItemInformation;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class ilMMItemInformation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemInformation implements ItemInformation {

	/**
	 * @var array
	 */
	private $items;
	/**
	 * @var StorageFacade
	 */
	private $storage;


	/**
	 * ilMMItemInformation constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		$this->storage = $storage;
		$this->items = ilMMItemStorage::getArray('identification', ['position', 'active']);
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
		return (int)$this->items[$item->getProviderIdentification()->serialize()]['position'];
	}


	/**
	 * @inheritDoc
	 */
	public function isItemActive(\ILIAS\GlobalScreen\MainMenu\isItem $item): bool {
		$serialize = $item->getProviderIdentification()->serialize();

		return $this->items[$serialize]['active'] === "1";
	}
}
