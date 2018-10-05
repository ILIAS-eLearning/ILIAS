<?php namespace ILIAS\GlobalScreen\MainMenu\Slate;

use ILIAS\GlobalScreen\MainMenu\AbstractParentEntry;
use ILIAS\GlobalScreen\MainMenu\Tool\ToolInterfaceInterface;

/**
 * Class Tool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tool extends AbstractParentEntry {

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
	 * @return Tool
	 */
	public function withTitle(string $title): Tool {
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
	 * @param string $path_to_svg_icon
	 *
	 * @return Tool
	 */
	public function withIconPath(string $path_to_svg_icon): Tool {
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
