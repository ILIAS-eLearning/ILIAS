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
use ILIAS\BackgroundTasks\Exceptions\BucketNotFoundException;
use ILIAS\BackgroundTasks\Exceptions\SerializationException;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionSkippedException;

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

        // check this before saving the bucket state to prevent an orphaned entry with 0%
        if (!$DIC->settings()->get('soap_user_administration')) {
            $DIC->logger()->bgtk()->warning("SOAP not enabled, fallback to sync version");
            $sync_manager = new SyncTaskManager($this->persistence);
            $sync_manager->run($bucket);
            return;
        }

        $bucket->setState(State::SCHEDULED);
        $bucket->setCurrentTask($bucket->getTask());
        $DIC->backgroundTasks()->persistence()->saveBucketAndItsTasks($bucket);

        $DIC->logger()->bgtk()->info("Trying to call webserver");

        // Call SOAP-Server
        $soap_client = new \ilSoapClient();
        $soap_client->setResponseTimeout(1);
        $soap_client->enableWSDL(true);
        $soap_client->init();
        $session_id = session_id();
        $client_id = $DIC->http()->wrapper()->cookie()->retrieve(
            'ilClientId',
            $DIC->refinery()->byTrying([
                $DIC->refinery()->kindlyTo()->string(),
                $DIC->refinery()->always(
                    defined('CLIENT_ID') ? CLIENT_ID : null
                )
            ])
        );

        try {
            $soap_client->call(self::CMD_START_WORKER, [
                $session_id . '::' . $client_id,
            ]);
        } catch (\Throwable $t) {
            $DIC->logger()->bgtk()->warning($t->getMessage());
            $DIC->logger()->bgtk()->warning("Calling webserver failed, fallback to sync version");
            $sync_manager = new SyncTaskManager($this->persistence);
            $sync_manager->run($bucket);
            return;
        }
        $DIC->logger()->bgtk()->info("Calling webserver successful");
    }

    /**
     * @return bool|null
     */
    public function runAsync(): ?bool
    {
        global $DIC, $ilIliasIniFile;

        $n_of_tasks = $ilIliasIniFile->readVariable("background_tasks", "number_of_concurrent_tasks");
        $n_of_tasks = $n_of_tasks ?: 5;

        $DIC->logger()->bgtk()->info("Starting background job.");
        $persistence = $DIC->backgroundTasks()->persistence();

        // TODO search over all clients.
        $MAX_PARALLEL_JOBS = $n_of_tasks;
        if (count($persistence->getBucketIdsByState(State::RUNNING)) >= $MAX_PARALLEL_JOBS) {
            $DIC->logger()->bgtk()->info("Too many running jobs, worker going down.");

            return null;
        }

        $ids = [];
        while (true) {
            if ($ids === []) {
                $ids = $persistence->getBucketIdsByState(State::SCHEDULED);
                if ($ids === []) {
                    break;
                }
            }

            $bucket_id = (int) array_shift($ids);
            try {
                $bucket = $persistence->loadBucket($bucket_id);
            } catch (BucketNotFoundException $e) {
                // Another worker already removed this bucket. Keep the remaining ids.
                $DIC->logger()->bgtk()->info(
                    "Bucket with id $bucket_id no longer exists, skipping it."
                );
                continue;
            } catch (SerializationException $e) {
                // Missing or corrupt entries in il_bt_task / il_bt_value. Remove the bucket,
                // otherwise it would be picked up again and again.
                $DIC->logger()->bgtk()->warning(
                    "Could not load bucket with id $bucket_id, skipping and deleting it: " . $e->getMessage()
                );
                try {
                    $persistence->deleteBucketById($bucket_id);
                } catch (\Throwable $t) {
                    $DIC->logger()->bgtk()->error(
                        "Could not delete bucket with id $bucket_id, worker going down: " . $t->getMessage()
                    );
                    break;
                }
                continue;
            } catch (\Throwable $t) {
                // A database failure or a bucket that is still being saved must not be deleted.
                $DIC->logger()->bgtk()->error(
                    "Could not load bucket with id $bucket_id, worker going down: " . $t->getMessage()
                );
                break;
            }
            // A job may take long enough for the queue to change. Reload after it finishes.
            $ids = [];
            $observer = new PersistingObserver($bucket, $persistence);
            $task = $bucket->getTask();

            try {
                $this->executeTask($task, $observer);
                $bucket->setState(State::FINISHED);
                $this->persistence->updateBucket($bucket);
            } catch (UserInteractionSkippedException) {
                $bucket->setState(State::FINISHED);
                $this->persistence->deleteBucket($bucket);
            } catch (UserInteractionRequiredException) {
                // We're okay!
                $this->persistence->saveBucketAndItsTasks($bucket);
            } catch (\Throwable $e) {
                $persistence->deleteBucket($bucket);
                $DIC->logger()->bgtk()->info("Exception while async computing: "
                    . $e->getMessage());
                $DIC->logger()->bgtk()->info("Stack Trace: "
                    . $e->getTraceAsString());
            }
        }

        $DIC->logger()->bgtk()->info("One worker going down because there's nothing left to do.");

        return true;
    }
}
