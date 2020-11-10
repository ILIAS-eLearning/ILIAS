<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilBackgroundTasksMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
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
            "type",
            $ini->readVariable("background_tasks", "concurrency"),
            "The type of execution used for background tasks"
        );
        $storage->storeConfigGauge(
            "max_number_of_concurrent_tasks",
            (int)$ini->readVariable("background_tasks", "number_of_concurrent_tasks"),
            "The maximum amount of concurrent tasks used to run background tasks."
        );
    }
}
