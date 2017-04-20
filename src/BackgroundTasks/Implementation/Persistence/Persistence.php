<?php

namespace ILIAS\BackgroundTasks\Implementation\Persistence;

use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

class Persistence {

	/**
	 * @var Persistence
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

	protected function __construct() {
	}

	public function instance() {
		if (!self::$instance)
			self::$instance = new Persistence();
		return self::$instance;
	}

	public function saveObserverAndItsTasks(Observer $observer) {

	}

	public function saveObserver(Observer $observer) {
		$observerContainer = new ObserverContainer($this->observerHashToObserverContainerId[spl_object_hash($observer)]);
		$this->writeObjectInContainer($observer, $observerContainer);
		$observerContainer->setPercentage($observer->getPercentage());
		$observerContainer->setCurrentTaskId($this->getTaskContainerId($observer->getCurrentTask()));
		$observerContainer->setRootTaskId($this->getTaskContainerId($observer->getTask()));
		$observerContainer->save();
		$this->observerHashToObserverContainerId[spl_object_hash($observer)] = $observerContainer->getId();
	}

	public function saveTask(Task $task) {
		$taskContainer = new TaskContainer($this->taskHashToTaskContainerId[spl_object_hash($task)]);
		$taskContainer->setType($task->getType());
		$reflection = new \ReflectionClass(get_class($task));
		$taskContainer->setClassName(get_class($task));
		$taskContainer->setClassPath($reflection->getFileName());

	}

	/**
	 * @param $task Task
	 * @return int
	 * @throws SerializationException
	 */
	protected function getTaskContainerId(Task $task) {
		if(! $this->taskHashToTaskContainerId[spl_object_hash($task)] )
			throw new SerializationException("Could not resolve container id of task: " . print_r($task, true));
		return $this->taskHashToTaskContainerId[spl_object_hash($task)];
	}

	/**
	 * @param $value Value
	 * @return int
	 * @throws SerializationException
	 */
	protected function getValueContainerId($value) {
		if(! $this->valueHashToValueContainerId[spl_object_hash($value)] )
			throw new SerializationException("Could not resolve container id of value: " . print_r($value, true));
		return $this->valueHashToValueContainerId[spl_object_hash($value)];
	}

	protected function writeObjectInContainer($object, $container) {
		$objectReflection = new \ReflectionClass(get_class($object));
		$containerReflection = new \ReflectionClass(get_class($container));
		$objectProperties = $objectReflection->getProperties();
		$containerProperties = $containerReflection->getProperties();

		// TODO: Faster implementation using arrays. We can go down from O(n*m) to O(n + m).
		foreach ($objectProperties as $objectProperty) {
			foreach ($containerProperties as $containerProperty){
				if($objectProperty->getName() == $containerProperty->getName())
					$containerProperty->setValue($container, $objectProperty->getValue($object));
			}
		}
	}
}