<?php

/*
 * JOBS
 * |id|input_id --> job_io:id|output_id --> job_io:id|
 *
 * JOB_IO
 * |id|serialized_data|container|
 *
 */



class TestilBackgroundTasksIO extends ilBackgroundTasksIO {

	protected $myvar1 = 34;
	protected $myvar2 = 74;
	public $var3;
}

$TestilBackgroundTasksIO = new TestilBackgroundTasksIO();
$TestilBackgroundTasksIO->var3 = 777;
$string_serialized = serialize($TestilBackgroundTasksIO);
echo "Serialized: <br>";
var_dump($string_serialized);

$unTestilBackgroundTasksIO = unserialize($string_serialized);
var_dump($unTestilBackgroundTasksIO); // FSX
//echo '<pre>' . print_r($string_serialized, 1) . '</pre>';


$input = new ilBTUserAndYearAndMonth(6, 2017, 0);
$bucket = new ilBTBucket();
$bucket
  ->setInput($input)
  ->addJob(new ilBTCollectCalendarFiles)
  ->addJob(new ilBTZipFiles)
  ->addJob(new ilBTDownloadFile);
$bucket->putInQueue();

$bucketObserver = new ilBucketObserver($ilUser->getId(), $bucket->getId());
$bucketObserver->create();