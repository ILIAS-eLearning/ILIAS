<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Services;

/**
 * Class AbstractProvider
 *
 * @package ILIAS\GlobalScreen\Provider
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