<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Services;

/**
 * Interface Provider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Provider {

	/**
	 * You will get an Instance of the GlobalScreen-Services to use i you provider.
	 * Do not create own Instances of the GlobalScreen-Services.
	 *
	 * All Providers will be called at some point, the GlobalScreen-Services will take
	 * care of the initialisation of your providers by creating instances and by
	 * injecting the one and only instance of the GlobalScreen-Services using this method.
	 *
	 * @param Services $services
	 */
	public function inject(Services $services);
}
