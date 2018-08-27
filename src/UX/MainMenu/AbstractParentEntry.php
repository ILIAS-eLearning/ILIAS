<?php namespace ILIAS\UX\MainMenu;

/**
 * Class AbstractParentEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractParentEntry extends AbstractBaseEntry implements ParentEntryInterface {

	/**
	 * @var EntryInterface[]
	 */
	protected $children = [];


	/**
	 * @inheritDoc
	 */
	public function getChildren(): array {
		return $this->children;
	}


	/**
	 * @inheritDoc
	 */
	public function withChildren(array $children): ParentEntryInterface {
		$clone = clone($this);
		$clone->children = $children;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function appendChild(ChildEntryInterface $child): ParentEntryInterface {
		$this->children[] = $child;

		return $this;
	}
}
