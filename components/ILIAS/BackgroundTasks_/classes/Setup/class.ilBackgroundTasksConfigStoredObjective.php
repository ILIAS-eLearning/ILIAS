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

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;

class ilBackgroundTasksConfigStoredObjective implements Objective
{
    public function __construct(protected ilBackgroundTasksSetupConfig $config)
    {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Fill ini with settings for components/ILIAS/BackgroundTasks_";
    }

    public function isNotable(): bool
    {
        return false;
    }

    /**
     * @return \ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);

        if (!$ini->groupExists("background_tasks")) {
            $ini->addGroup("background_tasks");
        }

        if ($this->config->getType() === \ilBackgroundTasksSetupConfig::TYPE_ASYNCHRONOUS) {
            $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
            /** @var Setup\CLI\IOWrapper $io */
            $io->inform('Asynchronous background tasks need SOAP administration to be enabled. Make sure to enable it in your configuarion.');
        }

        $ini->setVariable("background_tasks", "concurrency", $this->config->getType());
        $ini->setVariable("background_tasks", "number_of_concurrent_tasks", $this->config->getMaxCurrentTasks());

        if (!$ini->write()) {
            throw new UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        if (!$ini->groupExists("background_tasks")) {
            return true;
        }
        if ($ini->readVariable("background_tasks", "concurrency") !== $this->config->getType()) {
            return true;
        }
        return $ini->readVariable("background_tasks", "number_of_concurrent_tasks") !== $this->config->getMaxCurrentTasks();
    }
}
