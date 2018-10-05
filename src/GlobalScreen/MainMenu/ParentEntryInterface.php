<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Interface ParentEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ParentEntryInterface extends EntryInterface {

	/**
	 * @return EntryInterface[]
	 */
	public function getChildren(): array;


	/**
	 * @param EntryInterface[] $children
	 *
	 * @return ParentEntryInterface
	 */
	public function withChildren(array $children): ParentEntryInterface;


	/**
	 * Attention
	 *
	 * @param ChildEntryInterface $child
	 *
	 * @return ParentEntryInterface
	 */
	public function appendChild(ChildEntryInterface $child): ParentEntryInterface;
}
