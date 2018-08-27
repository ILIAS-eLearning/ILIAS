<?php namespace ILIAS\UX\MainMenu\Slate;

use ILIAS\UX\MainMenu\AsyncContentEntry;
use ILIAS\UX\MainMenu\EntryInterface;
use ILIAS\UX\MainMenu\IconEntryInterface;
use ILIAS\UX\MainMenu\ParentEntryInterface;
use ILIAS\UX\MainMenu\TopEntryInterface;

/**
 * Interface SlateInterfaceInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface SlateInterfaceInterface extends EntryInterface, ParentEntryInterface, AsyncContentEntry, TopEntryInterface, IconEntryInterface {

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
