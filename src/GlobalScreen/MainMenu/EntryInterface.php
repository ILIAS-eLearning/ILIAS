<?php namespace ILIAS\GlobalScreen\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

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
	 * Pass a callable which can decide whether your element is visible for
	 * the current user
	 *
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
	 * Pass a callable which can decide whether your element is in a active
	 * state (e.g. the Repository-Slate is active whenever a user is in the
	 * repository)
	 *
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
	 * Pass a callable which can decide wheter your element is available in
	 * general, e.g. return false for the Badges entry when the Badges-Service
	 * is disabled.
	 *
	 * @param callable $is_avaiable
	 *
	 * @return EntryInterface|ChildEntryInterface
	 */
	public function withAvailableCallable(callable $is_avaiable): EntryInterface;


	/**
	 * @return bool
	 */
	public function isAvailable(): bool;
}
