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
	public function withChildren(array $children): ParentInterface;


	/**
	 * Attention
	 *
	 * @param ChildEntryInterface $child
	 *
	 * @return EntryInterface
	 */
	public function appendChild(ChildEntryInterface $child): ParentInterface;
}
