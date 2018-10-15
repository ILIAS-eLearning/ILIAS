<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Services;

/**
 * Class AbstractProvider
 *
 * @package ILIAS\GlobalScreen\Provider
 */
abstract class AbstractProvider implements Provider {

	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
	}


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
		return $this->dic->globalScreen();
	}


	/**
	 * @inheritDoc
	 */
	final public function getFullyQualifiedClassName(): string {
		return self::class;
	}
}