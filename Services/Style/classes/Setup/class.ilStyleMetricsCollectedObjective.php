<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilStyleMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        if (!$ini) {
            return;
        }

        $storage->storeConfigBool(
            "manage_system_styles",
            $ini->readVariable("tools", "enable_system_styles_management"),
            "Can users manage system styles from within the installation?"
        );
        $storage->storeConfigText(
            "path_to_lessc",
            $ini->readVariable("tools", "lessc"),
            "The path to the binary that is used for compiling less."
        );
    }
}
