<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilBackgroundTasksConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilBackgroundTasksSetupConfig
     */
    protected $config;

    public function __construct(
        \ilBackgroundTasksSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with settings for Services/BackgroundTasks";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
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
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return
            !$ini->groupExists("background_tasks") ||
            $ini->readVariable("background_tasks", "concurrency") !== $this->config->getType() ||
            $ini->readVariable("background_tasks", "number_of_concurrent_tasks") !== $this->config->getMaxCurrentTasks()
        ;
    }
}
