<?php

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Value;

class BasicBucket implements Bucket
{

    /**
     * @var int
     */
    protected $userId;
    /**
     * @var Task
     */
    protected $rootTask;
    /**
     * @var Task
     */
    protected $currentTask;
    /**
     * @var Task[]
     */
    protected $tasks;
    /**
     * @var int
     */
    protected $state;
    /**
     * @var int
     */
    protected $totalNumberOfTasks;
    /**
     * @var int[]
     */
    protected $percentages = [];
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var string
     */
    protected $description = "";
    /**
     * @var int
     */
    protected $percentage = 0;
    /**
     * @var int
     */
    protected $lastHeartbeat = 0;


    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }


    /**
     * @param int $user_id
     *
     * @return $this BasicObserver
     */
    public function setUserId($user_id)
    {
        $this->userId = $user_id;

        return $this;
    }


    /**
     * Used by a job to notify his percentage.
     *
     * @param $task       Task
     * @param $percentage int
     */
    public function setPercentage(Task $task, $percentage)
    {
        $this->percentages[spl_object_hash($task)] = $percentage;
        $this->calculateOverallPercentage();
    }


    public function setOverallPercentage($percentage)
    {
        $this->percentage = $percentage;
    }


    /**
     * @param Task $task
     *
     * @return mixed
     */
    public function setCurrentTask($task)
    {
        $this->currentTask = $task;
    }


    /**
     * @param Task $task
     *
     * @return void
     */
    public function setTask(Task $task)
    {
        $this->tasks = $task->unfoldTask();
        $this->totalNumberOfTasks = count($this->tasks);
        $this->rootTask = $task;
        foreach ($this->tasks as $subTask) {
            $this->percentages[spl_object_hash($subTask)] = 0;
        }
    }


    /**
     * Calculates the percentage up to the last task.
     *
     * @return int
     */
    public function calculateOverallPercentage()
    {
        $countable_tasks = 0;
        /**
         * @var $task Task\UserInteraction\
         */
        foreach ($this->tasks as $task) {
            switch (true) {
                case ($task instanceof Task\Job):
                    $countable_tasks++;
                    break;
            }
        }

        $this->percentage = array_sum($this->percentages) / $countable_tasks;
    }


    public function getOverallPercentage()
    {
        return $this->percentage;
    }


    /**
     * @param int $state From ILIAS\BackgroundTasks\Implementation\Observer\State
     */
    public function setState($state)
    {
        $this->state = $state;
    }


    /**
     * @return Task
     */
    public function getCurrentTask()
    {
        return $this->currentTask;
    }


    /**
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->rootTask;
    }


    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function checkIntegrity()
    {
        if (!$this->getUserId()) {
            foreach ($this->getTask()->unfoldTask() as $task) {
                if ($task instanceof Task\UserInteraction) {
                    throw new Exception("Your task contains user interactions and thus needs a user that observes the task.");
                }
            }
        }

        return true;
    }


    /**
     * Continue a task with a given option.
     *
     * @param Option $option
     *
     * @return mixed
     * @throws Exception
     */
    public function userInteraction(Option $option)
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

        if ($currentTask === $this->rootTask) {
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
     *
     * @param Task  $currentTask
     * @param Value $resulting_value
     */
    protected function replaceThunkValue(Task $currentTask, Value $resulting_value)
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


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * There was something going on in the bucket, it's still working.
     *
     * @return void
     */
    public function heartbeat()
    {
        $timezone_identifier = ini_get('date.timezone');
        date_default_timezone_set($timezone_identifier ? $timezone_identifier : 'UTC');
        $now = new \DateTime();
        $this->lastHeartbeat = $now->getTimestamp();
    }


    /**
     * @param $timestamp int
     *
     * @return void
     */
    public function setLastHeartbeat($timestamp)
    {
        $this->lastHeartbeat = $timestamp;
    }


    /**
     * When was the last time that something happened on this bucket?
     *
     * @return int Timestamp.
     */
    public function getLastHeartbeat()
    {
        return $this->lastHeartbeat;
    }
}