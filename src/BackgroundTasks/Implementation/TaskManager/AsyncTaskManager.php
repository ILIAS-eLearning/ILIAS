<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionSkippedException;
use ILIAS\BackgroundTasks\Task\UserInteraction;

class AsyncTaskManager extends BasicTaskManager
{
    public const CMD_START_WORKER = 'startBackgroundTaskWorker';

    /**
     * This will add an Observer of the Task and start running the task.
     * @throws \Exception
     */
    public function run(Bucket $bucket): void
    {
        global $DIC;

        $bucket->setState(State::SCHEDULED);
        $bucket->setCurrentTask($bucket->getTask());
        $DIC->backgroundTasks()->persistence()->saveBucketAndItsTasks($bucket);

        $DIC->logger()->root()->info("[BT] Trying to call webserver");

        // Call SOAP-Server
        $soap_client = new \ilSoapClient();
        $soap_client->setResponseTimeout(1);
        $soap_client->enableWSDL(true);
        $soap_client->init();
        $session_id = session_id();
        $client_id = $DIC->http()->wrapper()->cookie()->has('ilClientId')
            ? $DIC->http()->wrapper()->cookie()->retrieve(
                'ilClientId',
                $DIC->refinery()->kindlyTo()->string()
            )
            : '';
        try {
            $soap_client->call(self::CMD_START_WORKER, array(
                $session_id . '::' . $client_id,
            ));
        } catch (\Throwable $t) {
            $DIC->logger()->root()->info("[BT] Calling Webserver failed, fallback to sync version");
            $sync_manager = new SyncTaskManager($this->persistence);
            $sync_manager->run($bucket);
        } finally {
            $DIC->logger()->root()->info("[BT] Calling webserver successful");
        }
    }

    /**
     * @return void|bool
     */
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
            $DIC->logger()->root()->info("[BT] Too many running jobs, worker going down.");

            return;
        }

        while (true) {
            $ids = $persistence->getBucketIdsByState(State::SCHEDULED);
            if (count($ids) === 0) {
                break;
            }

            $bucket = $persistence->loadBucket(array_shift($ids));
            $observer = new PersistingObserver($bucket, $persistence);
            $task = $bucket->getTask();

            try {
                $this->executeTask($task, $observer);
                $bucket->setState(State::FINISHED);
                $this->persistence->updateBucket($bucket);
            } catch (UserInteractionSkippedException $e) {
                $bucket->setState(State::FINISHED);
                $this->persistence->deleteBucket($bucket);
            } catch (UserInteractionRequiredException $e) {
                // We're okay!
                $this->persistence->saveBucketAndItsTasks($bucket);
            } catch (\Exception $e) {
                $persistence->deleteBucket($bucket);
                $DIC->logger()->root()->info("[BT] Exception while async computing: "
                    . $e->getMessage());
                $DIC->logger()->root()->info("[BT] Stack Trace: "
                    . $e->getTraceAsString());
            }
        }

        $DIC->logger()->root()->info("[BT] One worker going down because there's nothing left to do.");

        return true;
    }
}
