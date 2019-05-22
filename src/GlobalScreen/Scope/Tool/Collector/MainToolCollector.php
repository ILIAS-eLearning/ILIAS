<?php namespace ILIAS\GlobalScreen\Scope\Tool\Collector;

use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class MainToolCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainToolCollector {

	/**
	 * @var array
	 */
	private $tools;
	/**
	 * @var DynamicToolProvider[]
	 */
	private $providers = [];


	/**
	 * MainToolCollector constructor.
	 *
	 * @param DynamicToolProvider[] $providers
	 */
	public function __construct(array $providers) {
		$this->providers = $providers;
		$this->tools = [];
		$this->initTools();
	}


	private function initTools() {
		global $DIC;
		$called_contexts = $DIC->navigationContext()->stack();

		foreach ($this->providers as $provider) {
			$context_collection = $provider->isInterestedInContexts();
			if ($context_collection->hasMatch($called_contexts)) {
				$this->tools = array_merge($this->tools, $provider->getToolsForContextStack($called_contexts));
			}
		}
	}


	/**
	 * @return Tool[]
	 */
	public function getTools(): array {
		return $this->tools;
	}


	public function hasTools(): bool {
		return true;
	}
}
