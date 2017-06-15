<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;


use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;


class AsyncTaskManager extends BasicTaskManager {
	/**
	 * This will add an Observer of the Task and start running the task.
	 *
	 * @param Bucket $bucket
	 *
	 * @return mixed|void
	 * @throws \Exception
	 *
	 */
	public function run(Bucket $bucket) {
		global $DIC, $ilLog;
		$persistence = $DIC->backgroundTasks()->persistence();

		$bucket->setState(State::SCHEDULED);
		$persistence->saveBucketAndItsTasks($bucket);

		$ilLog->write("[BackgroundTasks] Trying to call webserver");
		require_once("./Services/WebServices/SOAP/classes/class.ilSoapClient.php");
		$soap_client = new \ilSoapClient();
		$soap_client->setResponseTimeout(0);
		$soap_client->enableWSDL(true);
		$soap_client->init();
		$ilLog->write(var_export($soap_client->call('startBackgroundTaskWorker', array(
			session_id() . '::' . $_COOKIE['ilClientId'],
		)), true));
	}

	public function runAsync() {
		global $DIC, $ilLog, $ilIliasIniFile;

		$n_of_tasks = $ilIliasIniFile->readVariable("background_tasks","number_of_concurrent_tasks");
		$n_of_tasks = $n_of_tasks ? $n_of_tasks : 5;

		$ilLog->write("[BackgroundTask] Starting background job.");
		$persistence = $DIC->backgroundTasks()->persistence();
		//TODO search over all clients.
		$MAX_PARALLEL_JOBS = $n_of_tasks ;
		if( count($persistence->getBucketIdsByState(State::RUNNING)) >= $MAX_PARALLEL_JOBS) {
			$ilLog->write("[BackgroundTask] Too many running jobs, worker going down.");
			return;
		}

		while(true) {
			$ids = $persistence->getBucketIdsByState(State::SCHEDULED);
			if(!count($ids))
				break;

			$bucket = $persistence->loadBucket(array_shift($ids));
			$observer = new PersistingObserver($bucket, $persistence);
			$task = $bucket->getTask();

			try {
				$this->executeTask($task, $observer);
				$bucket->setState(State::FINISHED);
				$this->persistence->updateBucket($bucket);
			} catch (UserInteractionRequiredException $e) {
				// We're okay!
				$this->persistence->saveBucketAndItsTasks($bucket);
			} catch (\Exception $e) {
				$persistence->deleteBucket($bucket);
				$ilLog->write("[BackgroundTasks] Exception while async computing: " . $e->getMessage());
			}
		}

		$ilLog->write("[BackgroundTasks] One worker going down because there's nothing left to do.");
		return true;
	}
}