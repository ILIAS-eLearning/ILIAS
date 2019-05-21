<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class ilGSProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSProviderFactory extends ProviderFactory {

	/**
	 * @var Container
	 */
	private $dic;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
		parent::__construct(
			[], [], [], new ilMMItemInformation()
		);
	}


	/**
	 * @inheritDoc
	 */
	public function getMetaBarProvider(): array {
		//
		// ATTENTION: This is currently WIP, the Providers will be collected by
		// the same mechanism as the MainBarProviders (services.xml or modules.xml)
		//
		// Core
		$meta_bar_providers = [
			new ilSearchGSProvider($this->dic),
			new ilMMCustomProvider($this->dic),
		];
		// Plugins
		$this->appendPlugins($meta_bar_providers, StaticMetaBarProvider::class);

		$this->registerInternal($meta_bar_providers);

		return $meta_bar_providers;
	}


	/**
	 * @inheritDoc
	 */
	public function getMainBarProvider(): array {
		//
		// ATTENTION: This is currently WIP, the Providers will be collected by
		// the same mechanism as the MainBarProviders (services.xml or modules.xml)
		//
		$main_bar_providers = [];
		// Core
		foreach (ilGSProviderStorage::where(['purpose' => StaticMainMenuProvider::PURPOSE_MAINBAR])->get() as $provider_storage) {
			/**
			 * @var $provider_storage ilGSProviderStorage
			 */
			$main_bar_providers[] = $provider_storage->getInstance();
		}
		// Plugins
		$this->appendPlugins($main_bar_providers, StaticMainMenuProvider::class);

		$this->registerInternal($main_bar_providers);

		return $main_bar_providers;
	}


	/**
	 * @inheritDoc
	 */
	public function getToolProvider(): array {
		$tool_providers = [
			new ilStaffGSToolProvider($this->dic),
		];
		// Plugins
		$this->appendPlugins($tool_providers, DynamicToolProvider::class);

		$this->registerInternal($tool_providers);

		return $tool_providers;
	}


	/**
	 * @param array  $array_of_core_providers
	 * @param string $interface
	 */
	private function appendPlugins(array &$array_of_core_providers, string $interface): void {
		// Plugins
		static $plugin_providers;

		$plugin_providers = $plugin_providers ?? ilPluginAdmin::getAllGlobalScreenProviders();

		foreach ($plugin_providers as $provider) {
			if (is_a($provider, $interface)) {
				$array_of_core_providers[] = $provider;
			}
		}
	}
}
