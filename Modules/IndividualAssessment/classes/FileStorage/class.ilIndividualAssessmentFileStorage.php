<?php

declare(strict_types=1);

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

use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Handles the file upload and folder creation for files uploaded in grading form
 */
class ilIndividualAssessmentFileStorage extends ilFileSystemAbstractionStorage implements IndividualAssessmentFileStorage
{
    public const PATH_POSTFIX = "iass";
    public const PATH_PREFIX = "IASS";

    protected ?int $user_id = null;

    public static function getInstance(int $container_id = 0): ilIndividualAssessmentFileStorage
    {
        return new self(self::STORAGE_WEB, true, $container_id);
    }

    /**
     * part of the folder structure in ILIAS webdir.
     */
    protected function getPathPostfix(): string
    {
        return self::PATH_POSTFIX;
    }

    /**
     * part of the folder structure in ILIAS webdir.
     */
    protected function getPathPrefix(): string
    {
        return self::PATH_PREFIX;
    }

    /**
     * Set the user id for an extra folder of each participant in the IA
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * creates the folder structure
     */
    public function create(): void
    {
        if (!file_exists($this->getAbsolutePath())) {
            ilFileUtils::makeDirParents($this->getAbsolutePath());
        }
    }

    /**
     * Get the absolute path for files
     */
    public function getAbsolutePath(): string
    {
        $path = parent::getAbsolutePath();

        if ($this->user_id) {
            $path .= "/user_" . $this->user_id;
        }

        return $path;
    }

    /**
     * Read the dir
     *
     * @return string[]
     */
    public function readDir(): array
    {
        if (!is_dir($this->getAbsolutePath())) {
            $this->create();
        }

        $fh = opendir($this->getAbsolutePath());
        $files = array();
        while ($file = readdir($fh)) {
            if ($file != "." && $file != ".." && !is_dir($this->getAbsolutePath() . "/" . $file)) {
                $files[] = $file;
            }
        }
        closedir($fh);

        return $files;
    }

    /**
     * Upload the file
     */
    public function uploadFile(UploadResult $file): string
    {
        $path = $this->getAbsolutePath();

        $clean_name = ilFileUtils::getValidFilename($file->getName());
        $new_file = $path . "/" . $clean_name;

        ilFileUtils::moveUploadedFile(
            $file->getPath(),
            $clean_name, // This parameter does not do a thing
            $new_file
        );

        return $clean_name;
    }

    /**
     * Delete the existing file
     */
    public function deleteAllFilesBut(?string $filename): void
    {
        $files = $this->readDir();
        foreach ($files as $file) {
            if ($file === $filename) {
                continue;
            }
            $this->deleteFile($this->getAbsolutePath() . "/" . $file);
        }
    }
}
