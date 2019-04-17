<?php namespace ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media;

/**
 * Class InlineCssCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class InlineCssCollection extends AbstractCollection {

	/**
	 * @param InlineCss $item
	 */
	public function addItem(InlineCss $item) {
		$this->items[] = $item;
	}


	/**
	 * @return InlineCss[]
	 */
	public function getItems(): array {
		return parent::getItems();
	}


	/**
	 * @return InlineCss[]
	 */
	public function getItemsInOrderOfDelivery(): array {
		return parent::getItemsInOrderOfDelivery();
	}
}