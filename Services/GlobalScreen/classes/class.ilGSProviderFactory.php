<?php

use Composer\Autoload\ClassLoader;
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
		// $providers = [];
		// // Core
		// foreach (ilGSProviderStorage::where(['purpose' => StaticMetaBarProvider::PURPOSE_MBS])->get() as $provider_storage) {
		// 	/**
		// 	 * @var $provider_storage ilGSProviderStorage
		// 	 */
		// 	$providers[] = $provider_storage->getInstance();
		// }
		//
		// ATTENTION: This is currently WIP, the Providers will be collected by
		// the same mechanism as the MainBarProviders (services.xml or modules.xml)
		//
		$providers = [
			new ilSearchGSProvider($this->dic),
			new ilMMCustomTopBarProvider($this->dic),
		];
		// Plugins
		$this->appendPlugins($providers, StaticMetaBarProvider::class);

		$this->registerInternal($providers);

		return $providers;
	}


	/**
	 * @inheritDoc
	 */
	public function getMainBarProvider(): array {
		$providers = [];
		// Core
		foreach (ilGSProviderStorage::where(['purpose' => StaticMainMenuProvider::PURPOSE_MAINBAR])->get() as $provider_storage) {
			/**
			 * @var $provider_storage ilGSProviderStorage
			 */
			$providers[] = $provider_storage->getInstance();
		}

		// Plugins
		$this->appendPlugins($providers, StaticMainMenuProvider::class);

		$this->registerInternal($providers);

		return $providers;
	}


	/**
	 * @inheritDoc
	 */
	public function getToolProvider(): array {
		// $providers = [];
		// // Core
		// foreach (ilGSProviderStorage::where(['purpose' => DynamicToolProvider::PURPOSE_TOOLS])->get() as $provider_storage) {
		// 	/**
		// 	 * @var $provider_storage ilGSProviderStorage
		// 	 */
		// 	$providers[] = $provider_storage->getInstance();
		// }

		// ATTENTION: This is currently WIP, the Providers will be collected by
		// the same mechanism as the MainBarProviders (services.xml or modules.xml)

		$providers = [
			new ilStaffGSToolProvider($this->dic),
		];
		// Plugins
		$this->appendPlugins($providers, DynamicToolProvider::class);

		$this->registerInternal($providers);

		return $providers;
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
