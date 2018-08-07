<?php namespace ILIAS\UX\MainMenu\Entry;

use ILIAS\UX\MainMenu\AbstractChildEntry;

/**
 * Class Link
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Link extends AbstractChildEntry implements LinkInterface {

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
}
