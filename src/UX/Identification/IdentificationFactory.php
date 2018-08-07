<?php namespace ILIAS\UX\Identification;

/**
 * Class IdentificationFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationFactory {

	/**
	 * @inheritdoc
	 */
	public function core(\ILIAS\UX\Provider\Provider $provider): ProviderInterface {
		return new Core(get_class($provider));
	}
}

