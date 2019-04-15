<?php namespace ILIAS\Services\UICore\Page\Media;

/**
 * Class Js
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractCollection {

	/**
	 * @var array
	 */
	protected $items = [];


	public function clear() {
		$this->items = [];
	}


	/**
	 * @return array
	 */
	public function getItems(): array {
		return $this->items;
	}


	/**
	 * @return array
	 */
	public function getItemsInOrderOfDelivery(): array {
		return $this->items;
	}
}
