<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Interface hasTitle
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasTitle {

	/**
	 * @param string $title
	 *
	 * @return hasTitle
	 */
	public function withTitle(string $title): hasTitle;


	/**
	 * @return string
	 */
	public function getTitle(): string;
}
