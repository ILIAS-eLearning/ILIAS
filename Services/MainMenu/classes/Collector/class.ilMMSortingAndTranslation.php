<?php

use ILIAS\GlobalScreen\Collector\MainMenu\ItemSorting;
use ILIAS\GlobalScreen\Collector\MainMenu\ItemTranslation;
use ILIAS\GlobalScreen\Collector\StorageFacade;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class ilMMSortingAndTranslation
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSortingAndTranslation implements ItemSorting, ItemTranslation {

	/**
	 * @var ilMMItemRepository
	 */
	private $provider;
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
		$this->provider = new ilMMItemRepository($storage);
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
		return 1;
	}


	/**
	 * @inheritDoc
	 */
	public function getPositionOfTopItem(isTopItem $top_item): int {
		$positions = $this->provider->getPositionsOfAllItems();

		return (int)$positions[$top_item->getProviderIdentification()->serialize()];
	}
}
