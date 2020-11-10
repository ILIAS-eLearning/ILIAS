<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilLoggingMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
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

        $storage->storeConfigBool(
            "enable",
            $ini->readVariable("log", "enabled"),
            "Is the logging enabled on the installation?"
        );
        $storage->storeConfigText(
            "path_to_logfile",
            $ini->readVariable("log", "path") . "/" . $ini->readVariable("log", "file"),
            "The path to the logfile."
        );
        $storage->storeConfigText(
            "errorlog_dir",
            $ini->readVariable("log", "error_path"),
            "The path to the directory where error protocols are stored."
        );
    }
}
