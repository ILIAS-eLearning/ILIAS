<?php namespace ILIAS\GlobalScreen\MainMenu;

/**
 * Interface AsyncContentEntryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface AsyncContentEntryInterface {

	/**
	 * @return string
	 */
	public function getAsyncContentURL(): string;


	/**
	 * @param string $async_content_url
	 *
	 * @return AsyncContentEntryInterface
	 */
	public function withAsyncContentURL(string $async_content_url): AsyncContentEntryInterface;
}
