<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Interface isChild
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isChild extends isItem {

	/**
	 * As a developer, you provide the standard-parent Entry while creating yout entry.
	 * Please note that the effective parent can be changed by configuration.
	 *
	 * @param IdentificationInterface $identification
	 *
	 * @return isItem
	 */
	public function withParent(IdentificationInterface $identification): isItem;


	/**
	 * @return bool
	 */
	public function hasParent(): bool;


	/**
	 * @return IdentificationInterface
	 */
	public function getParent(): IdentificationInterface;
}
