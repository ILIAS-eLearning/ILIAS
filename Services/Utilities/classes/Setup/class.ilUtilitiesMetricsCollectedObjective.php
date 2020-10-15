<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilUtilitiesMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
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
