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
 
/**
 * @author Jesús López <lopez@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFSWebStorageExercise extends ilFileSystemAbstractionStorage
{
    protected ilLogger $log;
    protected int $ass_id;
    protected string $submissions_path;

    public function __construct(
        int $a_container_id = 0,
        int $a_ass_id = 0
    ) {
        $this->ass_id = $a_ass_id;
        $this->log = ilLoggerFactory::getLogger("exc");
        $this->log->debug("ilFSWebStorageExercise construct with a_container_id = " . $a_container_id . " and ass_id =" . $a_ass_id);
        parent::__construct(self::STORAGE_WEB, true, $a_container_id);
    }

    protected function init() : bool
    {
        if (parent::init()) {
            if ($this->ass_id > 0) {
                $this->submissions_path = $this->path . "/subm_" . $this->ass_id;

                $this->log->debug("parent init() with ass_id =" . $this->ass_id);
                $this->path .= "/ass_" . $this->ass_id;
            }
        } else {
            $this->log->debug("no parent init() without ass_id");
            return false;
        }
        return true;
    }

    protected function getPathPostfix() : string
    {
        return 'exc';
    }

    protected function getPathPrefix() : string
    {
        return 'ilExercise';
    }


    public function deleteUserSubmissionDirectory(
        int $user_id
    ) : void {
        $internal_dir = $this->submissions_path . "/" . $user_id;

        //remove first dot from (./data/client/ilExercise/3/exc_318/subm_21/6)
        $internal_dir_without_dot = substr($internal_dir, 1);

        $absolute_path = ILIAS_ABSOLUTE_PATH . $internal_dir_without_dot;

        if (is_dir($absolute_path)) {
            parent::deleteDirectory($absolute_path);
            $this->log->debug("Removed = " . $absolute_path);
        }
    }

    /**
     * Get assignment files
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getFiles() : array
    {
        $ass = new ilExAssignment($this->ass_id);
        $files_order = $ass->getInstructionFilesOrder();

        $files = array();
        if (!is_dir($this->path)) {
            return $files;
        }

        $dp = opendir($this->path);
        while ($file = readdir($dp)) {
            if (!is_dir($this->path . '/' . $file)) {
                $files[] = array(
                    'name' => $file,
                    'size' => filesize($this->path . '/' . $file),
                    'ctime' => filectime($this->path . '/' . $file),
                    'fullpath' => $this->path . '/' . $file,
                    'order' => $files_order[$file]["order_nr"] ?? 0
                    );
            }
        }
        closedir($dp);
        return ilArrayUtil::sortArray($files, "order", "asc", true);
    }

    public function getAssignmentFilePath(
        string $a_file
    ) : string {
        return $this->getAbsolutePath() . "/" . $a_file;
    }

    /**
     * @throws ilException
     */
    public function uploadAssignmentFiles(
        array $a_files
    ) : void {
        if (is_array($a_files["name"])) {
            foreach ($a_files["name"] as $k => $name) {
                if ($name != "") {
                    $tmp_name = $a_files["tmp_name"][$k];
                    ilFileUtils::moveUploadedFile(
                        $tmp_name,
                        basename($name),
                        $this->path . DIRECTORY_SEPARATOR . basename($name),
                        false
                    );
                }
            }
        }
    }
}
