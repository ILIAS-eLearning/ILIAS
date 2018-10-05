<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;
use ilLink;

/**
 * Class Link
 *
 * Attention: This is not the same as the \ILIAS\UI\Component\Link\Link. Please
 * read the difference between GlobalScreen and UI in the README.md of the GlobalScreen Service.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RepositoryLink extends AbstractChildEntry implements RepositoryLinkInterface {

	/**
	 * @var int
	 */
	protected $ref_id = 0;
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
	public function withTitle(string $title): RepositoryLink {
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
	public function withAltText(string $alt_text): RepositoryLink {
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
	public function getAction(): string {
		return ilLink::_getLink($this->ref_id);
	}


	/**
	 * @inheritDoc
	 */
	public function withRefId(int $ref_id): RepositoryLink {
		$this->ref_id = $ref_id;
	}


	/**
	 * @inheritDoc
	 */
	public function getRefId(): int {
		return $this->ref_id;
	}
}
