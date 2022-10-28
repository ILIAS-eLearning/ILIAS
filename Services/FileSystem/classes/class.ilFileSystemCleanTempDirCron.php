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
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return false;
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 0;
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

        $num_folders = count($deleted_folders);
        $num_files = count($deleted_files);

        $result = new ilCronJobResult();
        $result->setMessage($num_folders . " folders and " . $num_files . " files have been deleted.");
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
