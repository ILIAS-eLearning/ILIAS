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

        $virus_scanner = $ini->readVariable("tools", "vscantype");
        $storage->storeConfigText(
            "virusscanner",
            $virus_scanner,
            "The engine that is used for virus scanning."
        );

        if ($virus_scanner === ilVirusScannerSetupConfig::VIRUS_SCANNER_ICAP) {
            $storage->storeConfigText(
                "icap_client_path",
                $ini->readVariable("tools", "icap_client_path"),
                "The configured ICAP client path."
            );
            $storage->storeConfigText(
                "icap_host",
                $ini->readVariable("tools", "icap_host"),
                "The configured ICAP host."
            );
            $storage->storeConfigText(
                "icap_port",
                $ini->readVariable("tools", "icap_port"),
                "The configured ICAP port."
            );
            $storage->storeConfigText(
                "icap_service_name",
                $ini->readVariable("tools", "icap_service_name"),
                "The configured ICAP service name."
            );
        } elseif (is_string($virus_scanner) &&
            $virus_scanner !== '' &&
            $virus_scanner !== ilVirusScannerSetupConfig::VIRUS_SCANNER_NONE) {
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
}
