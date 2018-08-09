<?php namespace ILIAS\UX\MainMenu;

use ILIAS\UX\Identification\IdentificationInterface;

/**
 * Class AbstractBaseEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractChildEntry extends AbstractBaseEntry implements ChildEntryInterface {

	/**
	 * @var IdentificationInterface
	 */
	protected $suggested_parent;
	/**
	 * @var IdentificationInterface
	 */
	protected $parent;


	/**
	 * @inheritDoc
	 */
	public function withParent(IdentificationInterface $identification): EntryInterface {
		$clone = clone($this);
		$clone->suggested_parent = $identification;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function hasParent(): bool {
		return ($this->suggested_parent instanceof IdentificationInterface);
	}


	/**
	 * @inheritDoc
	 */
	public function getParent(): IdentificationInterface {
		return $this->suggested_parent;
	}


	/**
	 * @inheritDoc
	 */
	public function hasParent(): bool {
		return ($this->parent instanceof IdentificationInterface);
	}


	/**
	 * @inheritDoc
	 */
	public function getParent(): IdentificationInterface {
		return $this->parent;
	}
}
