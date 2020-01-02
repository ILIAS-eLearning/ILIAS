<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\BackgroundTasks\Value;

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
abstract class BasicTaskManager implements TaskManager
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
     * @param Task     $task
     * @param Observer $observer
     *
     * @return Value
     * @throws Exception
     */
    public function executeTask(Task $task, Observer $observer)
    {
        $observer->notifyState(State::RUNNING);
        /** @var Value[] $values */
        $values = $task->getInput();
        $final_values = [];
        $replace_thunk_values = false;
        foreach ($values as $value) {
            if (is_a($value, ThunkValue::class)) {
                $value = $this->executeTask($value->getParentTask(), $observer);
                $replace_thunk_values = true;
            }
            $final_values[] = $value;
        }

        if ($replace_thunk_values) {
            $task->setInput($final_values);
        }

        if (is_a($task, Task\Job::class)) {
            /** @var Task\Job $job */
            $job = $task;
            $observer->notifyCurrentTask($job);
            $value = $job->run($final_values, $observer);
            if (!$value->getType()->isExtensionOf($job->getOutputType())) {
                throw new Exception("The job " . $job->getType()
                    . " did state to output a value of type "
                    . $job->getOutputType() . " but outputted a value of type "
                    . $value->getType());
            }
            $observer->notifyPercentage($job, 100);

            return $value;
        }

        if (is_a($task, Task\UserInteraction::class)) {
            /** @var Task\UserInteraction $userInteraction */
            $userInteraction = $task;
            $observer->notifyCurrentTask($userInteraction);
            $observer->notifyState(State::USER_INTERACTION);
            throw new UserInteractionRequiredException("User interaction required.");
        }

        throw new Exception("You need to execute a Job or a UserInteraction.");
    }


    /**
     * Continue a task with a given option.
     *
     * @param Bucket $bucket
     * @param Option $option
     *
     * @return mixed
     */
    public function continueTask(Bucket $bucket, Option $option)
    {
        // We do the user interaction
        $bucket->userInteraction($option);
        if ($bucket->getState() != State::FINISHED) { // The job is not done after the user interaction, so we continue to run it.
            $this->run($bucket);
        } else {
            $this->persistence->deleteBucket($bucket);
        }
    }


    /**
     * @inheritdoc
     */
    public function quitBucket(Bucket $bucket)
    {
        $this->persistence->deleteBucket($bucket);
    }
}
