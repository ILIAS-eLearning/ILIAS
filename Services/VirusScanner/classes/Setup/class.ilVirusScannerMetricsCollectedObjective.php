<?php

declare(strict_types=1);

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

class ilVirusScannerMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return ilIniFilesLoadedObjective[]
     */
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
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
