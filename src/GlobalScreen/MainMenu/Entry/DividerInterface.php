<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\ChildEntryInterface;

/**
 * Interface DividerInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DividerInterface extends ChildEntryInterface {

	/**
	 * @param string $title
	 *
	 * @return DividerInterface
	 */
	public function withTitle(string $title): DividerInterface;


	/**
	 * @return string
	 */
	public function getTitle(): string;
}
