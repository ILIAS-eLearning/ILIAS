<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/BackgroundTask/classes/class.ilZipBackgroundTaskHandler.php";

/**
 * Background task handler for folder downloads
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesBackgroundTask
 */
class ilFolderDownloadBackgroundTaskHandler extends ilZipBackgroundTaskHandler
{
    protected $settings; // [ilSetting]
    protected $ref_ids = array(); // [array]
    
    protected static $initialized; // [bool]
    
    //
    // constructor
    //
    
    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        parent::__construct();
        $this->settings = new ilSetting("fold");
    }
    
    public static function getInstanceFromTask(ilBackgroundTask $a_task)
    {
        global $DIC;
        $tree = $DIC['tree'];
        
        $obj = new self();
        $obj->setTask($a_task);
        
        $params = $a_task->getParams();
        $obj->setRefIds($params["ref_ids"]);
        
        $ref_id = (sizeof($params["ref_ids"]) == 1)
            ? $params["ref_ids"][0]
            : $tree->getParentId($params["ref_ids"][0]);
        $obj->setDeliveryFilename(ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)));
        
        return $obj;
    }
    
    
    //
    // setter/getter/status
    //
        
    /**
     * Is folder background download active?
     *
     * @return boolean
     */
    public static function isActive()
    {
        $settings = new ilSetting("fold");
        return (bool) $settings->get("bgtask_download", false);
    }
    
    /**
     * Gets the involved reference ids.
     *
     * @return array
     */
    public function getRefIds()
    {
        return $this->ref_ids;
    }
    
    /**
     * Sets the involved reference ids
     *
     * @param array $a_val
     */
    public function setRefIds($a_val)
    {
        $this->ref_ids = $a_val;
    }

    
    //
    // gui integration
    //
    
    /**
     * Get object list action
     *
     * @see ilObjectListGUI::insertCommand()
     * @param int $a_ref_id
     * @return string
     */
    public static function getObjectListAction($a_ref_id)
    {
        self::initObjectListAction();

        return "il.BgTask.init('" . static::class . "', " . $a_ref_id . ");";
    }


    /**
     * init js for background download
     */
    public static function initObjectListAction()
    {
        // js init only needed once per request
        if (!self::$initialized) {
            global $DIC;
            $tpl = $DIC['tpl'];
            $ilCtrl = $DIC['ilCtrl'];

            $url =  $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjfoldergui", "ilbackgroundtaskhub"), "", "", true, false);

            $tpl->addJavaScript("Services/BackgroundTask/js/BgTask.js");
            $tpl->addOnLoadCode('il.BgTask.setAjax("' . $url . '");');

            // enable modals from js
            include_once "Services/UIComponent/Modal/classes/class.ilModalGUI.php";
            ilModalGUI::initJS();

            self::$initialized = true;
        }
    }
    
    
    //
    // handler interface
    //
    
    public function init($a_params = null)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        
        if ($a_params) {
            $this->setRefIds(explode(",", $a_params));
        }
        
        $file_count = $total_bytes = 0;
        $this->calculateRecursive($this->getRefIds(), $file_count, $total_bytes);
        
        include_once "Services/BackgroundTask/classes/class.ilBackgroundTaskJson.php";
        
        // empty folder - nothing to do
        if (!$file_count) {
            $json = ilBackgroundTaskJson::getFailedJson($lng->txt("bgtask_empty_folder"));
        } else {
            // check if below download size limit
            $size_limit_mb = $this->getDownloadSizeLimit() * 1024 * 1024;
            if ($size_limit_mb > 0 && $total_bytes > $size_limit_mb) {
                $json = ilBackgroundTaskJson::getFailedJson(sprintf($lng->txt("bgtask_download_too_large"), ilUtil::formatSize($size_limit_mb)));
            } else {
                // set up task instance
                include_once "Services/BackgroundTask/classes/class.ilBackgroundTask.php";
                $task = new ilBackgroundTask();
                $task->setHandlerId(get_class($this));
                $task->setUserId($ilUser->getId());
                $task->setParams(array(
                    "ref_ids" => $this->getRefIds()
                ));
                $task->setSteps($file_count+1); // +1 = create zip
                $task->setStatus(ilBackgroundTask::STATUS_INITIALIZED);
                $task->save();
                
                $this->setTask($task);
                
                // above thresholds: do background task
                if ($file_count >= $this->getFileCountThreshold()
                    || $total_bytes >= $this->getTotalSizeThreshold() * 1024 * 1024) {
                    // check for other tasks from same user
                    $existing = ilBackgroundTask::getActiveByUserId($ilUser->getId());
                    if (sizeof($existing)) {
                        $json = ilBackgroundTaskJson::getBlockedJson($task->getId());
                    } else {
                        $json = ilBackgroundTaskJson::getProcessingJson(
                            $task->getId(),
                            sprintf($lng->txt("bgtask_download_long"), $file_count, ilUtil::formatSize($total_bytes)),
                            $file_count+1
                        );
                    }
                }
                // below thresholds: direct download
                else {
                    $this->process();
                    
                    $task->setStatus(ilBackgroundTask::STATUS_FINISHED);
                    $task->save();
                    
                    $res = $this->finish();
                    
                    // see ilBackgroundTaskHub::progress()
                    $json = ilBackgroundTaskJson::getFinishedJson($task->getId(), $res[0], $res[1]);
                }
            }
        }
        
        return $json;
    }
            
    protected function gatherFiles()
    {
        $tmpdir = $this->getTempFolderPath();
        
        $current_step = 0;
                
        // parse folders
        foreach ($this->getRefIds() as $ref_id) {
            // has been cancelled: hurry up
            if ($this->task->isToBeCancelled()) {
                return;
            }
            
            if (!$this->validateAccess($ref_id)) {
                continue;
            }
            
            $object = ilObjectFactory::getInstanceByRefId($ref_id);
            switch ($object->getType()) {
                case "fold":
                    $this->recurseFolder($ref_id, $object->getTitle(), $tmpdir, $current_step);
                    break;
                                
                case "file":
                    $this->copyFile($object->getId(), $object->getTitle(), $tmpdir, $current_step);
                    break;
            }
        }
        
        return $current_step;
    }
            
    
    //
    // processing
    //
    
    /**
     * Calculates the number and size of the files being downloaded recursively.
     *
     * @param array $a_ref_ids
     * @param int &$a_file_count
     * @param int &$a_file_size
     */
    protected function calculateRecursive($a_ref_ids, &$a_file_count, &$a_file_size)
    {
        global $DIC;
        $tree = $DIC['tree'];
        
        include_once("./Modules/File/classes/class.ilObjFileAccess.php");
                        
        // parse folders
        foreach ($a_ref_ids as $ref_id) {
            if (!$this->validateAccess($ref_id)) {
                continue;
            }
            
            // we are only interested in folders and files
            switch (ilObject::_lookupType($ref_id, true)) {
                case "fold":
                    // get child objects
                    $subtree = $tree->getChildsByTypeFilter($ref_id, array("fold", "file"));
                    if (count($subtree) > 0) {
                        $child_ref_ids = array();
                        foreach ($subtree as $child) {
                            $child_ref_ids[] = $child["ref_id"];
                        }
                        $this->calculateRecursive($child_ref_ids, $a_file_count, $a_file_size);
                    }
                    break;
                    
                case "file":
                    $a_file_size += ilObjFileAccess::_lookupFileSize(ilObject::_lookupObjId($ref_id));
                    $a_file_count += 1;
                    break;
            }
        }
    }
    
    /**
     * Copies a folder and its files to the specified temporary directory.
     *
     * @param int $a_ref_id
     * @param string $a_title
     * @param string $a_tmpdir
     * @param int &$a_current_step
     */
    protected function recurseFolder($a_ref_id, $a_title, $a_tmpdir, &$a_current_step)
    {
        global $DIC;
        $tree = $DIC['tree'];
        
        $tmpdir = $a_tmpdir . "/" . ilUtil::getASCIIFilename($a_title);
        ilUtil::makeDir($tmpdir);
        
        $subtree = $tree->getChildsByTypeFilter($a_ref_id, array("fold", "file"));
        foreach ($subtree as $child) {
            // has been cancelled: hurry up
            if ($this->task->isToBeCancelled()) {
                return;
            }
            
            if (!$this->validateAccess($child["ref_id"])) {
                continue;
            }
            
            switch ($child["type"]) {
                case "fold":
                    $this->recurseFolder($child["ref_id"], $child["title"], $tmpdir, $a_current_step);
                    break;
                
                case "file":
                    $this->copyFile($child["obj_id"], $child["title"], $tmpdir, $a_current_step);
                    break;
            }
        }
    }
    
    /**
     * Copies a file to the specified temporary directory.
     *
     * @param int $a_obj_id
     * @param string $a_title
     * @param string $a_tmpdir
     * @param int &$a_current_step
     */
    protected function copyFile($a_obj_id, $a_title, $a_tmpdir, &$a_current_step)
    {
        // :TODO: every file?
        $this->task->setCurrentStep(++$a_current_step);
        $this->task->save();
        
        $new_filename = $a_tmpdir . "/" . ilUtil::getASCIIFilename($a_title);
        
        // copy to temporary directory
        include_once "Modules/File/classes/class.ilObjFile.php";
        $old_filename = ilObjFile::_lookupAbsolutePath($a_obj_id);
        if (!copy($old_filename, $new_filename)) {
            throw new ilFileException("Could not copy " . $old_filename . " to " . $new_filename);
        }
        
        touch($new_filename, filectime($old_filename));
    }
            
    /**
     * Check file access
     *
     * @param int $ref_id
     * @return boolean
     */
    protected function validateAccess($ref_id)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        
        if (!$ilAccess->checkAccess("read", "", $ref_id)) {
            return false;
        }

        if (ilObject::_isInTrash($ref_id)) {
            return false;
        }
        
        return true;
    }
    
    
    //
    // settings
    //
    
    /**
     * Get overall download size limit
     *
     * @return int
     */
    protected function getDownloadSizeLimit()
    {
        return (int) $this->settings->get("bgtask_download_limit", 0);
    }
    
    /**
     * Get file count threshold
     *
     * @return int
     */
    protected function getFileCountThreshold()
    {
        return (int) $this->settings->get("bgtask_download_tcount", 0);
    }
    
    /**
     * Get total size threshold
     *
     * @return int
     */
    protected function getTotalSizeThreshold()
    {
        return (int) $this->settings->get("bgtask_download_tsize", 0);
    }
}
