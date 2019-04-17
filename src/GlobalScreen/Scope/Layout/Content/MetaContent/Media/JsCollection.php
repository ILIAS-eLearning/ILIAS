<?php namespace ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media;

/**
 * Class JsCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class JsCollection extends AbstractCollection {

	/**
	 * @var array
	 */
	protected $path_storage = [];


	/**
	 * @param Js $item
	 */
	public function addItem(Js $item) {
		if (isset($this->path_storage[$item->getContent()])) {
			if ((int)$this->path_storage[$item->getContent()] > $item->getBatch()) {
				// unset($this->path_storage[$item->getContent()]);
				$this->storeItem($item);
			}
		} else {
			$this->storeItem($item);
		}
	}


	private function storeItem(js $item) {
		$this->items[$item->getContent()] = $item;
		$this->path_storage[$item->getContent()] = $item->getBatch();
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
			$ordered['pos_' . (string)$js->getBatch()][] = $js;
		}
		ksort($ordered);
		$ordered_all = [];
		foreach ($ordered as $item) {
			foreach ($item as $js) {
				$ordered_all[] = $js;
			}
		}

		return $ordered_all;
	}
}