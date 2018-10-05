<?php namespace ILIAS\GlobalScreen\MainMenu\Entry;

use ILIAS\GlobalScreen\MainMenu\ChildEntryInterface;

/**
 * Interface RepositoryLinkInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RepositoryLinkInterface extends ChildEntryInterface {

	/**
	 * @param string $title
	 *
	 * @return RepositoryLink
	 */
	public function withTitle(string $title): RepositoryLink;


	/**
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * @param string $alt_text
	 *
	 * @return RepositoryLink
	 */
	public function withAltText(string $alt_text): RepositoryLink;


	/**
	 * @return string
	 */
	public function getAltText(): string;


	/**
	 * @param int $ref_id
	 *
	 * @return RepositoryLink
	 */
	public function withRefId(int $ref_id): RepositoryLink;


	/**
	 * @return int
	 */
	public function getRefId(): int;


	/**
	 * @return string
	 */
	public function getAction(): string;
}
