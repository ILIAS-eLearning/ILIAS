<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/BackgroundTask/interfaces/interface.ilBackgroundTaskHandler.php";

/**
 * Background task handler for zip creation
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesBackgroundTask
 */
abstract class ilZipBackgroundTaskHandler implements ilBackgroundTaskHandler
{
    protected $task; // [ilBackgroundTask]
    protected $filename; // [string]

    /**
     * @var ilLogger
     */
    private $log;

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $this->log = ilLoggerFactory::getLogger('btsk');
        $this->settings = new ilSetting("fold");
    }

    //
    // setter/getter
    //
            
    /**
     * Sets the delivery file name
     *
     * @param string
     */
    public function setDeliveryFilename($a_value)
    {
        $this->filename = $a_value;
    }
    
    /**
     * Gets the delivery file name
     *
     * @return string
     */
    public function getDeliveryFilename()
    {
        return $this->filename;
    }
    
    /**
     * Set current task instance
     *
     * @param ilBackgroundTask $a_task
     */
    protected function setTask(ilBackgroundTask $a_task)
    {
        $this->task = $a_task;
    }
    
    
    //
    // handler interface
    //
    
    public function getTask()
    {
        return $this->task;
    }
    
    public function process()
    {
        $this->log->debug("start");
        // create temporary file to download
        $tmpdir = $this->getTempFolderPath();
        ilUtil::makeDirParents($tmpdir);
        
        // gather all files
        $current_step = $this->gatherFiles();
        
        // has been cancelled?
        if ($this->task->isToBeCancelled()) {
            return $this->cancel();
        }
                    
        // :TODO: create zip in several steps
        
        $this->task->setCurrentStep(++$current_step);
        $this->task->save();
        
        // create archive to download
        $tmpzipfile = $this->getTempZipFilePath();
        ilUtil::zip($tmpdir, $tmpzipfile, true);
        ilUtil::delDir($tmpdir);
        
        // has been cancelled?
        if ($this->task->isToBeCancelled()) {
            return $this->cancel();
        }
        
        $this->task->setStatus(ilBackgroundTask::STATUS_FINISHED);
        $this->task->save();
        $this->log->debug("end");
    }
    
    /**
     * Cancel download
     *
     * @return boolean
     */
    public function cancel()
    {
        $this->log->debug("");
        $this->deleteTempFiles();
        
        $this->task->setStatus(ilBackgroundTask::STATUS_CANCELLED);
        $this->task->save();
        
        return true;
    }
    
    /**
     * Finish download
     */
    public function finish()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->deleteTempFiles(false);
        
        $ilCtrl->setParameterByClass("ilbackgroundtaskhub", "tid", $this->task->getId());
        $url = $ilCtrl->getLinkTargetByClass("ilbackgroundtaskhub", "deliver", "", false, false);

        return array("redirect", $url);
    }
    
    /**
     * Deliver file
     */
    public function deliver()
    {
        $tmpzipfile = $this->getTempZipFilePath();
        $deliverFilename = ilUtil::getAsciiFilename($this->getDeliveryFilename()) . ".zip";
        ilUtil::deliverFile($tmpzipfile, $deliverFilename, '', false, true, false);
    }
    
    public function deleteTaskAndFiles()
    {
        if (!$this->task) {
            return;
        }
        
        $this->deleteTempFiles();
        
        $this->task->delete();
        unset($this->task);
    }
    
    
    //
    // zip handling
    //
    
    /**
     * Copy files to target directory
     *
     * @return int current step
     */
    abstract protected function gatherFiles();
    
    /**
     * Deletes the temporary files and folders belonging to this download
     *
     * @param bool $a_delete_zip
     */
    protected function deleteTempFiles($a_delete_zip = true)
    {
        $successful = true;
        
        // delete temp directory
        $tmp_folder = $this->getTempFolderPath();
        if (is_dir($tmp_folder)) {
            ilUtil::delDir($tmp_folder);
            $successful = !file_exists($tmp_folder);
        }
        
        if ($a_delete_zip) {
            // delete temp zip file
            $tmp_file = $this->getTempZipFilePath();
            if (file_exists($tmp_file)) {
                $successful = @unlink($tmp_file);
            }
        }
        
        return $successful;
    }
    
    
    //
    // temp directories
    //
    
    /**
     * Gets the temporary folder path to copy the files and folders to
     *
     * @return int
     */
    protected function getTempFolderPath()
    {
        return $this->getTempBasePath() . ".tmp";
    }
    
    /**
     * Gets the full path of the temporary zip file that gets created
     *
     * @return int
     */
    protected function getTempZipFilePath()
    {
        return $this->getTempBasePath() . ".zip";
    }
    
    /**
     * Gets the temporary base path for all files and folders related to this download
     *
     * @return int
     */
    protected function getTempBasePath()
    {
        return ilUtil::getDataDir() . "/temp/dl_" . $this->task->getId();
    }
}
