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

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Value;

class BasicBucket implements Bucket
{
    protected int $user_id;
    protected Task $root_task;
    protected Task $current_task;
    /**
     * @var Task[]
     */
    protected array $tasks = [];
    protected int $state;
    protected int $total_number_of_tasks;
    protected array $percentages = [];
    protected string $title = "";
    protected string $description = "";
    protected int $percentage = 0;
    protected int $last_heartbeat = 0;

    public function getUserId(): int
    {
        return $this->user_id ?? 0;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setPercentage(Task $task, int $percentage): void
    {
        $this->percentages[spl_object_hash($task)] = $percentage;
        $this->calculateOverallPercentage();
    }

    public function setOverallPercentage(int $percentage): void
    {
        $this->percentage = $percentage;
    }

    public function setCurrentTask(Task $task): void
    {
        $this->current_task = $task;
    }

    public function setTask(Task $task): void
    {
        $this->tasks = $task->unfoldTask();
        $this->total_number_of_tasks = count($this->tasks);
        $this->root_task = $task;
        foreach ($this->tasks as $subTask) {
            $this->percentages[spl_object_hash($subTask)] = 0;
        }
    }

    /**
     * Calculates the percentage up to the last task.
     */
    public function calculateOverallPercentage(): void
    {
        $countable_tasks = 0;
        /**
         * @var $task Task\UserInteraction\
         */
        foreach ($this->tasks as $task) {
            if ($task instanceof Task\Job) {
                $countable_tasks++;
            }
        }

        $this->percentage = array_sum($this->percentages) / $countable_tasks;
    }

    public function getOverallPercentage(): int
    {
        return $this->percentage;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getCurrentTask(): Task
    {
        return $this->current_task;
    }

    public function hasCurrentTask(): bool
    {
        return isset($this->current_task);
    }

    public function getTask(): Task
    {
        return $this->root_task;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function checkIntegrity(): bool
    {
        if ($this->getUserId() === 0) {
            foreach ($this->getTask()->unfoldTask() as $task) {
                if ($task instanceof Task\UserInteraction) {
                    throw new Exception("Your task contains user interactions and thus needs a user that observes the task.");
                }
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function userInteraction(Option $option): void
    {
        $currentTask = $this->getCurrentTask();

        if ($this->getState() != State::USER_INTERACTION) {
            throw new Exception("Cannot continue a task that is not in the state 'user interaction'");
        }
        if (!$currentTask instanceof Task\UserInteraction) {
            // TODO: Maybe cleanup task?
            throw new Exception("Observer is in an invalid state! state: userInteraction but current task is not a user interaction!");
        }

        // From the current task we do the interaction.
        $inputs = $currentTask->getInput();
        $resulting_value = $currentTask->interaction($inputs, $option, $this);

        if ($currentTask === $this->root_task) {
            // If this user interaction was the last thing to do, we set the state to finished. We can throw away the resulting value.
            $this->setState(State::FINISHED);
        } else {
            // Then we replace the thunk value with the resulting value.
            $this->replaceThunkValue($currentTask, $resulting_value);
        }
    }

    /**
     * In the structure of the task of this bucket the result of $currentTask is replaced with the
     * $resulting_value
     */
    protected function replaceThunkValue(Task $currentTask, Value $resulting_value): void
    {
        $tasks = $this->getTask()->unfoldTask();

        foreach ($tasks as $task) {
            $newInputs = [];
            foreach ($task->getInput() as $input) {
                if ($input instanceof ThunkValue && $input->getParentTask() === $currentTask) {
                    $newInputs[] = $resulting_value;
                } else {
                    $newInputs[] = $input;
                }
            }
            $task->setInput($newInputs);
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * There was something going on in the bucket, it's still working.
     */
    public function heartbeat(): void
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->last_heartbeat = $now->getTimestamp();
    }

    public function setLastHeartbeat(int $timestamp): void
    {
        $this->last_heartbeat = $timestamp;
    }

    public function getLastHeartbeat(): int
    {
        return $this->last_heartbeat;
    }
}
