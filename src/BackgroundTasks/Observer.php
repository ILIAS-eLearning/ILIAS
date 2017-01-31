<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Observer
 *
 * @package ILIAS\BackgroundTasks
 *
 *                Observers show a background task in the user interface.
 */
interface Observer {

	/**
	 * @return int
	 */
	public function getUserId();


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id);


	/**
	 * @return int
	 */
	public function getBucketId();


	/**
	 * @param int $bucket_id
	 */
	public function setBucketId($bucket_id);


	/**
	 * Used by a job to notify his percentage.
	 *
	 * @param $task       Task
	 * @param $percentage int
	 */
	public function notifyPercentage(Task $task, $percentage);


	/**
	 * store the observerdata to persistence layer
	 */
	public function store();
}
