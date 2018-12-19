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
	 * @var  bool
	 */
	protected $visible_title = false;
	/**
	 * @var string
	 */
	protected $title = '';


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


	/**
	 * @param bool $visible_title
	 *
	 * @return Separator
	 */
	public function withVisibleTitle(bool $visible_title): Separator {
		$clone = clone($this);
		$clone->visible_title = $visible_title;

		return $clone;
	}


	/**
	 * @return bool
	 */
	public function isTitleVisible(): bool {
		return $this->visible_title;
	}
}
