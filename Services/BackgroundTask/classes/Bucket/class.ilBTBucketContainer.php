<?php

/**
 * Class ilBTBucketContainer
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBTBucketContainer extends ActiveRecord {
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int Reference to a ilBTIOContainer. The input that's running on the current job.
	 */
	protected $currentInput;

	/**
	 * @var ilBTJobContainer Reference to a ilBTJobContainer. The job that is running.
	 */
	protected $currentJob;

	/**
	 * @var bool
	 */
	protected $isRunning;

	/**
	 * @var int Timestamp.
	 */
	protected $startTime;

	/**
	 * @var int Timestamp
	 */
	protected $endTime;

	/**
	 * @return ilBTBucket
	 */
	public function getBucket() {
		//TODO: implement instanciation of a bucket.
		return null;
	}
}