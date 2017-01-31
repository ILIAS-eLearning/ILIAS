<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Exceptions\NoObserverForUserInteractionException;

/**
 * Interface BackgroundTaskManager
 *
 * @package ILIAS\BackgroundTasks
 *
 * We add, remove or observe buckets with the background task manager.
 *
 */
interface BackgroundTaskManager {

	/**
	 * @param $bucket   Bucket
	 * @param $user_ids int[]
	 * @return Bucket
	 *
	 * @throws NoObserverForUserInteractionException Is thrown when the user_id(s) cannot be
	 *                                               resolved to a user. Thus we would have a user
	 *                                               interaction without a user.
	 */
	public function putInQueueAndObserve(Bucket $bucket, $user_ids);


	/**
	 * @param $bucket
	 * @return mixed
	 */
	public function removeBucket($bucket);


	/**
	 * @param $bucket
	 * @param $user_id
	 * @return mixed
	 */
	public function addObserver($bucket, $user_id);


	/**
	 * @param $bucket
	 * @param $user_id
	 * @return mixed
	 */
	public function removeObserver($bucket, $user_id);


	/**
	 * @return Worker
	 */
	public function getWorker();
}