<?php

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderCollection;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilGSProviderFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSProviderFactory implements ProviderFactory
{
    /**
     * @var ProviderCollection[]
     */
    private ?array $plugin_provider_collections = null;
    private array $class_loader;
    private Container $dic;
    private ItemInformation $main_menu_item_information;
    /**
     * @var Provider[]
     */
    protected array $all_providers;

    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->main_menu_item_information = new ilMMItemInformation();
        /** @noRector */
        $this->class_loader = include "Services/GlobalScreen/artifacts/global_screen_providers.php";
        $this->component_repository = $dic["component.repository"];
        $this->component_factory = $dic["component.factory"];
    }

    private function initPlugins(): void
    {
        if (!is_array($this->plugin_provider_collections)) {
            $this->plugin_provider_collections = [];
            foreach ($this->component_repository->getPlugins() as $plugin) {
                if (!$plugin->isActive()) {
                    continue;
                }
                $pl = $this->component_factory->getPlugin($plugin->getId());
                $this->plugin_provider_collections[] = $pl->getGlobalScreenProviderCollection();
            }
        }
    }

    /**
     * @param array $providers
     */
    protected function registerInternal(array $providers): void
    {
        array_walk(
            $providers,
            function (Provider $item): void {
                $this->all_providers[get_class($item)] = $item;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getMainBarProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticMainMenuProvider::class);

        // Plugins
        $this->initPlugins();
        foreach ($this->plugin_provider_collections as $collection) {
            $provider = $collection->getMainBarProvider();
            if ($provider) {
                $providers[] = $provider;
            }
        }

        $this->registerInternal($providers);

        return $providers;
    }

    /**
     * @inheritDoc
     */
    public function getMetaBarProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticMetaBarProvider::class);

        // Plugins
        $this->initPlugins();
        foreach ($this->plugin_provider_collections as $collection) {
            $provider = $collection->getMetaBarProvider();
            if ($provider) {
                $providers[] = $provider;
            }
        }

        $this->registerInternal($providers);

        return $providers;
    }

    /**
     * @inheritDoc
     */
    public function getToolProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, DynamicToolProvider::class);

        // Plugins
        $this->initPlugins();
        foreach ($this->plugin_provider_collections as $collection) {
            $provider = $collection->getToolProvider();
            if ($provider) {
                $providers[] = $provider;
            }
        }

        $this->registerInternal($providers);

        return $providers;
    }

    /**
     * @inheritDoc
     */
    public function getModificationProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, ModificationProvider::class);

        // Plugins
        $this->initPlugins();
        foreach ($this->plugin_provider_collections as $collection) {
            $provider = $collection->getModificationProvider();
            if ($provider) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationsProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, NotificationProvider::class);

        // Plugins
        $this->initPlugins();
        foreach ($this->plugin_provider_collections as $collection) {
            $provider = $collection->getNotificationProvider();
            if ($provider) {
                $providers[] = $provider;
            }
        }

        $this->registerInternal($providers);

        return $providers;
    }

    /**
     * @param array  $array_of_providers
     * @param string $interface
     */
    private function appendCore(array &$array_of_providers, string $interface): void
    {
        foreach ($this->class_loader[$interface] as $class_name) {
            if ($this->isInstanceCreationPossible($class_name)) {
                try {
                    $array_of_providers[] = new $class_name($this->dic);
                } catch (Throwable $e) {
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getMainBarItemInformation(): ItemInformation
    {
        return $this->main_menu_item_information;
    }

    /**
     * @inheritDoc
     */
    public function getProviderByClassName(string $class_name): Provider
    {
        if (!$this->isInstanceCreationPossible($class_name) || !$this->isRegistered($class_name)) {
            throw new LogicException("the GlobalScreen-Provider $class_name is not available");
        }

        return $this->all_providers[$class_name];
    }

    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name): bool
    {
        try {
            return class_exists($class_name);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isRegistered(string $class_name): bool
    {
        return isset($this->all_providers[$class_name]);
    }
}
