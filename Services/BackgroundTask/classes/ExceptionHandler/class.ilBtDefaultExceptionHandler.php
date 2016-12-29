<?php

/**
 * Class ilBTDefaultExceptionHandler
 *
 * @author Oaskar Truffer <ot@studer-raimann.ch>
 */
class ilBTDefaultExceptionHandler implements ilBTExceptionHandler {

	/**
	 * Removes all buckets, inputs and jobs from the database belonging to the current bucket.
	 * Writes a something went wrong entry to the user output.
	 *
	 * @param ilBTException $exception
	 * @param ilBTBucket $bucket
	 * @param ilBTJob|null $job
	 * @return void
	 */
	public function handleException(ilBTException $exception, ilBTBucket $bucket, ilBTJob $job = null) {
		// TODO: Implement handleException() method.
	}
}