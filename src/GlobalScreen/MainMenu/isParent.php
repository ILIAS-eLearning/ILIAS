<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Interface isParent
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isParent extends isEntry {

	/**
	 * @return isEntry[]
	 */
	public function getChildren(): array;


	/**
	 * @param isEntry[] $children
	 *
	 * @return isParent
	 */
	public function withChildren(array $children): isParent;


	/**
	 * Attention
	 *
	 * @param isChild $child
	 *
	 * @return isParent
	 */
	public function appendChild(isChild $child): isParent;
}
