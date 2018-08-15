<?php namespace ILIAS\UX\MainMenu;

/**
 * Interface AsyncContentEntry
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface AsyncContentEntry {

	/**
	 * @return string
	 */
	public function getAsyncContentURL(): string;


	/**
	 * @param string $async_content_url
	 *
	 * @return EntryInterface
	 */
	public function withAsyncContentURL(string $async_content_url): EntryInterface;
}
