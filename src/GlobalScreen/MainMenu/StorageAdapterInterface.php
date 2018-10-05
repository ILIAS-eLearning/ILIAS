<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\MainMenu\Slate\Slate;

/**
 * Interface StorageAdapterInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorageAdapterInterface {

	/**
	 * @param EntryInterface $entry
	 *
	 * @return EntryInterface
	 */
	public function storeEntry(EntryInterface $entry): EntryInterface;


	/**
	 * @param EntryInterface $entry
	 *
	 * @return EntryInterface
	 */
	public function readEntry(EntryInterface $entry): EntryInterface;


	/**
	 * @param EntryInterface $entry
	 *
	 * @return EntryInterface
	 */
	public function updateEntry(EntryInterface $entry): EntryInterface;


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
