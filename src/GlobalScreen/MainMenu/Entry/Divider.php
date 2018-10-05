<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;

/**
 * Class Divider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Divider extends AbstractChildEntry {

	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @param string $title
	 *
	 * @return Divider
	 */
	public function withTitle(string $title): Divider {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}
}
