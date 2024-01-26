<?php

use ILIAS\FileUpload\DTO\UploadResult;

require_once("Modules/IndividualAssessment/interfaces/FileStorage/interface.IndividualAssessmentFileStorage.php");
include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
* Handles the fileupload and folder creation for files uploaded in grading form
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
*
*/
class ilIndividualAssessmentFileStorage extends ilFileSystemStorage implements IndividualAssessmentFileStorage
{
    public static function getInstance($a_container_id = 0)
    {
        return new self(self::STORAGE_WEB, true, $a_container_id);
    }

    /**
     * part of the folder structure in ILIAS webdir.
     *
     * @return string
     */
    protected function getPathPostfix()
    {
        return 'iass';
    }

    /**
     * part of the folder structure in ILIAS webdir.
     *
     * @return string
     */
    protected function getPathPrefix()
    {
        return 'IASS';
    }

    /**
     * Is the webdir folder for this IA empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        $files = $this->readDir();

        return (count($files) == 0) ? true : false;
    }

    /**
     * Set the user id for an extra folder of each participant in the IA
     *
     * @param int 	$user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * creates the folder structure
     *
     * @return boolean
     */
    public function create()
    {
        if (!file_exists($this->getAbsolutePath())) {
            ilUtil::makeDirParents($this->getAbsolutePath());
        }
        return true;
    }

    /**
     * Get the absolute path for files
     *
     * @return string
     */
    public function getAbsolutePath()
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
    public function readDir()
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
     *
     * @param UploadResult $result
     *
     * @return bool
     * @throws ilException
     */
    public function uploadFile(UploadResult $result)
    {
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $result->getName());
        $new_file = $path . "/" . $clean_name;

        ilUtil::moveUploadedFile(
            $result->getPath(),
            $clean_name, // This parameter does not do a thing
            $new_file
        );

        return true;
    }

    /**
     * Delete the existing file
     */
    public function deleteCurrentFile()
    {
        $this->deleteFile($this->getFilePath());
    }

    /**
     * Get the path of the file
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->getAbsolutePath() . "/" . $this->getFileName();
    }

    /**
     * Get the name of the file
     *
     * @return string
     */
    public function getFileName()
    {
        $files = $this->readDir();
        return $files[0];
    }

    /**
     * Delete a file by name
     *
     * @param string 	$file_name
     */
    public function deleteFileByName($file_name)
    {
        $this->deleteFile($this->getAbsolutePath() . "/" . $file_name);
    }
}
