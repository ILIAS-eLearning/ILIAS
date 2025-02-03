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

declare(strict_types=1);

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
