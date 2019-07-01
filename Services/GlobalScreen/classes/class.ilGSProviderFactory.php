<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;
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
     * @var array
     */
    private $class_loader;
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
        $this->class_loader = include "Services/GlobalScreen/artifacts/global_screen_providers.php";
    }


    /**
     * @inheritDoc
     */

    public function getFinalModificationProvider() : array
    {
        static $providers;
        if (!isset($providers)) {
            $providers = [];

            foreach (ilPluginAdmin::getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk") as $plugin) {
                /**
                 * @var $pl ilUserInterfaceHookPlugin
                 */
                $pl = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $plugin);
                $gui_class = $pl->getUIClassInstance();

                if (is_a($gui_class, FinalModificationProvider::class)) {
                    $providers[] = $gui_class;
                }
            }

            $this->registerInternal($providers);
        }

        return $providers;
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticMetaBarProvider::class);

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
        $this->appendCore($providers, StaticMainMenuProvider::class);

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
        $this->appendCore($providers, DynamicToolProvider::class);

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
     * @param string $interface
     */
    private function appendCore(array &$array_of_providers, string $interface) : void
    {
        foreach ($this->class_loader[$interface] as $class_name) {
            if ($this->isInstanceCreationPossible($class_name)) {
                $array_of_providers[] = new $class_name($this->dic);
            }
        }
    }
}
