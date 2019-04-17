<?php namespace ILIAS\GlobalScreen\Scope\View\MetaContent\Media;

/**
 * Class OnLoadCodeCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class OnLoadCodeCollection extends AbstractCollection {

	/**
	 * @param OnLoadCode $item
	 */
	public function addItem(OnLoadCode $item) {
		$this->items[] = $item;
	}


	/**
	 * @return OnLoadCode[]
	 */
	public function getItems(): array {
		return parent::getItems();
	}


	/**
	 * @return OnLoadCode[]
	 */
	public function getItemsInOrderOfDelivery(): array {
		return parent::getItemsInOrderOfDelivery();
	}
}