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
	 * Sets the delivery file name.
	 *
	 * @param string The value.
	 */
	public function setDeliveryFilename($a_value)
	{
		$this->filename = $a_value;
	}
	
	/**
	 * Gets the delivery file name.
	 *
	 * @return string.
	 */
	public function getDeliveryFilename()
	{
		return $this->filename;
	}
	
	
	//
	// handler interface
	// 
	
	public function getTask()
	{
		return $this->task;
	}
	
	protected function setTask(ilBackgroundTask $a_task)
	{
		$this->task = $a_task;
	}			
	
	public function process()
	{					
		// create temporary file to download
		$tmpdir = $this->getTempFolderPath();
		ilUtil::makeDirParents($tmpdir);
		
		$current_step = $this->gatherFiles();
		
		// has been cancelled?
		if($this->task->getStatus() == ilBackgroundTask::STATUS_CANCELLING)
		{
			return $this->cancel();
		}
					
		// :TODO: create zip in several steps
		
		$this->task->setCurrentStep(++$current_step);
		$this->task->save();
		
		sleep(1);
		
		// create archive to download
		$tmpzipfile = $this->getTempZipFilePath();
		ilUtil::zip($tmpdir, $tmpzipfile, true);
		ilUtil::delDir($tmpdir);		
		
		// has been cancelled?
		if($this->task->getStatus() == ilBackgroundTask::STATUS_CANCELLING)
		{
			return $this->cancel();
		}
		
		$this->task->setStatus(ilBackgroundTask::STATUS_FINISHED);
		$this->task->save();
	}
	
	public function cancel()
	{					
		return $this->deleteTempFiles();		
	}
	
	public function finish()
	{
		global $ilCtrl;
		
		$this->deleteTempFiles(false);
		
		$ilCtrl->setParameterByClass("ilbackgroundtaskhub", "tid", $this->task->getId());
		$url = $ilCtrl->getLinkTargetByClass("ilbackgroundtaskhub", "deliver", "", false, false);	

		return array("redirect", $url);
	}		
	
	public function deliver()
	{					
		$tmpzipfile = $this->getTempZipFilePath();		
		$deliverFilename = ilUtil::getAsciiFilename($this->getDeliveryFilename()) . ".zip";
		ilUtil::deliverFile($tmpzipfile, $deliverFilename, '', false, true, false);		
	}
	
	
	//
	// zip handling
	//
	
	abstract protected function gatherFiles();	
	
	/**
	 * Deletes the temporary files and folders belonging to this download.
	 */
	protected function deleteTempFiles($a_delete_zip = true)
	{				
		$successful = true;
		
		// delete temp directory
		$tmp_folder = $this->getTempFolderPath();
		if (is_dir($tmp_folder))
		{
			ilUtil::delDir($tmp_folder);
			$successful &= !file_exists($tmp_folder);
		}
		
		if($a_delete_zip)
		{
			// delete temp zip file
			$tmp_file = $this->getTempZipFilePath();
			if (file_exists($tmp_file))
				$successful &= @unlink($tmp_file);
		}
		
		return $successful;
	}
	
	/**
	 * Gets the temporary folder path to copy the files and folders to.
	 *
	 * @return int The value.
	 */
	protected function getTempFolderPath()
	{
		return $this->getTempBasePath() . ".tmp";
	}
	
	/**
	 * Gets the full path of the temporary zip file that gets created.
	 *
	 * @return int The value.
	 */
	protected function getTempZipFilePath()
	{
		return $this->getTempBasePath() . ".zip";
	}
	
	/**
	 * Gets the temporary base path for all files and folders related to this download.
	 *
	 * @return int The value.
	 */
	protected function getTempBasePath()
	{
		return ilUtil::getDataDir() . "/temp/dl_" . $this->task->getId();
	}	
}