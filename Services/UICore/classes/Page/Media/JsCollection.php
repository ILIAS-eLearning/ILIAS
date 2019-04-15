<?php namespace ILIAS\Services\UICore\Page\Media;

/**
 * Class JsCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class JsCollection extends AbstractCollection {

	/**
	 * @param Js $item
	 */
	public function addItem(Js $item) {
		$this->items[] = $item;
	}


	/**
	 * @return Js[]
	 */
	public function getItems(): array {
		return parent::getItems();
	}


	/**
	 * @return Js[]
	 */
	public function getItemsInOrderOfDelivery(): array {
		$ordered = [];
		foreach ($this->getItems() as $js) {
			$ordered[$js->getBatch()][] = $js;
		}
		$ordered_all = [];
		foreach ($ordered as $item) {
			foreach ($item as $js) {
				$ordered_all[] = $js;
			}
		}

		return $ordered_all;
	}
}