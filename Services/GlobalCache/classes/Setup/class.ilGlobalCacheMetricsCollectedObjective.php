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

use ILIAS\Setup;
use ILIAS\DI;

class ilGlobalCacheMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        if (!$client_ini || !$db) {
            return;
        }

        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
        $GLOBALS["DIC"]["ilDB"] = $db;

        $settings = new ilGlobalCacheSettings();
        $settings->readFromIniFile($client_ini);

        $service = ilGlobalCache::lookupServiceClassName($settings->getService());
        $storage->storeConfigText(
            "service",
            $service,
            "The backend that is used for the ILIAS cache."
        );
        $storage->storeConfigText(
            "active",
            $settings->isActive() ? 'yes' : 'no',
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
                    Setup\Metrics\Metric::TYPE_GAUGE,
                    $server->getPort()
                );
                $weight = new Setup\Metrics\Metric(
                    Setup\Metrics\Metric::STABILITY_CONFIG,
                    Setup\Metrics\Metric::TYPE_GAUGE,
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
            "components",
            $component_activation
        );

        $GLOBALS["DIC"] = $DIC;
    }
}
