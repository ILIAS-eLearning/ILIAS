<?php namespace ILIAS\UX\Provider;

use ILIAS\UX\Services;

/**
 * Interface Provider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Provider {

	/**
	 * You will get an Instance of the UX-Services to use i you provider.
	 * Do not create own Instances
	 *
	 * @param Services $services
	 */
	public function inject(Services $services);


	/**
	 * This is the first method which would be called and is in most cases a
	 * simple return true or return false without any futher checks.
	 *
	 * @return bool
	 */
	public function mayHaveElements(): bool;
}
