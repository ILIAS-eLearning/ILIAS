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
	 * @param isItem $entry
	 *
	 * @return isItem
	 */
	public function storeEntry(isItem $entry): isItem;


	/**
	 * @param isItem $entry
	 *
	 * @return isItem
	 */
	public function readEntry(isItem $entry): isItem;


	/**
	 * @param isItem $entry
	 *
	 * @return isItem
	 */
	public function updateEntry(isItem $entry): isItem;


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
