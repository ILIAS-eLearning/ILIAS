<?php namespace ILIAS\UX\Provider;

/**
 * Interface DynamicProvider
 *
 * Needs JF decision whenever a new DynamicProvider is implemented
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface DynamicProvider extends Provider {

	/**
	 * @return bool
	 */
	public function isGloballyActive():bool;


	/**
	 * @return bool
	 */
	public function isActiveForContext():bool;


	public function isActiveForCurrentUser():bool;

	/**
	 * ATTENTION: Implement your isCurrentlyActive()-Method as performant as possible
	 *
	 * @return bool
	 */
	public function isCurrentlyActive(): bool;
}
