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

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionSkippedException;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\BackgroundTasks\Value;

/**
 * Class BasicTaskManager
 * @package ILIAS\BackgroundTasks\Implementation
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * Basic Task manager. Will execute tasks immediately.
 * Some important infos:
 *         - The bucket and its tasks are not saved into the db upon execution
 *         - The percentage and current task are not updated during execution.
 *         - The bucket and its tasks inkl. percentage and current task are only saved into the DB
 *         when a user interaction occurs.
 */
abstract class BasicTaskManager implements TaskManager
{
    protected Persistence $persistence;

    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @throws UserInteractionSkippedException|UserInteractionRequiredException|Exception
     */
    public function executeTask(Task $task, Observer $observer): Value
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
            /** @var Task\UserInteraction $user_interaction */
            $user_interaction = $task;

            if ($user_interaction->canBeSkipped($final_values)) {
                if ($task->isFinal()) {
                    throw new UserInteractionSkippedException('Final interaction skipped');
                }
                return $task->getSkippedValue($task->getInput());
            }

            $observer->notifyCurrentTask($user_interaction);
            $observer->notifyState(State::USER_INTERACTION);
            throw new UserInteractionRequiredException("User interaction required.");
        }

        throw new Exception("You need to execute a Job or a UserInteraction.");
    }

    /**
     * Continue a task with a given option.
     */
    public function continueTask(Bucket $bucket, Option $option): void
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
    public function quitBucket(Bucket $bucket): void
    {
        $this->persistence->deleteBucket($bucket);
    }
}
