<?php namespace ILIAS\GlobalScreen\MainMenu\Slate;

use ILIAS\GlobalScreen\MainMenu\AbstractParentEntry;
use ILIAS\GlobalScreen\MainMenu\EntryInterface;
use ILIAS\GlobalScreen\MainMenu\IconEntryInterface;

/**
 * Class Slate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Slate extends AbstractParentEntry implements SlateInterfaceInterface {

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
	 * @return SlateInterfaceInterface
	 */
	public function withTitle(string $title): SlateInterfaceInterface {
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
	 * @inheritDoc
	 */
	public function withSticky(bool $is_sticky): SlateInterfaceInterface {
		$clone = clone($this);
		$clone->sticky = $is_sticky;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isSticky(): bool {
		return $this->sticky;
	}


	/**
	 * @inheritDoc
	 */
	public function withIconPath(string $path_to_svg_icon): IconEntryInterface {
		$clone = clone($this);
		$clone->icon_path = $path_to_svg_icon;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getIconPath(): string {
		return $this->icon_path;
	}
}
