<?php

class ilBucketObserver extends ActiveRecord {
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var int
	 */
	protected $bucket_id;

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getBucketId() {
		return $this->bucket_id;
	}

	/**
	 * @param int $bucket_id
	 */
	public function setBucketId($bucket_id) {
		$this->bucket_id = $bucket_id;
	}

}