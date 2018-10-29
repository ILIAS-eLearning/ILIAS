<?php namespace ILIAS\GlobalScreen\MainMenu\TopItem;

use ILIAS\GlobalScreen\MainMenu\AbstractParentItem;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isTopItem;

/**
 * Class TopParentItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItem extends AbstractParentItem implements isTopItem, hasTitle {

	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $icon_path = "";


	/**
	 * @param string $title
	 *
	 * @return TopParentItem
	 */
	public function withTitle(string $title): hasTitle {
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
