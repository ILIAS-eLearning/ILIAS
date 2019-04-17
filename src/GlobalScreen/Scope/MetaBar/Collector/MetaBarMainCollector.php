<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class MetaBarMainCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarMainCollector {

	/**
	 * @var StaticMetaBarProvider[]
	 */
	private $providers = [];


	/**
	 * MetaBarMainCollector constructor.
	 *
	 * @param array $providers
	 */
	public function __construct(array $providers) {
		$this->providers = $providers;
	}


	/**
	 * @return isItem[]
	 */
	public function getStackedItems(): array {
		$items = [];
		foreach ($this->providers as $provider) {
			$items = array_merge($items, $provider->getMetaBarItems());
		}

		return $items;
	}
}
