<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilVirusScannerMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        if (!$ini) {
            return;
        }

        $storage->storeConfigText(
            "virusscanner",
            $ini->readVariable("tools", "vscantype"),
            "The engine that is used for virus scanning."
        );
        $storage->storeConfigText(
            "path_to_scan",
            $ini->readVariable("tools", "scancommand"),
            "The path to the binary that is used for virus scanning."
        );
        $storage->storeConfigText(
            "path_to_clean",
            $ini->readVariable("tools", "cleancommand"),
            "The path to the binary that is used for cleaning up after virus scanning."
        );
    }
}
