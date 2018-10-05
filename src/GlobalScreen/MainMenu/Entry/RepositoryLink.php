<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\AbstractChildEntry;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ilLink;

/**
 * Class Link
 *
 * Attention: This is not the same as the \ILIAS\UI\Component\Link\Link. Please
 * read the difference between GlobalScreen and UI in the README.md of the GlobalScreen Service.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RepositoryLink extends AbstractChildEntry implements hasTitle, hasAction {

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
	 * @param string $title
	 *
	 * @return RepositoryLink
	 */
	public function withTitle(string $title): hasTitle {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @param string $alt_text
	 *
	 * @return RepositoryLink
	 */
	public function withAltText(string $alt_text): RepositoryLink {
		$clone = clone($this);
		$clone->alt_text = $alt_text;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getAltText(): string {
		return $this->alt_text;
	}


	/**
	 * @return string
	 */
	public function getAction(): string {
		return ilLink::_getLink($this->ref_id);
	}


	/**
	 * @param string $action
	 *
	 * @return hasAction
	 */
	public function withAction(string $action): hasAction {
		throw new \LogicException("Please use withRefId() instead");
	}


	/**
	 * @param int $ref_id
	 *
	 * @return RepositoryLink
	 */
	public function withRefId(int $ref_id): RepositoryLink {
		$this->ref_id = $ref_id;
	}


	/**
	 * @return int
	 */
	public function getRefId(): int {
		return $this->ref_id;
	}
}
