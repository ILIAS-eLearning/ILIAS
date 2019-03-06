<?php namespace ILIAS\GlobalScreen\Scope\Context\Collector;

use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class MainContextCollector
 *
 * This Collector will collect and then provide all available contexts from
 * providers in the whole system.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainContextCollector {

	/**
	 * @var bool
	 */
	private static $constructed = false;
	/**
	 * @var array|Provider[]
	 */
	protected $providers;
	/**
	 * @var bool
	 */
	private $loaded = false;


	/**
	 * MainContextCollector constructor.
	 *
	 * @param array $providers
	 */
	public function __construct(array $providers) {
		if (self::$constructed === true) {
			throw new \LogicException("only one Instance of MainMenuMainCollector Collector is possible");
		}
		self::$constructed = true;
		$this->providers = $providers;
	}


	/**
	 * @return array
	 */
	public function getAvailableContexts(): array {
		return [];
	}
}
