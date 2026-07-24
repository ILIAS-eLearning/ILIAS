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

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\DI\Container;
use ILIAS\Cron\Schedule\CronJobScheduleType;

/**
 * Class ilFileSystemCleanTempDirCron
 *
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilFileSystemCleanTempDirCron extends ilCronJob
{
    protected \ILIAS\Filesystem\Filesystem $filesystem;

    protected ilLanguage $language;

    protected ilLogger $logger;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        /**
         * @var $DIC Container
         */
        global $DIC;
        if ($DIC->offsetExists('lng')) {
            $this->language = $DIC['lng'];
        }
        if ($DIC->offsetExists('filesystem')) {
            $this->filesystem = $DIC->filesystem()->temp();
        }
        if ($DIC->offsetExists('ilLoggerFactory')) {
            $this->logger = $DIC->logger()->root();
        }
    }

    private function initDependencies(): void
    {
    }

    public function getId(): string
    {
        return "file_system_clean_temp_dir";
    }

    public function getTitle(): string
    {
        return $this->language->txt('file_system_clean_temp_dir_cron');
    }

    public function getDescription(): string
    {
        return $this->language->txt("file_system_clean_temp_dir_cron_info");
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): CronJobScheduleType
    {
        return CronJobScheduleType::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): ilCronJobResult
    {
        $this->initDependencies();
        // only delete files and folders older than ten days to prevent issues with ongoing processes (e.g. zipping a folder)
        $date = "until 10 day ago";

        // files are deleted before folders to prevent issues that would arise when trying to delete a (no longer existing) file in a deleted folder.
        $files = $this->filesystem->finder()->in([""]);
        $files = $files->files();
        $files = $files->date($date);
        $files = $files->getIterator();
        $files->rewind();
        $deleted_files = [];
        while ($files->valid()) {
            try {
                $file_match = $files->current();
                $path = $file_match->getPath();
                if ($file_match->isFile()) {
                    $this->filesystem->delete($path);
                    $deleted_files[] = $path;
                }
                $files->next();
            } catch (Throwable $t) {
                $this->logger->error(
                    "Cron Job \"Clean temp directory\" could not delete " . $path
                    . "due to the following exception: " . $t->getMessage()
                );
                $files->next();
            }
        }

        // the folders are sorted based on their path length to ensure that nested folders are deleted first
        // thereby preventing any issues due to deletion attempts on no longer existing folders.
        $folders = $this->filesystem->finder()->in([""]);
        $folders = $folders->directories();
        $folders = $folders->date($date);
        $folders = $folders->sort(fn (
            Metadata $a,
            Metadata $b
        ): int => strlen($a->getPath()) - strlen($b->getPath()));
        $folders = $folders->reverseSorting();
        $folders = $folders->getIterator();

        $deleted_folders = [];

        $folders->rewind();
        while ($folders->valid()) {
            try {
                $folder_match = $folders->current();
                $path = $folder_match->getPath();
                if ($folder_match->isDir()) {
                    $this->filesystem->deleteDir($path);
                    $deleted_folders[] = $path;
                }
                $folders->next();
            } catch (Throwable $t) {
                $this->logger->error(
                    "Cron Job \"Clean temp directory\" could not delete " . $path
                    . "due to the following exception: " . $t->getMessage()
                );
                $folders->next();
            }
        }

        $corrupted_paths = $this->reportCorruptedPaths();

        $num_folders = count($deleted_folders);
        $num_files = count($deleted_files);
        $num_corrupted = count($corrupted_paths);

        $message = $num_folders . " folders and " . $num_files . " files have been deleted.";
        if ($num_corrupted > 0) {
            $message .= " " . $num_corrupted . " path(s) could not be processed and must be removed manually,"
                . " see the log for details.";
        }

        $result = new ilCronJobResult();
        $result->setMessage($message);
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    /**
     * Paths containing control characters (e.g. a tab or a line break) are rejected by the
     * path normalizer of the filesystem. They can neither be listed nor deleted through it,
     * which is why they are skipped silently during the cleanup above. Report them, so they
     * can be removed manually.
     *
     * @return string[] the reported paths
     */
    private function reportCorruptedPaths(): array
    {
        try {
            $contents = $this->filesystem->listContents("", true);
        } catch (Throwable $t) {
            // the cleanup itself already succeeded, this report must never fail the job
            $this->logger->error(
                "Cron Job \"Clean temp directory\" could not look for corrupted paths"
                . " due to the following exception: " . $t->getMessage()
            );
            return [];
        }

        $corrupted_paths = [];
        foreach ($contents as $metadata) {
            $path = $metadata->getPath();
            if (preg_match('#\p{C}#u', $path) !== 1) {
                continue;
            }

            // everything below an already reported path shares its unusable prefix,
            // reporting the topmost path of such a subtree is sufficient
            foreach ($corrupted_paths as $reported_path) {
                if (str_starts_with($path, $reported_path . '/')) {
                    continue 2;
                }
            }

            $corrupted_paths[] = $path;
            $this->logger->error(
                "Cron Job \"Clean temp directory\" cannot delete \"" . $path
                . "\" because its path contains characters which are rejected by the filesystem."
                . " Please remove it manually."
            );
        }

        return $corrupted_paths;
    }
}
