<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;

class AsyncTaskManager extends BasicTaskManager
{
    const CMD_START_WORKER = 'startBackgroundTaskWorker';


    /**
     * This will add an Observer of the Task and start running the task.
     *
     * @param Bucket $bucket
     *
     * @return mixed|void
     * @throws \Exception
     *
     */
    public function run(Bucket $bucket)
    {
        global $DIC;

        $bucket->setState(State::SCHEDULED);
        $bucket->setCurrentTask($bucket->getTask());
        $DIC->backgroundTasks()->persistence()->saveBucketAndItsTasks($bucket);

        $DIC->logger()->root()->info("[BackgroundTasks] Trying to call webserver");

        // Call SOAP-Server
        $soap_client = new \ilSoapClient();
        $soap_client->setResponseTimeout(1);
        $soap_client->enableWSDL(true);
        $soap_client->init();
        $session_id = session_id();
        $ilClientId = $_COOKIE['ilClientId'];
        $call = $soap_client->call(self::CMD_START_WORKER, array(
            $session_id . '::' . $ilClientId,
        ));
        $DIC->logger()->root()->info("[BackgroundTasks] After SOAP Call");
        $DIC->logger()->root()->info(var_export($call, true));
    }


    public function runAsync()
    {
        global $DIC, $ilIliasIniFile;

        $n_of_tasks = $ilIliasIniFile->readVariable("background_tasks", "number_of_concurrent_tasks");
        $n_of_tasks = $n_of_tasks ? $n_of_tasks : 5;

        $DIC->logger()->root()->info("[BackgroundTask] Starting background job.");
        $persistence = $DIC->backgroundTasks()->persistence();

        // TODO search over all clients.
        $MAX_PARALLEL_JOBS = $n_of_tasks;
        if (count($persistence->getBucketIdsByState(State::RUNNING)) >= $MAX_PARALLEL_JOBS) {
            $DIC->logger()->root()->info("[BackgroundTask] Too many running jobs, worker going down.");

            return;
        }

        while (true) {
            $ids = $persistence->getBucketIdsByState(State::SCHEDULED);
            if (!count($ids)) {
                break;
            }

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
                $DIC->logger()->root()->info("[BackgroundTasks] Exception while async computing: "
                    . $e->getMessage());
                $DIC->logger()->root()->info("[BackgroundTasks] Stack Trace: "
                    . $e->getTraceAsString());
            }
        }

        $DIC->logger()->root()->info("[BackgroundTasks] One worker going down because there's nothing left to do.");

        return true;
    }
}
