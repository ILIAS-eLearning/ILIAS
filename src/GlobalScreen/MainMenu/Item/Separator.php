<?php namespace ILIAS\GlobalScreen\MainMenu\Item;

use ILIAS\GlobalScreen\MainMenu\AbstractChildItem;
use ILIAS\GlobalScreen\MainMenu\hasTitle;

/**
 * Class Separator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Separator extends AbstractChildItem implements hasTitle {

	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @param string $title
	 *
	 * @return Separator
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
