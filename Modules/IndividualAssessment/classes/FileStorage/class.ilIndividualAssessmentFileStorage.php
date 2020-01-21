<?php
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
     * @return boolen
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
            if ($file !="." && $file !=".." && !is_dir($this->getAbsolutePath() . "/" . $file)) {
                $files[] = $file;
            }
        }
        closedir($fh);

        return $files;
    }

    /**
     * Upload the file
     *
     * @param string[]
     *
     * @return boolen
     */
    public function uploadFile($file)
    {
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file["name"]);
        $new_file = $path . "/" . $clean_name;

        ilUtil::moveUploadedFile(
            $file["tmp_name"],
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
        $files = $this->readDir();
        $this->deleteFile($this->getAbsolutePath() . "/" . $files[0]);
    }

    /**
     * Get the path of file
     *
     * @return sgtring
     */
    public function getFilePath()
    {
        $files = $this->readDir();
        return $this->getAbsolutePath() . "/" . $files[0];
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
