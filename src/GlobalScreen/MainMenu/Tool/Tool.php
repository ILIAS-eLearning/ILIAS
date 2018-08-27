<?php namespace ILIAS\GlobalScreen\MainMenu\Slate;

use ILIAS\GlobalScreen\MainMenu\AbstractParentEntry;
use ILIAS\GlobalScreen\MainMenu\Tool\ToolInterfaceInterface;

/**
 * Class Tool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tool extends AbstractParentEntry implements ToolInterfaceInterface {

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
	 * @return ToolInterfaceInterface
	 */
	public function withTitle(string $title): ToolInterfaceInterface {
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
	public function withIconPath(string $path_to_svg_icon): ToolInterfaceInterface {
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
