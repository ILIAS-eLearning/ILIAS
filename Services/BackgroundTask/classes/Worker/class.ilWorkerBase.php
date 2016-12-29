<?php

/**
 * Class ilWorkerBase
 *
 * The default worker goes through the different tasks. He does not go on if already some tasks are running.
 *
 * Furthermore the default worker resolves one Bucket per step.
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilWorkerBase implements ilWorker {

	public function doWork() {
		while ($this->workConditions() && $bucketContainer = $this->getNextBucketContainer()) {
			$this->doStep($bucketContainer->getBucket());
		}
	}


	/**
	 * @return ilBTBucketContainer
	 */
	protected function getNextBucketContainer() {
		return ilBTBucketContainer::where(array(
			'isRunning' => 0,
		))->orderBy('id')->first();
	}


	/**
	 * @return int
	 */
	protected function numberOfRunningJobs() {
		return ilBTBucketContainer::where(array(
			'isRunning' => 0,
		))->count();
	}


	/**
	 * @return bool
	 */
	protected function workConditions() {
		return $this->numberOfRunningJobs() < 3;
	}


	/**
	 * @param $bucket ilBTBucket
	 */
	protected function doStep(ilBTBucket $bucket) {
		$bucket->runBucket();
	}
}