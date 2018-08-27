<?php namespace ILIAS\GlobalScreen\MainMenu\Slate;

use ILIAS\GlobalScreen\MainMenu\AsyncContentEntryInterface;
use ILIAS\GlobalScreen\MainMenu\EntryInterface;
use ILIAS\GlobalScreen\MainMenu\IconEntryInterface;
use ILIAS\GlobalScreen\MainMenu\ParentEntryInterface;
use ILIAS\GlobalScreen\MainMenu\TopEntryInterface;

/**
 * Interface SlateInterfaceInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface SlateInterfaceInterface extends EntryInterface, ParentEntryInterface, TopEntryInterface, IconEntryInterface {

	/**
	 * @param string $title
	 *
	 * @return SlateInterfaceInterface
	 */
	public function withTitle(string $title): SlateInterfaceInterface;


	/**
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * @param bool $is_sticky
	 *
	 * @return SlateInterfaceInterface
	 */
	public function withSticky(bool $is_sticky): SlateInterfaceInterface;


	/**
	 * @return bool
	 */
	public function isSticky(): bool;
}
