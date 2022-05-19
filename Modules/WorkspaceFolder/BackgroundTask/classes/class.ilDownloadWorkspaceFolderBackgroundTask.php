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

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/**
 * Background task for downloads
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDownloadWorkspaceFolderBackgroundTask
{
    private ?ilLogger $logger = null;
    protected ilLanguage $lng;
    protected int $user_id;
    /** @var int[] */
    protected array $object_wsp_ids;
    /**
     * determines whether the task has been initiated by a
     * folder's action drop-down to prevent a folder duplicate inside the zip.
     */
    protected bool $initiated_by_folder_action = false;
    protected ?\ILIAS\BackgroundTasks\Task\TaskFactory $task_factory = null;
    protected string $bucket_title;
    protected bool $has_files = false;

    public function __construct(
        int $a_usr_id,
        array $a_object_wsp_ids,
        bool $a_initiated_by_folder_action = false
    ) {
        global $DIC;
        $this->logger = ilLoggerFactory::getLogger("pwsp");
        $this->user_id = $a_usr_id;
        $this->object_wsp_ids = $a_object_wsp_ids;
        $this->initiated_by_folder_action = $a_initiated_by_folder_action;
        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->lng = $DIC->language();
    }

    public function setBucketTitle(string $a_title) : void
    {
        $this->bucket_title = $a_title;
    }

    public function getBucketTitle() : string
    {
        //TODO: fix ilUtil zip stuff
        // Error If name starts "-"
        // error massage from ilUtil->execQuoted = ["","zip error: Invalid command arguments (short option 'a' not supported)"]
        if (substr($this->bucket_title, 0, 1) === "-") {
            $this->bucket_title = ltrim($this->bucket_title, "-");
        }

        return $this->bucket_title;
    }

    public function run() : bool
    {
        // This is our Bucket
        $this->logger->info('Started download workspace files background task');
        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);
        $this->logger->debug('Created bucket and set the following user id: ' . $this->user_id);

        // Copy Definition
        $definition = new ilWorkspaceCopyDefinition();
        $normalized_name = ilFileUtils::getASCIIFilename($this->getBucketTitle());
        $definition->setTempDir($normalized_name);
        $definition->setObjectWspIds($this->object_wsp_ids);
        $this->logger->debug('Created copy definition and added the following tempdir: ' . $normalized_name);

        // Collect all files by the definition and prevent duplicates
        $collect_job = $this->task_factory->createTask(ilCollectWorkspaceFilesJob::class, [$definition, $this->initiated_by_folder_action]);
        $this->logger->debug('Collected files based on the following object ids: ');
        $this->logger->dump($this->object_wsp_ids);

        // Check the FileSize
        $file_size_job = $this->task_factory->createTask(ilCheckSumOfWorkspaceFileSizesJob::class, [$collect_job]);

        // Show problems with file-limit
        $file_size_interaction = $this->task_factory->createTask(ilSumOfWorkspaceFileSizesTooLargeInteraction::class, [$file_size_job]);
        $this->logger->debug('Determined the sum of all file sizes');

        // move files from source dir to target directory
        $copy_job = $this->task_factory->createTask(ilCopyWorkspaceFilesToTempDirectoryJob::class, [$file_size_interaction]);

        // Zip it
        $zip_job = $this->task_factory->createTask(ilZipJob::class, [$copy_job]);
        $this->logger->debug('Moved files from source- to target-directory');

        // Download
        $download_name = new StringValue();
        $download_name->setValue($normalized_name . '.zip');
        $download_interaction = $this->task_factory->createTask(ilDownloadZipInteraction::class, [$zip_job, $download_name]);
        $this->logger->debug('Created a download interaction with the following download name: ' . $download_name->getValue());

        // last task to bucket
        $bucket->setTask($download_interaction);
        $bucket->setTitle($this->getBucketTitle());
        $this->logger->debug('Added last task to bucket and set the following title: ' . $this->getBucketTitle());

        $task_manager = $GLOBALS['DIC']->backgroundTasks()->taskManager();
        $task_manager->run($bucket);
        $this->logger->debug('Ran bucket in task manager');

        return true;
    }
}
