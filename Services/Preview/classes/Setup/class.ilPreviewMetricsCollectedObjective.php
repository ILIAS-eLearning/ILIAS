<?php

use ILIAS\Setup;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilPreviewMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return \ilIniFilesLoadedObjective[]
     */
    protected function getTentativePreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage) : void
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        if (!$ini) {
            return;
        }

        $storage->storeConfigText(
            "path_to_ghostscript",
            $ini->readVariable("tools", "ghostscript"),
            "The path to the binary of ghostscripts that is used to render previews."
        );
    }
}
