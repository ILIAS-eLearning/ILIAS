<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilGlobalCacheMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        if (!$client_ini) {
            return;
        }

        $settings = new ilGlobalCacheSettings();
        $settings->readFromIniFile($client_ini);

        $service = ilGlobalCache::lookupServiceClassName($settings->getService());
        $storage->storeConfigText(
            "service",
            $service,
            "The backend that is used for the ILIAS cache."
        );

        $servers = ilMemcacheServer::get();
        if (
            $service === ilGlobalCache::lookupServiceClassName(ilGlobalCache::TYPE_MEMCACHED) &&
            count($servers) > 0
        ) {
            $server_collection = [];
            foreach ($servers as $server) {
                $active = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_BOOL,
                    $server->isActive()
                );
                $host = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $server->getHost()
                );
                $port = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $server->getPort()
                );
                $weight = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_TEXT,
                    $server->getWeight()
                );

                $server_collection[] = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_COLLECTION,
                    [
                        "active" => $active,
                        "host" => $host,
                        "port" => $port,
                        "weight" => $weight
                    ],
                    "Configured memcached node."
                );
            }

            $nodes = new Setup\Metrics\Metric(
                Setup\Metrics\Metric::STABILITY_CONFIG,
                Setup\Metrics\Metric::TYPE_COLLECTION,
                $server_collection,
                "Collection of configured memcached nodes."
            );
            $storage->store("memcached_nodes", $nodes);
        }

        $component_activation = [];
        foreach (ilGlobalCache::getAvailableComponents() as $component) {
            $component_activation[$component] = new Setup\Metrics\Metric(
                Setup\Metrics\Metric::STABILITY_CONFIG,
                Setup\Metrics\Metric::TYPE_BOOL,
                $settings->isComponentActivated($component)
            );
        }
        $component_activation = new Setup\Metrics\Metric(
            Setup\Metrics\Metric::STABILITY_CONFIG,
            Setup\Metrics\Metric::TYPE_COLLECTION,
            $component_activation,
            "Which components are activated to use caching?"
        );
        $storage->store(
            "component_activation",
            $component_activation
        );
    }
}
