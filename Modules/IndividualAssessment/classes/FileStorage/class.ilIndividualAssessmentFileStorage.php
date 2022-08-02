<?php declare(strict_types=1);

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
    const PATH_POSTFIX = "iass";
    const PATH_PREFIX = "IASS";

    protected ?int $user_id = null;

    public static function getInstance(int $container_id = 0) : ilIndividualAssessmentFileStorage
    {
        return new self(self::STORAGE_WEB, true, $container_id);
    }

    /**
     * part of the folder structure in ILIAS webdir.
     */
    protected function getPathPostfix() : string
    {
        return self::PATH_POSTFIX;
    }

    /**
     * part of the folder structure in ILIAS webdir.
     */
    protected function getPathPrefix() : string
    {
        return self::PATH_PREFIX;
    }

    /**
     * Is the webdir folder for this IA empty?
     */
    public function isEmpty() : bool
    {
        $files = $this->readDir();

        return count($files) == 0;
    }

    /**
     * Set the user id for an extra folder of each participant in the IA
     */
    public function setUserId(int $user_id) : void
    {
        $this->user_id = $user_id;
    }

    /**
     * creates the folder structure
     */
    public function create() : void
    {
        if (!file_exists($this->getAbsolutePath())) {
            ilFileUtils::makeDirParents($this->getAbsolutePath());
        }
    }

    /**
     * Get the absolute path for files
     */
    public function getAbsolutePath() : string
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
    public function readDir() : array
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
    public function uploadFile(UploadResult $file) : bool
    {
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file->getName());
        $new_file = $path . "/" . $clean_name;

        ilFileUtils::moveUploadedFile(
            $file->getPath(),
            $clean_name, // This parameter does not do a thing
            $new_file
        );

        return true;
    }

    /**
     * Delete the existing file
     */
    public function deleteCurrentFile() : void
    {
        $files = $this->readDir();
        $this->deleteFile($this->getAbsolutePath() . "/" . $files[0]);
    }

    /**
     * Get the path of file
     */
    public function getFilePath() : string
    {
        $files = $this->readDir();
        return $this->getAbsolutePath() . "/" . $files[0];
    }

    /**
     * Delete a file by name
     */
    public function deleteFileByName(string $file_name) : void
    {
        $this->deleteFile($this->getAbsolutePath() . "/" . $file_name);
    }
}
