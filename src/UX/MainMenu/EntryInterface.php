<?php namespace ILIAS\UX\MainMenu;

use ILIAS\UX\Identification\IdentificationInterface;

/**
 * Interface IFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface EntryInterface {

	/**
	 * @return IdentificationInterface
	 */
	public function getProviderIdentification(): IdentificationInterface;


	/**
	 * @param callable $is_visible
	 *
	 * @return EntryInterface|ChildEntryInterface
	 */
	public function withVisibilityCallable(callable $is_visible): EntryInterface;


	/**
	 * @return bool
	 */
	public function isVisible(): bool;


	/**
	 * @param callable $is_active
	 *
	 * @return EntryInterface|ChildEntryInterface
	 */
	public function withActiveCallable(callable $is_active): EntryInterface;


	/**
	 * @return bool
	 */
	public function isActive(): bool;


	/**
	 * @param bool $available
	 *
	 * @return EntryInterface
	 */
	public function withAvailable(bool $available): EntryInterface;


	/**
	 * @return bool
	 */
	public function isAvailable(): bool;
}
