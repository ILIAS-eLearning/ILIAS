<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;

/**
 * Class Divider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Divider extends AbstractChildEntry implements DividerInterface {

	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @inheritDoc
	 */
	public function withTitle(string $title): DividerInterface {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}
}
