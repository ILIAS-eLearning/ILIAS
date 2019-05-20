<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class AbstractChildItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractChildItem extends AbstractBaseItem implements isItem, isChild {

	/**
	 * @var IdentificationInterface
	 */
	protected $parent;


	/**
	 * @inheritDoc
	 */
	public function withParent(IdentificationInterface $identification): isItem {
		$clone = clone $this;
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


	/**
	 * @inheritDoc
	 */
	public function overrideParent(IdentificationInterface $identification): isChild {
		$this->parent = $identification;

		return $this;
	}
}
