<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilSetupMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getLabel() : string
    {
        return "Collect common metrics for the ILIAS installation.";
    }

    public function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $storage->storeStableBool(
            "is_installed",
            $ini !== null && $client_ini !== null,
            "Are there any indications an installation was performed?"
        );
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        if ($client_id) {
            $storage->storeConfigText(
                "client_id",
                $client_id,
                "Id of the ILIAS client."
            );
        }
    }
}
