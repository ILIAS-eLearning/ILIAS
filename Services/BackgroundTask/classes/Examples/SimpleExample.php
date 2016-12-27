<?php

class ILIASObjectIDs extends ilBackgroundTaskIO {
	/** @var  int[] */
	public $ids;

	public function __construct($ilias_ids) {
		$this->ids = $ilias_ids;
	}
}

class ILIASObjects extends ilBackgroudTaskIO {
	/** @var  ilObject[] */
	public $objects;

	public function __construct($objects) {
		$this->objects = $objects;
	}
}

/**
 * Class collectILIASObjectsJob
 *
 * This Job takes a list of Object Ids and returns a list of ilObjects.
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class CollectILIASObjectsJob extends ilBTJobBase {

	/**
	 * @param ilBTIO $input
	 * @return ilBTIO
	 */
	public function run($input) {
		/** @var ILIASObjectIDs $input */
		$input = $input;
		$ids = $input->ids;
		$objects = []; //TODO load from db.
		return new ILIASObjects($objects);
	}

	/**
	 * @return bool Returns true iff the job supports giving feedback about the percentage done.
	 */
	public function supportsPercentage() {
		return false;
	}

	/**
	 * @return int Returns 0 if !supportsPercentage and the percentage otherwise.
	 */
	public function getPercentage() {
		return 0;
	}

	/** @return returns true iff the job's output ONLY depends on the input. Stateless task results may be cached! */
	public function isStateless() {
		return false;
	}

	/**
	 * @return string Class-Name of the ilBTIO
	 */
	public function getInputType() {
		return ILIASObjectIDs::class;
	}

	/**
	 * @return string
	 */
	public function getOutputType() {
		return ILIASObjects::class;
	}
}

class DownloadILIASObjectsAsJSONJob extends ilBTUserInteractionBase {

	/**
	 * @return string Class-Name of the ilBTIO
	 */
	public function getInputType() {
		return ILIASObjects::class;
	}

	/**
	 * @return string
	 */
	public function getOutputType() {
		return ilBTIOVoid::class;
	}

	/**
	 * @return array returns an array with value => lang_var. What options can the user select?
	 */
	public function getOptions() {
		return array(
			"cancel" => "cancel",
			"download" => "download"
		);
	}

	/**
	 * Create the output here.
	 * @param $input ILIASObjects
	 * @param $user_input
	 * @return void
	 */
	public function interaction($input, $user_input) {
		$objects = $input->objects;
		$this->output = new ilBTIOVoid();

		switch ($user_input) {
			case "cancel":
				break; // we don't need to do anything, user interaction finished.
			case "download":
				$send = serialize($objects);
				// TODO send as file.
				break;
		}
	}
}

// we make a background Task that gathers object ids and offers a serialized download with all the object details in them.
$input = new ILIASObjectIDs([6]);
$bucket = new ilBTBucketBase();
$bucket = $bucket
	->setInput($input)
	->addTask(new CollectILIASObjectsJob())
	->addTask(new DownloadILIASObjectsAsJSONJob())
	->setTitle($lng->txt("ilias_objects_bucket_title"));

$bucket->putInQueueAndObserve($ilUser->getId()); // will raise an exception if the input/output types do not match.
// $bucket->putInQueue(); Will raise an exception if there are user interactions in the bucket (no observer => no one can interact)
