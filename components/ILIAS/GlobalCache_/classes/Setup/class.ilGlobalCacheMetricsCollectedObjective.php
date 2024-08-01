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

use ILIAS\Setup\Metrics\CollectedObjective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Cache\Config;
use ILIAS\Setup\Metrics\Metric;
use ILIAS\Setup\Metrics\MetricStability;
use ILIAS\Setup\Metrics\MetricType;
use ILIAS\Setup;

class ilGlobalCacheMetricsCollectedObjective extends CollectedObjective
{
    protected function getTentativePreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new \ilDatabaseInitializedObjective()
        ];
    }

    protected function collectFrom(Environment $environment, Storage $storage): void
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $client_ini = $environment->getResource(Environment::RESOURCE_CLIENT_INI);

        if (!$client_ini) {
            return;
        }

        $config = (new ilGlobalCacheSettingsAdapter($client_ini, $db))->getConfig();

        $service = $config->getAdaptorName();
        $storage->storeConfigText(
            "service",
            fn() => $service,
            "The backend that is used for the ILIAS cache."
        );
        $storage->storeConfigBool(
            "active",
            fn() => $config->isActivated()
        );

        $servers = $config->getNodes();
        if (
            $service === Config::MEMCACHED &&
            $servers !== []
        ) {
            $server_collection = [];
            foreach ($servers as $server) {
                $host = new Metric(
                    MetricStability::CONFIG,
                    MetricType::TEXT,
                    fn() => $server->getHost()
                );
                $port = new Metric(
                    MetricStability::CONFIG,
                    MetricType::GAUGE,
                    fn() => $server->getPort()
                );
                $weight = new Metric(
                    MetricStability::CONFIG,
                    MetricType::GAUGE,
                    fn() => $server->getWeight()
                );

                $server_collection[] = new Metric(
                    MetricStability::CONFIG,
                    MetricType::COLLECTION,
                    fn() => [
                        "host" => $host,
                        "port" => $port,
                        "weight" => $weight
                    ],
                    "Configured memcached node."
                );
            }

            $nodes = new Metric(
                MetricStability::CONFIG,
                MetricType::COLLECTION,
                fn() => $server_collection,
                "Collection of configured memcached nodes."
            );
            $storage->store("memcached_nodes", $nodes);
        }

        $component_activation = [];
        foreach (ilGlobalCache::getAvailableComponents() as $component) {
            $component_activation[$component] = new Metric(
                MetricStability::CONFIG,
                MetricType::BOOL,
                fn() => $config->isComponentActivated($component)
            );
        }
        $component_activation = new Metric(
            MetricStability::CONFIG,
            MetricType::COLLECTION,
            fn() => $component_activation,
            "Which components are activated to use caching?"
        );
        $storage->store(
            "components",
            $component_activation
        );
    }
}
