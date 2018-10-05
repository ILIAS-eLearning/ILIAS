<?php namespace ILIAS\GlobalScreen\MainMenu\Adapter;

use ILIAS\GlobalScreen\MainMenu\isEntry;
use ILIAS\GlobalScreen\MainMenu\Slate\Slate;

/**
 * Interface StorageAdapter
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorageAdapter {

	/**
	 * @param isEntry $entry
	 *
	 * @return isEntry
	 */
	public function storeEntry(isEntry $entry): isEntry;


	/**
	 * @param isEntry $entry
	 *
	 * @return isEntry
	 */
	public function readEntry(isEntry $entry): isEntry;


	/**
	 * @param isEntry $entry
	 *
	 * @return isEntry
	 */
	public function updateEntry(isEntry $entry): isEntry;


	/**
	 * @param Slate $slate
	 */
	public function storeSlate(Slate $slate);


	/**
	 * @param Slate $slate
	 */
	public function readSlate(Slate $slate);


	/**
	 * @param Slate $slate
	 */
	public function updateSlate(Slate $slate);
}
