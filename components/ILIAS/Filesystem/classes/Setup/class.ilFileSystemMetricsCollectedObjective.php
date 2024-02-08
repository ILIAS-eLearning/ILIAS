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

use ILIAS\Setup;
use ILIAS\Setup\Metrics\Storage;

class ilFileSystemMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return \ilIniFilesLoadedObjective[]
     */
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Storage $storage): void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        if ($ini) {
            $storage->storeConfigText(
                "data_dir",
                $ini->readVariable("clients", "datadir"),
                "Filesystem location where ILIAS stores data outside of direct web access."
            );
        }
    }
}
