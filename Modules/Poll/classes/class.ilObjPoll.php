<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";
require_once "Services/Object/classes/class.ilObjectActivation.php";

/**
* Class ilObjPoll
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjPoll extends ilObject2
{
	protected $online; // [bool]
	protected $access_type; // [int]
	protected $access_begin; // [timestamp]
	protected $access_end; // [timestamp]
	protected $question; // [string]
	protected $image; // [string]
	protected $view_results; // [int]
	
	const VIEW_RESULTS_ALWAYS = 1;
	const VIEW_RESULTS_NEVER = 2;
	const VIEW_RESULTS_AFTER_VOTE = 3;
	const VIEW_RESULTS_AFTER_PERIOD = 4; // ???
	
	function __construct($a_id = 0, $a_reference = true) 
	{
		// default
		$this->setOnline(false);
		$this->setViewResults(self::VIEW_RESULTS_AFTER_VOTE);
		$this->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
		
		parent::__construct($a_id, $a_reference);			
	}
	
	function initType()
	{
		$this->type = "poll";
	}
	
	function setOnline($a_value)
	{
		$this->online = (bool)$a_value;
	}
	
	function isOnline()
	{
		return $this->online;
	}
	
	function setAccessType($a_value)
	{
		$this->access_type = (int)$a_value;
	}
	
	function getAccessType()
	{
		return $this->access_type;
	}
	
	function setAccessBegin($a_value)
	{
		$this->access_begin = (int)$a_value;
	}
	
	function getAccessBegin()
	{
		return $this->access_begin;
	}
	
	function setAccessEnd($a_value)
	{
		$this->access_end = (int)$a_value;
	}
	
	function getAccessEnd()
	{
		return $this->access_end;
	}
	
	function setQuestion($a_value)
	{
		$this->question = (string)$a_value;
	}
	
	function getQuestion()
	{
		return $this->question;
	}
	
	function setImage($a_value)
	{
		$this->image = (string)$a_value;
	}
	
	function getImage()
	{
		return $this->image;
	}
	
	function setViewResults($a_value)
	{
		$this->view_results = (int)$a_value;
	}
	
	function getViewResults()
	{
		return $this->view_results;
	}

	protected function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM il_poll".
				" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$row = $ilDB->fetchAssoc($set);
		$this->setQuestion($row["question"]);
		$this->setImage($row["image"]);
		$this->setOnline($row["online_status"]);
		$this->setViewResults($row["view_results"]);
		
		if($this->ref_id)
		{
			$activation = ilObjectActivation::getItem($this->ref_id);			
			$this->setAccessType($activation["timing_type"]);
			$this->setAccessBegin($activation["timing_begin"]);
			$this->setAccessEnd($activation["timing_end"]);							
		}
	}
	
	protected function propertiesToDB()
	{
		$fields = array(
			"question" => array("text", $this->getQuestion()),
			"image" => array("text", $this->getImage()),
			"online_status" => array("integer", $this->isOnline()),
			"view_results" => array("integer", $this->getViewResults())
		);
		
		return $fields;
	}

	protected function doCreate()
	{
		global $ilDB;
		
		if($this->getId())
		{
			$fields = $this->propertiesToDB();
			$fields["id"] = array("integer", $this->getId());

			$ilDB->insert("il_poll", $fields);
			
			
			// object activation default entry will be created on demand
		}
	}
		
	protected function doUpdate()
	{
		global $ilDB;
	
		if($this->getId())
		{
			$fields = $this->propertiesToDB();
			
			$ilDB->update("il_poll", $fields,
				array("id"=>array("integer", $this->getId())));
			
			
			if($this->ref_id)
			{
				$activation = new ilObjectActivation();
				$activation->setTimingType($this->getAccessType());
				$activation->setTimingStart($this->getAcessBegin());
				$activation->setTimingEnd($this->getAcessEnd());
				$activation->update($this->ref_id);
			}
			
		}
	}

	
	protected function doDelete()
	{
		global $ilDB;
		
		if($this->getId())
		{		
			$this->deleteImage();
			
			if($this->ref_id)
			{
				ilObjectActivation::deleteAllEntries($this->ref_id);
			}
			
			$ilDB->manipulate("DELETE FROM il_poll".
				" WHERE id = ".$ilDB->quote($this->id, "integer"));
		}
	}
	
	
	
	//
	// image
	// 
	
	/**
	 * Get image incl. path
	 *
	 * @param bool $a_as_thumb
	 */
	function getImageFullPath($a_as_thumb = false)
	{		
		if($this->img)
		{
			$path = $this->initStorage($this->id);
			if(!$a_as_thumb)
			{
				return $path.$this->img;
			}
			else
			{
				return $path."thb_".$this->img;
			}
		}
	}
	
	/**
	 * remove existing file
	 */
	public function deleteImage()
	{
		if($this->id)
		{
			include_once "Modules/Poll/classes/class.ilFSStoragePoll.php";
			$storage = new ilFSStoragePoll($this->id);
			$storage->delete();
			
			$this->setImage(null);
		}
	}

	/**
	 * Init file system storage
	 * 
	 * @param type $a_id
	 * @param type $a_subdir
	 * @return string 
	 */
	public static function initStorage($a_id, $a_subdir = null)
	{		
		include_once "Modules/Poll/classes/class.ilFSStoragePoll.php";
		$storage = new ilFSStoragePoll($a_id);
		$storage->create();
		
		$path = $storage->getAbsolutePath()."/";
		
		if($a_subdir)
		{
			$path .= $a_subdir."/";
			
			if(!is_dir($path))
			{
				mkdir($path);
			}
		}
				
		return $path;
	}
	
	/**
	 * Upload new image file
	 * 
	 * @param array $a_upload
	 * @return bool
	 */
	function uploadImage(array $a_upload)
	{
		if(!$this->id)
		{
			return false;
		}
		
		$this->deleteImage();
	
		$path = $this->initStorage($this->id);
		$original = "org_".$this->id."_".$a_upload["name"];
		$thumb = "thb_".$this->id."_".$a_upload["name"];
		$processed = $this->id."_".$a_upload["name"];
		
		if(@move_uploaded_file($a_upload["tmp_name"], $path.$original))
		{
			chmod($path.$original, 0770);

			// take quality 100 to avoid jpeg artefacts when uploading jpeg files
			// taking only frame [0] to avoid problems with animated gifs
			$original_file = ilUtil::escapeShellArg($path.$original);
			$thumb_file = ilUtil::escapeShellArg($path.$thumb);
			$processed_file = ilUtil::escapeShellArg($path.$processed);
			ilUtil::execConvert($original_file."[0] -geometry 100x100 -quality 100 JPEG:".$thumb_file);
			ilUtil::execConvert($original_file."[0] -geometry ".self::getImageSize()."! -quality 100 JPEG:".$processed_file);
			
			$this->setImage($processed);
			return true;
		}
		return false;
	}	
	
	public static function getImageSize()
	{
		// :TODO:
		return "300x300";
	}
}

?>