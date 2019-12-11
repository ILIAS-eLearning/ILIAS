<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/FileSystem/classes/class.ilFileSystemStorage.php');
/**
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilFSWebStorageExercise extends ilFileSystemStorage
{
    protected $log;
    protected $ass_id;

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

    /**
     * Get assignment files
     */
    public function getFiles()
    {
        require_once "./Modules/Exercise/classes/class.ilExAssignment.php";

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
