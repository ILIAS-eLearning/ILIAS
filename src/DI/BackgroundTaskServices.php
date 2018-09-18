<?php

namespace ILIAS\DI;

use ILIAS\BackgroundTasks\Dependencies\Injector;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;

/**
 * Class BackgroundTaskServices
 *
 * @package ILIAS\DI
 *
 * @since   5.3
 */
class BackgroundTaskServices {

	/**
	 * @var Container
	 */
	protected $container;


	/**
	 * BackgroundTaskServices constructor
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}


	/**
	 * @return TaskFactory
	 */
	public function taskFactory() {
		return $this->container['bt.task_factory'];
	}


	/**
	 * @return Persistence
	 */
	public function persistence() {
		return $this->container['bt.persistence'];
	}


	/**
	 * @return TaskManager
	 */
	public function taskManager() {
		return $this->container['bt.task_manager'];
	}


	/**
	 * @return Injector
	 */
	public function injector() {
		return $this->container['bt.injector'];
	}
}
