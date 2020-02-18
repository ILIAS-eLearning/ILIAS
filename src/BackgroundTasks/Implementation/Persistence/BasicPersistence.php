<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Exceptions\BucketNotFoundException;
use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucketMeta;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

class BasicPersistence implements Persistence
{

    /**
     * @var BasicPersistence
     */
    protected static $instance;
    /**
     * @var Bucket[]
     */
    protected static $buckets = [];
    /**
     * @var int[]
     */
    protected $bucketHashToObserverContainerId = [];
    /**
     * @var int[]
     */
    protected $taskHashToTaskContainerId = [];
    /**
     * @var int[]
     */
    protected $valueHashToValueContainerId = [];
    /**
     * @var Task[]
     */
    protected static $tasks = [];
    /**
     * @var \arConnector
     */
    protected $connector = null;


    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new BasicPersistence();
        }

        return self::$instance;
    }


    /**
     * Currently for testing only.
     *
     * @param $connector \arConnector
     */
    public function setConnector(\arConnector $connector)
    {
        $this->connector = $connector;
    }


    /**
     * Fully updates or creates an Observer and all its tasks into the database.
     *
     * @param Bucket $bucket The bucket you want to save.
     */
    public function saveBucketAndItsTasks(Bucket $bucket)
    {
        $bucket->checkIntegrity();

        $this->saveObserver($bucket);
    }


    /**
     * Updates only the bucket! Use this if e.g. the percentage or the current task changes.
     *
     * @param Bucket $bucket
     */
    public function updateBucket(Bucket $bucket)
    {
        $bucketContainer = new BucketContainer($this->getBucketContainerId($bucket), $this->connector);

        // The basic information about the task.
        $bucketContainer->setUserId($bucket->getUserId());
        $bucketContainer->setState($bucket->getState());
        $bucketContainer->setTotalNumberoftasks(count($bucket->getTask()->unfoldTask()));
        $bucketContainer->setPercentage($bucket->getOverallPercentage());
        $bucketContainer->setTitle($bucket->getTitle());
        $bucketContainer->setLastHeartbeat($bucket->getLastHeartbeat());
        $bucketContainer->setDescription($bucket->getDescription());
        $bucketContainer->setCurrentTaskid($this->getTaskContainerId($bucket->getCurrentTask()));
        $bucketContainer->setRootTaskid($this->getTaskContainerId($bucket->getTask()));

        // Save and store the container to bucket instance.
        $bucketContainer->update();
    }


    /**
     * @inheritdoc
     */
    public function getBucketIdsOfUser($user_id, $order_by = "id", $order_direction = "ASC")
    {
        return BucketContainer::where(['user_id' => $user_id])
            ->orderBy($order_by, $order_direction)
            ->getArray(null, 'id');
    }


    /**
     * @param int $user_id
     *
     * @return BucketMeta[]
     */
    public function getBucketMetaOfUser($user_id)
    {
        $buckets = BucketContainer::where(['user_id' => $user_id])->get();
        $bucketMetas = array_map(function (BucketContainer $bucketContainer) {
            $bucketMeta = new BasicBucketMeta();

            $bucketMeta->setUserId($bucketContainer->getUserId());
            $bucketMeta->setState($bucketContainer->getState());
            $bucketMeta->setTitle($bucketContainer->getTitle());
            $bucketMeta->setDescription($bucketContainer->getDescription());
            $bucketMeta->setOverallPercentage($bucketContainer->getPercentage());

            return $bucketMeta;
        }, $buckets);

        return $bucketMetas;
    }


    /**
     * @inheritdoc
     */
    public function getBucketIdsByState($state)
    {
        $buckets = BucketContainer::where(['state' => $state])->get();
        $ids = array_map(function (BucketContainer $bucket_container) {
            return $bucket_container->getId();
        }, $buckets);

        return $ids;
    }


    /**
     * @param Bucket $bucket The bucket we want to save.
     *
     * This will recursivly save the Observer.
     *
     */
    protected function saveObserver(Bucket $bucket)
    {
        // If the instance has a known container we use it, otherwise we create a new container.
        if (isset($this->bucketHashToObserverContainerId[spl_object_hash($bucket)])) {
            $bucketContainer = new BucketContainer($this->bucketHashToObserverContainerId[spl_object_hash($bucket)], $this->connector);
        } else {
            $bucketContainer = new BucketContainer(0, $this->connector);
        }

        // The basic information about the task.
        $bucketContainer->setUserId($bucket->getUserId());
        $bucketContainer->setState($bucket->getState());
        $bucketContainer->setTitle($bucket->getTitle());
        $bucketContainer->setDescription($bucket->getDescription());
        $bucketContainer->setTotalNumberoftasks(count($bucket->getTask()->unfoldTask()));
        $bucketContainer->setPercentage($bucket->getOverallPercentage());

        // We want to store the bucket ID in every sub task and value. Thus we need to create an id if not available yet.
        if (!$bucketContainer->getId()) {
            $bucketContainer->create();
        }

        // The recursive part.
        $this->saveTask($bucket->getTask(), $bucketContainer->getId());
        if (!$bucket->getCurrentTask()) {
            $bucket->setCurrentTask($bucket->getTask());
        }
        $bucketContainer->setCurrentTaskid($this->getTaskContainerId($bucket->getCurrentTask()));
        $bucketContainer->setRootTaskid($this->getTaskContainerId($bucket->getTask()));

        // Save and store the container to bucket instance.
        $bucketContainer->save();
        $this->bucketHashToObserverContainerId[spl_object_hash($bucket)] = $bucketContainer->getId();
    }


    /**
     * @param Task $task     The task to save.
     * @param int  $bucketId The bucket id is needed as we want some control over what task belongs
     *                       to what batch.
     *
     * This will recursivly save a task.
     */
    protected function saveTask(Task $task, $bucketId)
    {
        // If the instance has a known container we use it, otherwise we create a new container.
        if (isset($this->taskHashToTaskContainerId[spl_object_hash($task)])) {
            $taskContainer = new TaskContainer($this->taskHashToTaskContainerId[spl_object_hash($task)], $this->connector);
        } else {
            $taskContainer = new TaskContainer(0, $this->connector);
        }

        // The basic information about the task.
        $taskContainer->setType($task->getType());
        $taskContainer->setBucketId($bucketId);
        $reflection = new \ReflectionClass(get_class($task));
        $taskContainer->setClassName(get_class($task));
        // bugfix mantis 23503
        $absolute_class_path = $reflection->getFileName();
        // $relative_class_path = str_replace(ILIAS_ABSOLUTE_PATH,".",$absolute_class_path);
        $taskContainer->setClassPath($reflection->getFileName());

        // Recursivly save the inputs and link them to this task.
        foreach ($task->getInput() as $input) {
            $this->saveValue($input, $bucketId);
        }
        $this->saveValueToTask($task, $taskContainer, $bucketId);

        // Save and store the container to the task instance.
        $taskContainer->save();
        $this->taskHashToTaskContainerId[spl_object_hash($task)] = $taskContainer->getId();
    }


    /**
     * Save all input parameters to a task.
     *
     * @param Task          $task          The task containing the inputs
     * @param TaskContainer $taskContainer The container of the task. This is needed to link the
     *                                     ids and delete old links.
     * @param int           $bucketId
     */
    protected function saveValueToTask(Task $task, TaskContainer $taskContainer, $bucketId)
    {
        // If we have previous values to task associations we delete them.
        if ($taskContainer->getId()) {
            /** @var ValueToTaskContainer[] $olds */
            $olds = ValueToTaskContainer::where(['task_id' => $taskContainer->getId()])->get();
            foreach ($olds as $old) {
                $old->delete();
            }
        } else {
            // We need a valid ID to link the inputs
            $taskContainer->save();
        }

        // We create the new 1 to n relation.
        foreach ($task->getInput() as $inputValue) {
            $v = new ValueToTaskContainer(0, $this->connector);
            $v->setTaskId($taskContainer->getId());
            $v->setBucketId($bucketId);
            $v->setValueId($this->getValueContainerId($inputValue));
            $v->save();
        }
    }


    /**
     * @param Value $value    The value
     * @param int   $bucketId The bucket id, we need it to have an overview of all values belonging
     *                        to a batch.
     *
     * Stores the value recursively.
     */
    protected function saveValue(Value $value, $bucketId)
    {
        // If we have previous values to task associations we delete them.
        if (isset($this->valueHashToValueContainerId[spl_object_hash($value)])) {
            $valueContainer = new ValueContainer($this->valueHashToValueContainerId[spl_object_hash($value)], $this->connector);
        } else {
            $valueContainer = new ValueContainer(0, $this->connector);
        }

        // Save information about the value
        $reflection = new \ReflectionClass(get_class($value));
        $valueContainer->setClassName(get_class($value));
        // bugfix mantis 23503
        // $absolute_class_path = $reflection->getFileName();
        // $relative_class_path = str_replace(ILIAS_ABSOLUTE_PATH,".",$absolute_class_path);
        // $valueContainer->setClassPath($relative_class_path);
        $valueContainer->setType($value->getType());
        $valueContainer->setHasParenttask($value->hasParentTask());
        $valueContainer->setBucketId($bucketId);
        $valueContainer->setHash($value->getHash());
        $valueContainer->setSerialized($value->serialize());

        // If the value is a thunk value we also store its parent.
        if ($value->hasParentTask()) {
            $this->saveTask($value->getParentTask(), $bucketId);
            $valueContainer->setParentTaskid($this->getTaskContainerId($value->getParentTask()));
        }

        // We save the container and store the instance to container association.
        $valueContainer->save();
        $this->valueHashToValueContainerId[spl_object_hash($value)] = $valueContainer->getId();
    }


    /**
     * @param Bucket $bucket
     *
     * @return int
     * @throws SerializationException
     */
    public function getBucketContainerId(Bucket $bucket)
    {
        if (!isset($this->bucketHashToObserverContainerId[spl_object_hash($bucket)])) {
            throw new SerializationException("Could not resolve container id of task: "
                . print_r($bucket, true));
        }

        return $this->bucketHashToObserverContainerId[spl_object_hash($bucket)];
    }


    /**
     * @param $task Task
     *
     * @return int
     * @throws SerializationException
     */
    protected function getTaskContainerId(Task $task)
    {
        if (!isset($this->taskHashToTaskContainerId[spl_object_hash($task)])) {
            throw new SerializationException("Could not resolve container id of task: "
                . print_r($task, true));
        }

        return $this->taskHashToTaskContainerId[spl_object_hash($task)];
    }


    /**
     * @param $value Value
     *
     * @return int
     * @throws SerializationException
     */
    protected function getValueContainerId($value)
    {
        if (!isset($this->valueHashToValueContainerId[spl_object_hash($value)])) {
            throw new SerializationException("Could not resolve container id of value: "
                . print_r($value, true));
        }

        return $this->valueHashToValueContainerId[spl_object_hash($value)];
    }


    /**
     * @param int $bucket_id
     *
     * @return \ILIAS\BackgroundTasks\Bucket
     * @throws \ILIAS\BackgroundTasks\Exceptions\BucketNotFoundException
     */
    public function loadBucket($bucket_id)
    {
        if (isset(self::$buckets[$bucket_id])) {
            return self::$buckets[$bucket_id];
        }
        /** @var BucketContainer $bucketContainer */
        $bucketContainer = BucketContainer::find($bucket_id);
        if (!$bucketContainer) {
            throw new BucketNotFoundException("The requested bucket with container id $bucket_id could not be found in the database.");
        }
        $bucket = new BasicBucket();

        $bucket->setUserId($bucketContainer->getUserId());
        $bucket->setState($bucketContainer->getState());
        $bucket->setTitle($bucketContainer->getTitle());
        $bucket->setDescription($bucketContainer->getDescription());
        $bucket->setOverallPercentage($bucketContainer->getPercentage());
        $bucket->setLastHeartbeat($bucketContainer->getLastHeartbeat());
        $bucket->setTask($this->loadTask($bucketContainer->getRootTaskid(), $bucket, $bucketContainer));

        $this->bucketHashToObserverContainerId[spl_object_hash($bucket)] = $bucket_id;

        return $bucket;
    }


    /**
     * Recursively loads a task.
     *
     * @param int             $taskContainerId The container ID to load.
     * @param Bucket          $bucket          Needed because we want to link the current task as
     *                                         soon as loaded.
     * @param BucketContainer $bucketContainer Needed because we need the current tasks container
     *                                         id for correct linking.
     *
     * @return Task
     */
    private function loadTask($taskContainerId, Bucket $bucket, BucketContainer $bucketContainer)
    {
        global $DIC;
        $factory = $DIC->backgroundTasks()->taskFactory();
        /** @var TaskContainer $taskContainer */
        $taskContainer = TaskContainer::find($taskContainerId);
        /** @noinspection PhpIncludeInspection */
        /** @var Task $task */
        $task = $factory->createTask($taskContainer->getClassName());

        // Bugfix 0023775
        // Added additional orderBy for the id to ensure that the items are returned in the right order.

        /** @var ValueToTaskContainer $valueToTask */
        $valueToTasks = ValueToTaskContainer::where(['task_id' => $taskContainerId])->orderBy('task_id')->orderBy('id')->get();
        $inputs = [];
        foreach ($valueToTasks as $valueToTask) {
            $inputs[] = $this->loadValue($valueToTask->getValueId(), $bucket, $bucketContainer);
        }
        $task->setInput($inputs);

        if ($taskContainerId == $bucketContainer->getCurrentTaskid()) {
            $bucket->setCurrentTask($task);
        }

        $this->taskHashToTaskContainerId[spl_object_hash($task)] = $taskContainerId;

        return $task;
    }


    private function loadValue($valueContainerId, Bucket $bucket, BucketContainer $bucketContainer)
    {
        global $DIC;
        $factory = $DIC->backgroundTasks()->injector();

        /** @var ValueContainer $valueContainer */
        $valueContainer = ValueContainer::find($valueContainerId);
        /** @noinspection PhpIncludeInspection */
        
        /** @var Value $value */
        $value = $factory->createInstance($valueContainer->getClassName());

        $value->unserialize($valueContainer->getSerialized());
        if ($valueContainer->getHasParenttask()) {
            $value->setParentTask($this->loadTask($valueContainer->getParentTaskid(), $bucket, $bucketContainer));
        }

        $this->valueHashToValueContainerId[spl_object_hash($value)] = $valueContainerId;

        return $value;
    }


    public function deleteBucketById($bucket_id)
    {
        /** @var BucketContainer $bucket */
        $buckets = BucketContainer::where(['id' => $bucket_id])->get();
        array_map(function (\ActiveRecord $item) {
            $item->delete();
        }, $buckets);

        /** @var TaskContainer $tasks */
        $tasks = TaskContainer::where(['bucket_id' => $bucket_id])->get();
        array_map(function (\ActiveRecord $item) {
            $item->delete();
        }, $tasks);

        /** @var ValueContainer $values */
        $values = ValueContainer::where(['bucket_id' => $bucket_id])->get();
        array_map(function (\ActiveRecord $item) {
            $item->delete();
        }, $values);

        /** @var ValueToTaskContainer $valueToTasks */
        $valueToTasks = ValueToTaskContainer::where(['bucket_id' => $bucket_id])->get();
        array_map(function (\ActiveRecord $item) {
            $item->delete();
        }, $valueToTasks);
    }


    /**
     * @inheritdoc
     */
    public function deleteBucket($bucket)
    {
        $id = $this->getBucketContainerId($bucket);
        $this->deleteBucketById($id);
        unset($this->bucketHashToObserverContainerId[spl_object_hash($bucket)]);
    }


    /**
     * @param int[] $bucket_container_id
     *
     * @return Bucket[]
     */
    public function loadBuckets($bucket_container_id)
    {
        $buckets = [];
        foreach ($bucket_container_id as $bucket_id) {
            $buckets[] = $this->loadBucket($bucket_id);
        }

        return $buckets;
    }
}
