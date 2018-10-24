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
	protected $gs;


	/**
	 * @inheritDoc
	 */
	public function inject(Services $services) {
		$this->gs = $services;
	}


	/**
	 * @return Services
	 */
	protected function globalScreen(): Services {
		return $this->gs;
	}


	/**
	 * @inheritDoc
	 */
	final public function getFullyQualifiedClassName(): string {
		return self::class;
	}
}