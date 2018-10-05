<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Class AbstractParentEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractParentEntry extends AbstractBaseEntry implements isParent {

	/**
	 * @var isEntry[]
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
	public function withChildren(array $children): isParent {
		$clone = clone($this);
		$clone->children = $children;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function appendChild(isChild $child): isParent {
		$this->children[] = $child;

		return $this;
	}
}
