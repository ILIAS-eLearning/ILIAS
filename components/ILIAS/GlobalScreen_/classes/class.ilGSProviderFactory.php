<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderCollection;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Toast\Provider\ToastProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\GlobalScreen\Scope\Footer\Provider\StaticFooterProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilGSProviderFactory implements ProviderFactory
{
    /**
     * @var null|ProviderCollection[]
     */
    private ?array $plugin_provider_collections = null;
    private array $class_loader;
    private readonly ItemInformation $main_menu_item_information;
    private readonly ilFooterCustomItemInformation $footer_item_information;
    /**
     * @var Provider[]
     */
    protected array $all_providers = [];

    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    /**
     * @inheritDoc
     */
    public function __construct(private readonly Container $dic)
    {
        $this->main_menu_item_information = new ilMMItemInformation();
        $this->footer_item_information = new ilFooterCustomItemInformation($dic);
        /** @noRector */
        $this->class_loader = include ilGlobalScreenBuildProviderMapObjective::PATH();
        $this->component_repository = $this->dic["component.repository"];
        $this->component_factory = $this->dic["component.factory"];
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


    protected function registerInternal(array $providers): void
    {
        array_walk(
            $providers,
            function (Provider $item): void {
                $this->all_providers[$item::class] = $item;
            }
        );
    }


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

    public function getFooterProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, StaticFooterProvider::class);

        // Plugins
        //        $this->initPlugins();
        //        foreach ($this->plugin_provider_collections as $collection) {
        //            $provider = $collection->getMainBarProvider();
        //            if ($provider) {
        //                $providers[] = $provider;
        //            }
        //        }

        $this->registerInternal($providers);

        return $providers;
    }


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


    public function getToastsProvider(): array
    {
        $providers = [];
        // Core
        $this->appendCore($providers, ToastProvider::class);

        // Plugins
        $this->initPlugins();
        foreach ($this->plugin_provider_collections as $collection) {
            $provider = $collection->getToastProvider();
            if ($provider) {
                $providers[] = $provider;
            }
        }

        $this->registerInternal($providers);

        return $providers;
    }


    private function appendCore(array &$array_of_providers, string $interface): void
    {
        foreach ($this->class_loader[$interface] ?? [] as $class_name) {
            if ($this->isInstanceCreationPossible($class_name)) {
                try {
                    $array_of_providers[] = new $class_name($this->dic);
                } catch (Throwable) {
                }
            }
        }
    }

    public function getMainBarItemInformation(): ItemInformation
    {
        return $this->main_menu_item_information;
    }

    public function getFooterItemInformation(): \ILIAS\GlobalScreen\Scope\Footer\Collector\Information\ItemInformation
    {
        return $this->footer_item_information;
    }

    public function getProviderByClassName(string $class_name): Provider
    {
        if (!$this->isInstanceCreationPossible($class_name) || !$this->isRegistered($class_name)) {
            throw new LogicException("the GlobalScreen-Provider $class_name is not available");
        }

        return $this->all_providers[$class_name];
    }


    public function isInstanceCreationPossible(string $class_name): bool
    {
        try {
            return class_exists($class_name);
        } catch (Throwable) {
            return false;
        }
    }


    public function isRegistered(string $class_name): bool
    {
        return isset($this->all_providers[$class_name]);
    }
}
