<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Persistence;

/**
 * Class BasicTaskManager
 *
 * @package ILIAS\BackgroundTasks\Implementation
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * Basic Task manager. Will execute tasks immediately.
 *
 * Some important infos:
 *         - The bucket and its tasks are not saved into the db upon execution
 *         - The percentage and current task are not updated during execution.
 *         - The bucket and its tasks inkl. percentage and current task are only saved into the DB
 *         when a user interaction occurs.
 *
 */
class SyncTaskManager extends BasicTaskManager
{

    /**
     * @var Persistence
     */
    protected $persistence;


    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }


    /**
     * This will add an Observer of the Task and start running the task.
     *
     * @param Bucket $bucket
     *
     * @return mixed|void
     * @throws \Exception
     *
     */
    public function run(Bucket $bucket)
    {
        $task = $bucket->getTask();
        $bucket->setCurrentTask($task);
        $observer = new NonPersistingObserver($bucket);

        try {
            $this->executeTask($task, $observer);
            $bucket->setState(State::FINISHED);
        } catch (UserInteractionRequiredException $e) {
            // We're okay!
            $this->persistence->saveBucketAndItsTasks($bucket);
        }
    }
}