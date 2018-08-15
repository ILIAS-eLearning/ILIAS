<?php namespace ILIAS\UX\Provider;

use ILIAS\UX\Services;

/**
 * Class AbstractProvider
 *
 * @package ILIAS\UX\Provider
 */
abstract class AbstractProvider implements Provider {

	/**
	 * @var Services
	 */
	protected $ux;


	/**
	 * @inheritDoc
	 */
	public function inject(Services $services) {
		$this->ux = $services;
	}


	/**
	 * @inheritDoc
	 */
	public function mayHaveElements(): bool {
		return true;
	}
}