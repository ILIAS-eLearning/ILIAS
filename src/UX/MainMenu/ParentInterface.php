<?php namespace ILIAS\UX\MainMenu;

/**
 * Interface ParentInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ParentInterface extends EntryInterface {

	/**
	 * @return EntryInterface[]
	 */
	public function getChildren(): array;


	/**
	 * @param EntryInterface[] $children
	 *
	 * @return EntryInterface
	 */
	public function withChildren(array $children): EntryInterface;


	/**
	 * @param EntryInterface $child
	 *
	 * @return EntryInterface
	 */
	public function appendChild(EntryInterface $child): EntryInterface;
}
