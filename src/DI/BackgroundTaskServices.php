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
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * @since   5.3
 */
final class BackgroundTaskServices {

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
	public function taskFactory(): TaskFactory {
		return $this->container['bt.task_factory'];
	}


	/**
	 * @return Persistence
	 */
	public function persistence(): Persistence {
		return $this->container['bt.persistence'];
	}


	/**
	 * @return TaskManager
	 */
	public function taskManager(): TaskManager {
		return $this->container['bt.task_manager'];
	}


	/**
	 * @return Injector
	 */
	public function injector(): Injector {
		return $this->container['bt.injector'];
	}
}
