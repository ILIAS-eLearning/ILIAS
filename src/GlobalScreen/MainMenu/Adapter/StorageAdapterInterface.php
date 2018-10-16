<?php namespace ILIAS\GlobalScreen\MainMenu\Adapter;

use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;

/**
 * Interface StorageAdapter
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorageAdapter {

	/**
	 * @param isItem $item
	 *
	 * @return isItem
	 */
	public function storeItem(isItem $item): isItem;


	/**
	 * @param isItem $item
	 *
	 * @return isItem
	 */
	public function readItem(isItem $item): isItem;


	/**
	 * @param isItem $item
	 *
	 * @return isItem
	 */
	public function updateItem(isItem $item): isItem;


	/**
	 * @param isTopItem $top_item
	 */
	public function storeTopItem(isTopItem $top_item);


	/**
	 * @param isTopItem $top_item
	 */
	public function readTopItem(isTopItem $top_item);


	/**
	 * @param isTopItem $top_item
	 */
	public function updateTopItem(isTopItem $top_item);
}
