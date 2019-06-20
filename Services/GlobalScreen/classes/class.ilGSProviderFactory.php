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
            [], [], [], new ilMMItemInformation()
        );
    }


    /**
     * @inheritDoc
     */
    public function getFinalPageHandlers() : array
    {
        return [new ilUIHookPluginsFinalPageHandler()];
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticMetaBarProvider::PURPOSE_MBS);

        // Plugins
        $this->appendPlugins($providers, StaticMetaBarProvider::class);

        $this->registerInternal($providers);

        return $providers;
    }


    /**
     * @inheritDoc
     */
    public function getMainBarProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticMainMenuProvider::PURPOSE_MAINBAR);

        // Plugins
        $this->appendPlugins($providers, StaticMainMenuProvider::class);

        $this->registerInternal($providers);

        return $providers;
    }


    /**
     * @inheritDoc
     */
    public function getToolProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, DynamicToolProvider::PURPOSE_TOOLS);

        // Plugins
        $this->appendPlugins($providers, DynamicToolProvider::class);

        $this->registerInternal($providers);

        return $providers;
    }


    /**
     * @param array  $array_of_core_providers
     * @param string $interface
     */
    private function appendPlugins(array &$array_of_core_providers, string $interface) : void
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
     * @param string $purpose
     */
    private function appendCore(array &$array_of_providers, string $purpose) : void
    {
        // // Core
        foreach (ilGSProviderStorage::where(['purpose' => $purpose])->get() as $provider_storage) {
            /**
             * @var $provider_storage ilGSProviderStorage
             */
            if ($this->isInstanceCreationPossible($provider_storage->getProviderClass())) {
                $array_of_providers[] = $provider_storage->getInstance();
            }
        }
    }
}
