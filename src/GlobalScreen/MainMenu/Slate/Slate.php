<?php namespace ILIAS\GlobalScreen\MainMenu\Slate;

use ILIAS\GlobalScreen\MainMenu\AbstractParentEntry;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\hasIcon;
use ILIAS\GlobalScreen\MainMenu\isTopNode;

/**
 * Class Slate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Slate extends AbstractParentEntry implements isTopNode, hasTitle, hasIcon {

	/**
	 * @var bool
	 */
	protected $sticky = false;
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
	 * @return Slate
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


	/**
	 * @param bool $is_sticky
	 *
	 * @return Slate
	 */
	public function withSticky(bool $is_sticky): Slate {
		$clone = clone($this);
		$clone->sticky = $is_sticky;

		return $clone;
	}


	/**
	 * @return bool
	 */
	public function isSticky(): bool {
		return $this->sticky;
	}


	/**
	 * @param string $path_to_svg_icon
	 *
	 * @return Slate
	 */
	public function withIconPath(string $path_to_svg_icon): hasIcon {
		$clone = clone($this);
		$clone->icon_path = $path_to_svg_icon;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getIconPath(): string {
		return $this->icon_path;
	}
}
