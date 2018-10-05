<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class AbstractBaseEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractChildEntry extends AbstractBaseEntry implements isChild {

	/**
	 * @var IdentificationInterface
	 */
	protected $parent;


	/**
	 * @inheritDoc
	 */
	public function withParent(IdentificationInterface $identification): isEntry {
		$clone = clone($this);
		$clone->parent = $identification;

		return $clone;
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
