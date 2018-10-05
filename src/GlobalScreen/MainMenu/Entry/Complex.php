<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;
use ILIAS\GlobalScreen\MainMenu\AsyncContentEntryInterface;

/**
 * Class Divider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Complex extends AbstractChildEntry implements AsyncContentEntryInterface {

	/**
	 * @var string
	 */
	protected $async_content_url = '';


	/**
	 * @inheritDoc
	 */
	public function getAsyncContentURL(): string {
		return $this->async_content_url;
	}


	/**
	 * @inheritDoc
	 */
	public function withAsyncContentURL(string $async_content_url): AsyncContentEntryInterface {
		$clone = clone($this);
		$clone->async_content_url = $async_content_url;

		return $clone;
	}
}
