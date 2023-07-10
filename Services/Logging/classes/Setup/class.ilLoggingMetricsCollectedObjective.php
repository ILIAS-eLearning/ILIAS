<?php

declare(strict_types=1);

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Metrics\CollectedObjective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Metrics\Storage;

class ilLoggingMetricsCollectedObjective extends CollectedObjective
{
    protected function getTentativePreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    protected function collectFrom(Environment $environment, Storage $storage): void
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        if (!$ini) {
            return;
        }

        $storage->storeConfigBool(
            "enable",
            (bool) $ini->readVariable("log", "enabled"),
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
