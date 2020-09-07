<?php

namespace ILIAS\DI;

use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;

/**
 */
class BackgroundTaskServices
{

    /**
     * @var    Container
     */
    protected $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @return TaskFactory
     */
    public function taskFactory()
    {
        return $this->container['bt.task_factory'];
    }


    /**
     * @return Persistence
     */
    public function persistence()
    {
        return $this->container['bt.persistence'];
    }


    /**
     * @return TaskManager
     */
    public function taskManager()
    {
        return $this->container['bt.task_manager'];
    }


    /**
     * @return \ILIAS\BackgroundTasks\Dependencies\Injector
     */
    public function injector()
    {
        return $this->container['bt.injector'];
    }
}
