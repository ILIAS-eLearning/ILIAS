<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;
use ILIAS\GlobalScreen\MainMenu\hasTitle;

/**
 * Class Divider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Divider extends AbstractChildEntry implements hasTitle {

	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @param string $title
	 *
	 * @return Divider
	 */
	public function withTitle(string $title): hasTitle {
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
