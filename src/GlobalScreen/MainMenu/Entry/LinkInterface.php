<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\ChildEntryInterface;

/**
 * Interface LinkInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface LinkInterface extends ChildEntryInterface {

	/**
	 * @param string $title
	 *
	 * @return LinkInterface
	 */
	public function withTitle(string $title): LinkInterface;


	/**
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * @param string $alt_text
	 *
	 * @return LinkInterface
	 */
	public function withAltText(string $alt_text): LinkInterface;


	/**
	 * @return string
	 */
	public function getAltText(): string;


	/**
	 * @param string $action
	 *
	 * @return LinkInterface
	 */
	public function withAction(string $action): LinkInterface;


	/**
	 * @return string
	 */
	public function getAction(): string;
}
