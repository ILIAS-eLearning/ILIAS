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

class ilUtilitiesMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
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

        $storage->storeConfigText(
            "path_to_convert",
            $ini->readVariable("tools", "convert"),
            "The path to the binary from imagemagick that is used to convert images."
        );
        $storage->storeConfigText(
            "path_to_zip",
            $ini->readVariable("tools", "zip"),
            "The path to the binary that is used for zipping files."
        );
        $storage->storeConfigText(
            "path_to_unzip",
            $ini->readVariable("tools", "unzip"),
            "The path to the binary that is used for unzipping files."
        );
    }
}
