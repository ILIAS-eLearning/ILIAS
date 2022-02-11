<?php

use ILIAS\Setup;

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
class ilVirusScannerMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return \ilIniFilesLoadedObjective[]
     */
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
