<?php

namespace ILIAS\DI;

use ILIAS\BackgroundTasks\Task\TaskFactory;

/**
 */
class BackgroundTaskServices {

	/**
	 * @var    Container
	 */
	protected $container;


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
	 * @return mixed
	 */
	public function persistence() {
		return $this->container['bt.persistence'];
	}
}