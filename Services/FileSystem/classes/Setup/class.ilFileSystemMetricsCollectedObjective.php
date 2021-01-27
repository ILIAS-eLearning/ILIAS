<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\Metrics\Storage;

class ilFileSystemMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    public function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Storage $storage) : void
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
