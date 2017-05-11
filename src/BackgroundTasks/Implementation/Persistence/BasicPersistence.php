<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Implementation\Observer\BasicObserver;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;
use ILIAS\DI\Injector;

class BasicPersistence implements Persistence {

	/**
	 * @var BasicPersistence
	 */
	protected static $instance;

	/**
	 * @var Observer[]
	 */
	protected static $observers = [];

	/**
	 * @var int[]
	 */
	protected $observerHashToObserverContainerId = [];

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

	protected function __construct() {
	}

	public static function instance() {
		if (!self::$instance)
			self::$instance = new BasicPersistence();
		return self::$instance;
	}


	/**
	 * Currently for testing only.
	 */
	function setConnector(\arConnector $connector) {
		$this->connector = $connector;
	}

	/**
	 * Fully updates or creates an Observer and all its tasks into the database.
	 *
	 * @param Observer $observer    The observer you want to save.
	 */
	public function saveObserverAndItsTasks(Observer $observer) {
		$observer->checkIntegrity();

		$this->saveObserver($observer);
	}


	/**
	 * Updates only the observer! Use this if e.g. the percentage or the current task changes.
	 *
	 * @param Observer $observer
	 */
	public function updateObserver(Observer $observer) {
		$observerContainer = new ObserverContainer($this->getObserverContainerId($observer), $this->connector);

		// The basic information about the task.
		$observerContainer->setUserId($observer->getUserId());
		$observerContainer->setState($observer->getState());
		$observerContainer->setTotalNumberoftasks(count($observer->getTask()->unfoldTask()));
		$observerContainer->setPercentage($observer->getPercentage());
		$observerContainer->setCurrentTaskid($this->getTaskContainerId($observer->getCurrentTask()));
		$observerContainer->setRootTaskid($this->getTaskContainerId($observer->getTask()));

		// Save and store the container to observer instance.
		$observerContainer->update();
	}


	/**
	 * @inheritdoc
	 */
	public function getObserverIdsOfUser(int $user_id) {
		$observers = ObserverContainer::where(['user_id' => $user_id])->get();
		$ids = array_map(function(ObserverContainer $observer_container) {
			return $observer_container->getId();
		}, $observers);

		return $ids;
	}


	/**
	 * @inheritdoc
	 */
	public function getObserverIdsByState($state) {
		$observers = ObserverContainer::where(['state' => $state])->get();
		$ids = array_map(function(ObserverContainer $observer_container) {
			return $observer_container->getId();
		}, $observers);

		return $ids;
	}

	/**
	 * @param Observer $observer    The observer we want to save.
	 *
	 * This will recursivly save the Observer.
	 *
	 */
	protected function saveObserver(Observer $observer) {
		// If the instance has a known container we use it, otherwise we create a new container.
		if (isset($this->observerHashToObserverContainerId[spl_object_hash($observer)]))
			$observerContainer = new ObserverContainer($this->observerHashToObserverContainerId[spl_object_hash($observer)], $this->connector);
		else
			$observerContainer = new ObserverContainer(0, $this->connector);

		// The basic information about the task.
		$observerContainer->setUserId($observer->getUserId());
		$observerContainer->setState($observer->getState());
		$observerContainer->setTotalNumberoftasks(count($observer->getTask()->unfoldTask()));
		$observerContainer->setPercentage($observer->getPercentage());

		// We want to store the observer ID in every sub task and value. Thus we need to create an id if not available yet.
		if(!$observerContainer->getId())
			$observerContainer->create();

		// The recursive part.
		$this->saveTask($observer->getTask(), $observerContainer->getId());
		$observerContainer->setCurrentTaskid($this->getTaskContainerId($observer->getCurrentTask()));
		$observerContainer->setRootTaskid($this->getTaskContainerId($observer->getTask()));

		// Save and store the container to observer instance.
		$observerContainer->save();
		$this->observerHashToObserverContainerId[spl_object_hash($observer)] = $observerContainer->getId();
	}


	/**
	 * @param Task $task        The task to save.
	 * @param int  $observerId  The observer id is needed as we want some control over what task belongs to what batch.
	 *
	 * This will recursivly save a task.
	 */
	protected function saveTask(Task $task, int $observerId) {
		// If the instance has a known container we use it, otherwise we create a new container.
		if(isset($this->taskHashToTaskContainerId[spl_object_hash($task)]))
			$taskContainer = new TaskContainer($this->taskHashToTaskContainerId[spl_object_hash($task)], $this->connector);
		else
			$taskContainer = new TaskContainer(0, $this->connector);

		// The basic information about the task.
		$taskContainer->setType($task->getType());
		$taskContainer->setObserverId($observerId);
		$reflection = new \ReflectionClass(get_class($task));
		$taskContainer->setClassName(get_class($task));
		$taskContainer->setClassPath($reflection->getFileName());

		// Recursivly save the inputs and link them to this task.
		foreach ($task->getInput() as $input) {
			$this->saveValue($input, $observerId);
		}
		$this->saveValueToTask($task, $taskContainer, $observerId);

		// Save and store the container to the task instance.
		$taskContainer->save();
		$this->taskHashToTaskContainerId[spl_object_hash($task)] = $taskContainer->getId();
	}


	/**
	 * Save all input parameters to a task.
	 *
	 * @param Task          $task           The task containing the inputs
	 * @param TaskContainer $taskContainer  The container of the task. This is needed to link the ids and delete old links.
	 * @param int           $observerId
	 */
	protected function saveValueToTask(Task $task, TaskContainer $taskContainer, int $observerId) {
		// If we have previous values to task associations we delete them.
		if($taskContainer->getId()) {
			/** @var ValueToTaskContainer[] $olds */
			$olds = ValueToTaskContainer::where(['taskId' => $taskContainer->getId()])->get();
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
			$v->setObserverId($observerId);
			$v->setValueId($this->getValueContainerId($inputValue));
			$v->save();
		}
	}


	/**
	 * @param Value $value          The value
	 * @param int   $observerId     The observer id, we need it to have an overview of all values belonging to a batch.
	 *
	 * Stores the value recursively.
	 */
	protected function saveValue(Value $value, int $observerId) {
		// If we have previous values to task associations we delete them.
		if(isset($this->valueHashToValueContainerId[spl_object_hash($value)]))
			$valueContainer = new ValueContainer($this->valueHashToValueContainerId[spl_object_hash($value)], $this->connector);
		else
			$valueContainer = new ValueContainer(0, $this->connector);

		// Save information about the value
		$reflection = new \ReflectionClass(get_class($value));
		$valueContainer->setClassName(get_class($value));
		$valueContainer->setClassPath($reflection->getFileName());
		$valueContainer->setType($value->getType());
		$valueContainer->setHasParenttask($value->hasParentTask());
		$valueContainer->setObserverId($observerId);
		$valueContainer->setHash($value->getHash());
		$valueContainer->setSerialized($value->serialize());

		// If the value is a thunk value we also store its parent.
		if($value->hasParentTask()) {
			$this->saveTask($value->getParentTask(), $observerId);
			$valueContainer->setParentTaskid($this->getTaskContainerId($value->getParentTask()));
		}

		// We save the container and store the instance to container association.
		$valueContainer->save();
		$this->valueHashToValueContainerId[spl_object_hash($value)] = $valueContainer->getId();
	}


	/**
	 * @param Observer $observer
	 *
	 * @return int
	 * @throws SerializationException
	 */
	protected function getObserverContainerId(Observer $observer) {
		if(! isset($this->observerHashToObserverContainerId[spl_object_hash($observer)] ))
			throw new SerializationException("Could not resolve container id of task: " . print_r($observer, true));
		return $this->observerHashToObserverContainerId[spl_object_hash($observer)];
	}

	/**
	 * @param $task Task
	 * @return int
	 * @throws SerializationException
	 */
	protected function getTaskContainerId(Task $task) {
		if(! isset($this->taskHashToTaskContainerId[spl_object_hash($task)] ))
			throw new SerializationException("Could not resolve container id of task: " . print_r($task, true));
		return $this->taskHashToTaskContainerId[spl_object_hash($task)];
	}

	/**
	 * @param $value Value
	 * @return int
	 * @throws SerializationException
	 */
	protected function getValueContainerId($value) {
		if(! isset($this->valueHashToValueContainerId[spl_object_hash($value)] ))
			throw new SerializationException("Could not resolve container id of value: " . print_r($value, true));
		return $this->valueHashToValueContainerId[spl_object_hash($value)];
	}


	/**
	 * @param int $observer_id
	 *
	 * @return Observer
	 */
	public function loadObserver(int $observer_id) {
		if(isset(self::$observers[$observer_id]))
			return self::$observers[$observer_id];
		/** @var ObserverContainer $observerContainer */
		$observerContainer = ObserverContainer::find($observer_id);
		$observer = new BasicObserver();

		$observer->setUserId($observerContainer->getUserId());
		$observer->setState($observerContainer->getState());

		$observer->setTask($this->loadTask($observerContainer->getRootTaskid(), $observer, $observerContainer));

		$this->observerHashToObserverContainerId[spl_object_hash($observer)] = $observer_id;
		return $observer;
	}


	/**
	 * Recursively loads a task.
	 *
	 * @param int               $taskContainerId    The container ID to load.
	 * @param Observer          $observer           Needed because we want to link the current task as soon as loaded.
	 * @param ObserverContainer $observerContainer  Needed because we need the current tasks container id for correct linking.
	 *
	 * @return Task
	 */
	private function loadTask(int $taskContainerId, Observer $observer, ObserverContainer $observerContainer) {
		global $DIC;
		$factory = new Injector($DIC);
		/** @var TaskContainer $taskContainer */
		$taskContainer = TaskContainer::find($taskContainerId);
		/** @noinspection PhpIncludeInspection */
		require_once($taskContainer->getClassPath());
		/** @var Task $task */
		$task = $factory->createInstance($taskContainer->getClassName(), [], null, true);

		/** @var ValueToTaskContainer $valueToTask */
		$valueToTasks = ValueToTaskContainer::where(['task_id' => $taskContainerId])->get();
		$inputs = [];
		foreach ($valueToTasks as $valueToTask) {
			$inputs[] = $this->loadValue($valueToTask->getValueId(), $observer, $observerContainer);
		}
		$task->setInput($inputs);

		if($taskContainerId == $observerContainer->getCurrentTaskid())
			$observer->setCurrentTask($task);

		$this->taskHashToTaskContainerId[spl_object_hash($task)] = $taskContainerId;
		return $task;
	}

	private function loadValue(int $valueContainerId, Observer $observer, ObserverContainer $observerContainer) {
		global $DIC;
		$factory = new Injector($DIC);

		/** @var ValueContainer $valueContainer */
		$valueContainer = ValueContainer::find($valueContainerId);
		/** @noinspection PhpIncludeInspection */
		require_once($valueContainer->getClassPath());
		/** @var Value $value */
		$value = $factory->createInstance($valueContainer->getClassName(), [], null, true);

		$value->unserialize($valueContainer->getSerialized());
		if($valueContainer->getHasParenttask()) {
			$value->setParentTask($this->loadTask($valueContainer->getParentTaskid(), $observer, $observerContainer));
		}

		$this->valueHashToValueContainerId[spl_object_hash($value)] = $valueContainerId;
		return $value;
	}

	public function deleteObserver($observer_id) {
		/** @var ObserverContainer $observer */
		$observer = ObserverContainer::where(['id' => $observer_id]);
		$observer->delete();

		/** @var TaskContainer $tasks */
		$tasks = TaskContainer::where(['observer_id' => $observer_id]);
		$tasks->delete();

		/** @var ValueContainer $values */
		$values = ValueContainer::where(['observer_id' => $observer_id]);
		$values->delete();

		/** @var ValueToTaskContainer $valueToTasks */
		$valueToTasks = ValueToTaskContainer::where(['obsever_id' => $observer_id]);
		$valueToTasks->delete();
	}


	/**
	 * @param int[] $observer_ids
	 *
	 * @return Observer[]
	 */
	public function loadObservers($observer_ids) {
		$observers = [];
		foreach ($observer_ids as $observer_id) {
			$observers[] = $this->loadObserver($observer_id);
		}
		return $observers;
	}
}