<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Interface ChildEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ChildEntryInterface extends EntryInterface {

	/**
	 * As a developer, you provide the standard-parent Entry while creating yout entry.
	 * Please note that the effective parent can be changed by configuration.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return EntryInterface
	 */
	public function withParent(IdentificationInterface $identification): EntryInterface;


	/**
	 * @return bool
	 */
	public function hasParent(): bool;


	/**
	 * @return IdentificationInterface
	 */
	public function getParent(): IdentificationInterface;
}
