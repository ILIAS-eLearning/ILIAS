<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class ilGSProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSProviderFactory implements ProviderFactory
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
     * @var ItemInformation
     */
    private $main_menu_item_information = null;
    /**
     * @var Provider[]
     */
    protected $all_providers;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->main_menu_item_information = new ilMMItemInformation();
        $this->class_loader = include "Services/GlobalScreen/artifacts/global_screen_providers.php";
    }


    /**
     * @inheritDoc
     */
    public function getModificationProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, ModificationProvider::class);

        return $providers;
    }


    /**
     * @param array $providers
     */
    protected function registerInternal(array $providers)
    {
        array_walk(
            $providers, function (Provider $item) {
            $this->all_providers[get_class($item)] = $item;
        }
        );
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
     * @inheritDoc
     */
    public function getNotificationsProvider() : array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, NotificationProvider::class);

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


    /**
     * @inheritDoc
     */
    public function getMainBarItemInformation() : ItemInformation
    {
        return $this->main_menu_item_information;
    }


    /**
     * @inheritDoc
     */
    public function getProviderByClassName(string $class_name) : Provider
    {
        if (!$this->isInstanceCreationPossible($class_name) || !$this->isRegistered($class_name)) {
            throw new \LogicException("the GlobalScreen-Provider $class_name is not available");
        }

        return $this->all_providers[$class_name];
    }


    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        try {
            return class_exists($class_name);
        } catch (\Throwable $e) {
            return false;
        }
    }


    /**
     * @inheritDoc
     */
    public function isRegistered(string $class_name) : bool
    {
        return isset($this->all_providers[$class_name]);
    }
}
