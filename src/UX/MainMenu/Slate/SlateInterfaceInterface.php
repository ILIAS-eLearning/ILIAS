<?php namespace ILIAS\UX\MainMenu\Slate;

use ILIAS\UX\MainMenu\AsyncContentEntry;
use ILIAS\UX\MainMenu\EntryInterface;
use ILIAS\UX\MainMenu\ParentInterface;
use ILIAS\UX\MainMenu\TopEntryInterface;

/**
 * Interface SlateInterfaceInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface SlateInterfaceInterface extends EntryInterface, ParentInterface, AsyncContentEntry, TopEntryInterface {

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
