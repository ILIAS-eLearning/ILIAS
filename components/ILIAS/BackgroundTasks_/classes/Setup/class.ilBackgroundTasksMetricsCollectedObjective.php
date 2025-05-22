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

class ilBackgroundTasksMetricsCollectedObjective extends CollectedObjective
{
    /**
     * @return \ilIniFilesLoadedObjective[]
     */
    public function getTentativePreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function collectFrom(Environment $environment, Storage $storage): void
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
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
            (int) $ini->readVariable("background_tasks", "number_of_concurrent_tasks"),
            "The maximum amount of concurrent tasks used to run background tasks."
        );
    }
}
