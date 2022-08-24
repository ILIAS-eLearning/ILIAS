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

class ilBackgroundTasksConfigStoredObjective implements Setup\Objective
{
    protected ilBackgroundTasksSetupConfig $config;

    public function __construct(
        \ilBackgroundTasksSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Fill ini with settings for Services/BackgroundTasks";
    }

    public function isNotable(): bool
    {
        return false;
    }

    /**
     * @return \ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        if (!$ini->groupExists("background_tasks")) {
            $ini->addGroup("background_tasks");
        }

        $ini->setVariable("background_tasks", "concurrency", $this->config->getType());
        $ini->setVariable("background_tasks", "number_of_concurrent_tasks", $this->config->getMaxCurrentTasks());

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return
            !$ini->groupExists("background_tasks") ||
            $ini->readVariable("background_tasks", "concurrency") !== $this->config->getType() ||
            $ini->readVariable("background_tasks", "number_of_concurrent_tasks") !== $this->config->getMaxCurrentTasks()
        ;
    }
}
