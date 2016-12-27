<?php

interface ilBTExceptionHandler {

	/**
	 * When working on a bucket and an exception occurs the exception handler will try to end the bucket operation gracefully.
	 *
	 * @param ilBTException $exception
	 * @param ilBTBucket $bucket
	 * @param ilBTJob|null $job
	 * @return void
	 */
	public function handleException( ilBTException $exception, ilBTBucket $bucket, ilBTJob $job = null);
}