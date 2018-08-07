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
	public function withChildren(array $children): EntryInterface {
		$clone = clone($this);
		$clone->children = $children;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function appendChild(EntryInterface $child): EntryInterface {
		$this->children[] = $child;

		return $this;
	}
}
