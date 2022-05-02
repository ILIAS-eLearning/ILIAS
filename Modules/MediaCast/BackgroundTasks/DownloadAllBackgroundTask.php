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

namespace ILIAS\MediaCast\BackgroundTasks;

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;

/**
 * Download all items
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DownloadAllBackgroundTask
{
    protected int $mcst_ref_id = 0;
    protected int $mcst_id = 0;
    protected ?int $user_id = null;
    protected ?\ILIAS\BackgroundTasks\Task\TaskFactory $task_factory = null;
    protected ?\ILIAS\BackgroundTasks\TaskManager $task_manager = null;
    protected \ilLanguage $lng;
    private ?\ilLogger $logger = null;

    public function __construct(
        int $a_usr_id,
        int $a_mcst_ref_id,
        int $a_mcst_id
    ) {
        global $DIC;

        $this->user_id = $a_usr_id;
        $this->mcst_ref_id = $a_mcst_ref_id;
        $this->mcst_id = $a_mcst_id;

        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->task_manager = $DIC->backgroundTasks()->taskManager();
        $this->logger = $DIC->logger()->mcst();
    }

    public function run() : bool
    {
        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $this->logger->debug("* Create task 'collect_data_job' using the following values:");
        $this->logger->debug("job class = " . DownloadAllCollectFilesJob::class);
        $this->logger->debug("mcst_id = " . $this->mcst_id . ", mcst_ref_id = " . $this->mcst_ref_id . ", user_id = " . (int) $this->user_id);

        $collect_data_job = $this->task_factory->createTask(
            DownloadAllCollectFilesJob::class,
            [
                (int) $this->user_id,
                (int) $this->mcst_ref_id
            ]
        );

        $this->logger->debug("* Create task 'zip job' using the following values:");
        $this->logger->debug("job class = " . DownloadAllZipJob::class);
        $this->logger->debug("sending as input the task called->collect_data_job");

        $zip_job = $this->task_factory->createTask(DownloadAllZipJob::class, [$collect_data_job]);

        $download_name = \ilFileUtils::getASCIIFilename(\ilObject::_lookupTitle($this->mcst_id));
        $bucket->setTitle($download_name);

        $this->logger->debug("* Create task 'download_interaction' using the following values:");
        $this->logger->debug("job class = " . DownloadAllZipInteraction::class);
        $this->logger->debug("download_name which is the same as bucket title = " . $download_name . " + the zip_job task");
        // see comments here -> https://github.com/leifos-gmbh/ILIAS/commit/df6fc44a4c85da33bd8dd5b391a396349e7fa68f
        $download_interaction = $this->task_factory->createTask(DownloadAllZipInteraction::class, [$zip_job, $download_name]);

        //download name
        $bucket->setTask($download_interaction);
        $this->task_manager->run($bucket);
        return true;
    }
}
