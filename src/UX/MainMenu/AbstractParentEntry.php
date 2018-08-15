<?php namespace ILIAS\UX\MainMenu;

/**
 * Class AbstractParentEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractParentEntry extends AbstractBaseEntry implements ParentInterface {

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
	public function withChildren(array $children): ParentInterface {
		$clone = clone($this);
		$clone->children = $children;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function appendChild(ChildEntryInterface $child): ParentInterface {
		$this->children[] = $child;

		return $this;
	}
}
