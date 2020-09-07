<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;

/**
 * Class ilGSProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSProviderFactory extends ProviderFactory
{

    /**
     * @var Container
     */
    private $dic;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;

        parent::__construct(
            [],
            new ilMMItemInformation()
        );
    }


    /**
     * @inheritDoc
     */
    public function getMainBarProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticMainMenuProvider::class);

        // Plugins
        $this->appendPlugins($providers, StaticMainMenuProvider::class);

        $this->registerInternal($providers);

        return $providers;
    }


    /**
     * @param array  $array_of_core_providers
     * @param string $interface
     */
    private function appendPlugins(array &$array_of_core_providers, string $interface)
    {
        // Plugins
        static $plugin_providers;

        $plugin_providers = $plugin_providers ?? ilPluginAdmin::getAllGlobalScreenProviders();

        foreach ($plugin_providers as $provider) {
            if (is_a($provider, $interface)) {
                $array_of_core_providers[] = $provider;
            }
        }
    }


    /**
     * @param array  $array_of_providers
     * @param string $interface
     */
    private function appendCore(array &$array_of_providers, string $interface)
    {
        static $provider_storages;

        /**
         * @var $provider_storages ilGSProviderStorage[]
         */
        $provider_storages = $provider_storages ?? ilGSProviderStorage::get();

        $interface_map = [
            StaticMainMenuProvider::class => StaticMainMenuProvider::PURPOSE_MAINBAR,
        ];

        foreach ($provider_storages as $provider_storage) {
            if ($provider_storage->getPurpose() === $interface_map[$interface]) {
                if ($this->isInstanceCreationPossible($provider_storage->getProviderClass())) {
                    $array_of_providers[] = $provider_storage->getInstance();
                }
            }
        }
    }
}
