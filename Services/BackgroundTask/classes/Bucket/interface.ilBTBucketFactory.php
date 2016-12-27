<?php

interface ilBTBucketFactory {
	/**
	 * @param $bucketContainer ilBTBucketContainer
	 * @return ilBTBucket
	 */
	public function buildFromBucketContainer($bucketContainer);
}