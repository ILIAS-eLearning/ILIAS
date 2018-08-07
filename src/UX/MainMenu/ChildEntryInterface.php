<?php namespace ILIAS\UX\MainMenu;

use ILIAS\UX\Identification\IdentificationInterface;

/**
 * Interface ChildEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ChildEntryInterface extends EntryInterface {

	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return EntryInterface
	 */
	public function withSuggestedParent(IdentificationInterface $identification): EntryInterface;


	/**
	 * @return bool
	 */
	public function hasSuggestedParent(): bool;


	/**
	 * @return bool
	 */
	public function hasParent(): bool;


	/**
	 * @return IdentificationInterface
	 */
	public function getSuggestedParent(): IdentificationInterface;


	/**
	 * @return IdentificationInterface
	 */
	public function getParent(): IdentificationInterface;
}
