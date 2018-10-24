<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class AbstractBaseItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractChildItem extends AbstractBaseItem implements isChild {

	/**
	 * @var IdentificationInterface
	 */
	protected $parent;


	/**
	 * @inheritDoc
	 */
	public function withParent(IdentificationInterface $identification): isItem {
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
