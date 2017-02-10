<?php

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exception;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

class TaskBucket implements Bucket {

	/** @var Task[] */
	protected $tasks = [];

	/** @var string */
	protected $title = '';

	/** @var Observer[] */
	protected $observers = [];

	/**
	 * @param \ILIAS\BackgroundTasks\Task $task
	 * @return Bucket
	 */
	public function addTask(Task $task) {
		$this->tasks[] = $task;
	}

	/**
	 * @return int
	 */
	public function countTasks() {
		return count($this->tasks);
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param $title string
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @param \ILIAS\BackgroundTasks\Observer $observer when no observer is set, Common observer is used
	 * @return $this
	 */
	public function addObserver(Observer $observer) {
		$this->observers[] = $observer;
	}

	/**
	 * @return Observer[]
	 */
	public function getObserver() {
		return $this->observers;
	}
}