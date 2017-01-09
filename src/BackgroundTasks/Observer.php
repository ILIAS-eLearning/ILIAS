<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Observer
 *
 * @package ILIAS\BackgroundTasks
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
	 * store the observerdata to persistence layer
	 */
	public function store();
}
