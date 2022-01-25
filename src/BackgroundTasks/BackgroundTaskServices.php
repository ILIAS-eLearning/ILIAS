<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\DI\Container;
use ILIAS\BackgroundTasks\Dependencies\Injector;

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
/**
 * Class BackgroundTaskServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BackgroundTaskServices
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function taskFactory() : TaskFactory
    {
        return $this->container['bt.task_factory'];
    }

    public function persistence() : Persistence
    {
        return $this->container['bt.persistence'];
    }

    public function taskManager() : TaskManager
    {
        return $this->container['bt.task_manager'];
    }

    public function injector() : Injector
    {
        return $this->container['bt.injector'];
    }
}
