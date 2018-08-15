<?php namespace ILIAS\UX\MainMenu\Slate;

use ILIAS\UX\MainMenu\AbstractParentEntry;
use ILIAS\UX\MainMenu\EntryInterface;

/**
 * Class Slate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Slate extends AbstractParentEntry implements SlateInterfaceInterface {

	/**
	 * @var bool
	 */
	protected $sticky = false;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $async_content_url = '';


	/**
	 * @param string $title
	 *
	 * @return SlateInterfaceInterface
	 */
	public function withTitle(string $title): SlateInterfaceInterface {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @inheritDoc
	 */
	public function withSticky(bool $is_sticky): SlateInterfaceInterface {
		$clone = clone($this);
		$clone->sticky = $is_sticky;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isSticky(): bool {
		return $this->sticky;
	}


	/**
	 * @inheritDoc
	 */
	public function getAsyncContentURL(): string {
		return $this->async_content_url;
	}


	/**
	 * @inheritDoc
	 */
	public function withAsyncContentURL(string $async_content_url): EntryInterface {
		$clone = clone($this);
		$clone->async_content_url = $async_content_url;

		return $clone;
	}
}
