<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;

/**
 * Class Link
 *
 * Attention: This is not the same as the \ILIAS\UI\Component\Link\Link. Please
 * read the difference between GlobalScreen and UI in the README.md of the GlobalScreen Service.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Link extends AbstractChildEntry implements LinkInterface {

	/**
	 * @var bool
	 */
	protected $is_external_action;
	/**
	 * @var string
	 */
	protected $action;
	/**
	 * @var string
	 */
	protected $alt_text;
	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @inheritDoc
	 */
	public function withTitle(string $title): LinkInterface {
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
	public function withAltText(string $alt_text): LinkInterface {
		$clone = clone($this);
		$clone->alt_text = $alt_text;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getAltText(): string {
		return $this->alt_text;
	}


	/**
	 * @inheritDoc
	 */
	public function withAction(string $action): LinkInterface {
		$clone = clone($this);
		$clone->action = $action;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getAction(): string {
		return $this->action;
	}


	/**
	 * @inheritDoc
	 */
	public function withIsLinkToExternalAction(bool $is_external): LinkInterface {
		$clone = clone $this;
		$clone->is_external_action = $is_external;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function isLinkWithExternalAction(): bool {
		return $this->is_external_action;
	}
}
