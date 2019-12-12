<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Jesús López <lopez@leifos.com>
 *
 * @ingroup ModulesExercise
 */
class ilFSWebStorageExercise extends ilFileSystemStorage
{
    protected $log;
    protected $ass_id;
    protected $submissions_path;

    /**
     * Constructor
     *
     * @param int assingment id
     */
    public function __construct($a_container_id = 0, $a_ass_id = 0)
    {
        $this->ass_id = $a_ass_id;
        $this->log = ilLoggerFactory::getLogger("exc");
        $this->log->debug("ilFSWebStorageExercise construct with a_container_id = " . $a_container_id . " and ass_id =" . $a_ass_id);
        parent::__construct(self::STORAGE_WEB, true, $a_container_id);
    }

    /**
     * Append ass_<ass_id> to path (assignment id)
     */
    public function init()
    {
        if (parent::init()) {
            if ($this->ass_id > 0) {
                $this->submissions_path = $this->path . "/subm_" . $this->ass_id;

                $this->log->debug("parent init() with ass_id =" . $this->ass_id);
                $this->path.= "/ass_" . $this->ass_id;
            }
        } else {
            $this->log->debug("no parent init() without ass_id");
            return false;
        }
        return true;
    }


    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPostfix()
    {
        return 'exc';
    }

    /**
     * Implementation of abstract method
     *
     * @access protected
     *
     */
    protected function getPathPrefix()
    {
        return 'ilExercise';
    }

    /**
     * Create directory
     *
     * @access public
     *
     */
    public function create()
    {
        $this->log->debug("parent create");

        parent::create();

        return true;
    }

    public function deleteUserSubmissionDirectory(int $user_id) : void
    {
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
     */
    public function getFiles()
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
                    'name'     => $file,
                    'size'     => filesize($this->path . '/' . $file),
                    'ctime'    => filectime($this->path . '/' . $file),
                    'fullpath' => $this->path . '/' . $file,
                    'order'    => $files_order[$file]["order_nr"] ? $files_order[$file]["order_nr"] : 0
                    );
            }
        }
        closedir($dp);
        $files = ilUtil::sortArray($files, "order", "asc", true);
        return $files;
    }

    /**
     * Get path for assignment file
     */
    public function getAssignmentFilePath($a_file)
    {
        return $this->getAbsolutePath() . "/" . $a_file;
    }

    /**
     * Upload assignment files
     * (e.g. from assignment creation form)
     */

    public function uploadAssignmentFiles($a_files)
    {
        if (is_array($a_files["name"])) {
            foreach ($a_files["name"] as $k => $name) {
                if ($name != "") {
                    $type = $a_files["type"][$k];
                    $tmp_name = $a_files["tmp_name"][$k];
                    $size = $a_files["size"][$k];
                    ilUtil::moveUploadedFile(
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
