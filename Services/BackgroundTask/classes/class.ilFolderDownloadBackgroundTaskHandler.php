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
	protected $ref_ids = array(); // [array]
	
	protected static $initialized; // [boo]
	
	public static function getInstanceFromTask(ilBackgroundTask $a_task)
	{
		$obj = new self();
		$obj->setTask($a_task);
		
		$params = $a_task->getParams();
		$obj->setRefIds($params["ref_ids"]);
		
		$ref_id = $params["ref_ids"][0];		
		$obj->setDeliveryFilename(ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)));
		
		return $obj;
	}
		
	public static function isActive()
	{
		// :TODO:
		return true;
	}
	
	public static function getObjectListAction($a_ref_id)
	{
		if(!self::$initialized)
		{
			global $tpl, $ilCtrl;
			
			$url =  $ilCtrl->getLinkTargetByClass(array("ilobjfoldergui", "ilbackgroundtaskhub"), "", "", true, false);
			
			$tpl->addJavaScript("Services/BackgroundTask/js/BgTask.js");			
			$tpl->addOnLoadCode('il.BgTask.setAjax("'.$url.'");');
			
			include_once "Services/UIComponent/Modal/classes/class.ilModalGUI.php";
			ilModalGUI::initJS();
			
			self::$initialized = true;
		}
		
		return "il.BgTask.init('".get_class($this)."', ".$a_ref_id.");";
	}	
	
	/**
	 * Gets the involved reference ids.
	 *
	 * @return array The value.
	 */
	public function getRefIds()
	{
		return $this->ref_ids;
	}
	
	/**
	 * Sets the involved reference ids.
	 *
	 * @param array $a_val The new value.
	 */
	public function setRefIds($a_val)
	{
		$this->ref_ids = $a_val;
	}

	public function init($a_params = null)
	{
		global $lng, $ilUser;
		
		if($a_params)
		{
			$this->setRefIds(array((int)$a_params)); // :TODO: multi?
		}
		
		$file_count = $total_bytes = 0;
		$this->calculateRecursive($this->getRefIds(), $file_count, $total_bytes);
		
		$json = new stdClass();
		
		if(!$file_count)
		{
			$json->status = "fail";
			$json->message = $lng->txt("bgtask_empty_folder");
		}
		else
		{
			// check if below download size limit
			$size_limit_mb = $this->getDownloadSizeLimit() * 1024 * 1024;
			if ($size_limit_mb > 0 && $total_bytes > $size_limit_mb) 
			{
				$bytes_formatted = ilUtil::formatSize($size_limit_mb);
				
				$json->status = "fail";
				$json->message = sprintf($lng->txt("bgtask_download_too_large"), $bytes_formatted);		
			} 
			else 
			{						
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
							
				$json->task_id = $task->getId();	

				if ($file_count >= $this->getFileCountThreshold()
					|| $total_bytes >= $this->getTotalSizeThreshold() * 1024 * 1024) 
				{										
					// check for other tasks from same user
					$existing = ilBackgroundTask::getActiveByUserId($ilUser->getId());
					if(sizeof($existing))
					{
						$json->status = "block";						
					}
					else
					{
						$bytes_formatted = ilUtil::formatSize($total_bytes);

						$json->status = "bg";
						$json->title = sprintf($lng->txt("bgtask_download_long"), $file_count, $bytes_formatted);	
						$json->steps = $file_count+1;
					}
				}
				else
				{							
					$this->process();		
					
					$task->setStatus(ilBackgroundTask::STATUS_FINISHED);
					$task->save();
					
					$res = $this->finish();
					
					$json->status = "finished";
					
					// see ilBackgroundTaskHub::progress()
					$json->result_cmd = $res[0];
					$json->result = $res[1];
				}
			}
		}
		
		return $json;
	}
	
	/**
	 * Calculates the number and size of the files being downloaded recursively.
	 * 
	 * @param array $ref_ids The reference ids.
	 */
	protected function calculateRecursive($a_ref_ids, &$a_file_count, &$a_file_size)
	{
		global $tree;
		
		include_once("./Modules/File/classes/class.ilObjFileAccess.php");
						
		// calculate each selected object
		foreach ($a_ref_ids as $ref_id)
		{
			if (!$this->validateFile($ref_id))
			{
				continue;
			}
			
			// get object
			$obj_type = ilObject::_lookupType($ref_id, true);
			if ($obj_type == "fold")
			{
				// get child objects
				$subtree = $tree->getChildsByTypeFilter($ref_id, array("fold", "file"));
				if (count($subtree) > 0)
				{
					$child_ref_ids = array();
					foreach ($subtree as $child) 
					{
						$child_ref_ids[] = $child["ref_id"];
					}
					$this->calculateRecursive($child_ref_ids, $a_file_count, $a_file_size);
				}
			}
			else if ($obj_type == "file")
			{
				$a_file_size += ilObjFileAccess::_lookupFileSize(ilObject::_lookupObjId($ref_id));
				$a_file_count += 1;
			}
		}
	}
			
	protected function validateFile($ref_id)
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess("read", "", $ref_id))
		{
			return false;
		}

		if (ilObject::_isInTrash($ref_id))
		{
			return false;
		}
		
		return true;
	}
	
	
	protected function gatherFiles()
	{	
		$tmpdir = $this->getTempFolderPath();
		
		$current_step = 0;
		
		// copy each selected object
		foreach ($this->getRefIds() as $ref_id)
		{
			// has been cancelled: hurry up
			if($this->task->isToBeCancelled())
			{
				return;
			}		
			
			if (!$this->validateFile($ref_id))
			{
				continue;
			}
			
			// get object
			$object = ilObjectFactory::getInstanceByRefId($ref_id);
			$obj_type = $object->getType();
			
			if ($obj_type == "fold")
			{
				// copy folder to temp directory
				$this->recurseFolder($ref_id, $object->getTitle(), $tmpdir, $current_step);
			}
			else if ($obj_type == "file")
			{
				// copy file to temp directory
				$this->copyFile($object->getId(), $object->getTitle(), $tmpdir, $current_step);
			}
		}
		
		return $current_step;
	}
	
	
	/**
	 * Copies a folder and its files to the specified temporary directory.
	 * 
	 * @param int $ref_id The reference id of the folder to copy.
	 * @param string $title The title of the folder.
	 * @param string $tmpdir The directory to copy the files to.
	 */
	protected function recurseFolder($ref_id, $title, $tmpdir, &$current_step) 
	{
		global $tree;
		
		$tmpdir = $tmpdir . "/" . ilUtil::getASCIIFilename($title);
		ilUtil::makeDir($tmpdir);
		
		$subtree = $tree->getChildsByTypeFilter($ref_id, array("fold","file"));
		
		foreach ($subtree as $child) 
		{			
			// has been cancelled: hurry up
			if($this->task->isToBeCancelled())
			{
				return;
			}		
			
			if (!$this->validateFile($ref_id))
			{
				continue;
			}
			
			if ($child["type"] == "fold")
			{
				$this->recurseFolder($child["ref_id"], $child["title"], $tmpdir, $current_step);
			}
			else 
			{
				$this->copyFile($child["obj_id"], $child["title"], $tmpdir, $current_step);
			}
		}
	}
	
	/**
	 * Copies a file to the specified temporary directory.
	 * 
	 * @param int $obj_id The object id of the file to copy.
	 * @param string $title The title of the file.
	 * @param string $tmpdir The directory to copy the file to.
	 */
	protected function copyFile($obj_id, $title, $tmpdir, &$current_step)
	{
		// :TODO: every file?
		$this->task->setCurrentStep(++$current_step);
		$this->task->save();	
				
		sleep(5);
		
		$newFilename = $tmpdir . "/" . ilUtil::getASCIIFilename($title);
		
		// copy to temporary directory
		include_once "Modules/File/classes/class.ilObjFile.php";
		$oldFilename = ilObjFile::_lookupAbsolutePath($obj_id);
		if (!copy($oldFilename, $newFilename))
		{
			throw new ilFileException("Could not copy ".$oldFilename." to ".$newFilename);
		}
		
		touch($newFilename, filectime($oldFilename));		
	}
	
	protected function getDownloadSizeLimit()
	{
		return 1;
		return 100000;
	}
	
	protected function getFileCountThreshold()
	{
		return 0;
		return 100;
	}
	
	protected function getTotalSizeThreshold() 
	{
		return 0;
		return 10000;
	}
}