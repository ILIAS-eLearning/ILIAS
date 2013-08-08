<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "Services/Object/classes/class.ilObject2.php";

/**
 * Portfolio base
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
abstract class ilObjPortfolioBase extends ilObject2
{
	protected $online; // [bool]
	protected $comments; // [bool]
	protected $bg_color; // [string]
	protected $font_color; // [string]
	protected $img; // [string]
	protected $ppic; // [string]
	
	//
	// PROPERTIES
	//

	/**
	 * Set online status
	 *
	 * @param bool $a_value
	 */
	function setOnline($a_value)
	{
		$this->online = (bool)$a_value;
	}

	/**
	 * Is online?
	 *
	 * @return bool
	 */
	function isOnline()
	{
		return $this->online;
	}
	
	/**
	 * Set public comments status
	 *
	 * @param bool $a_value
	 */
	function setPublicComments($a_value)
	{
		$this->comments = (bool)$a_value;
	}

	/**
	 * Active public comments?
	 *
	 * @return bool
	 */
	function hasPublicComments()
	{
		return $this->comments;
	}
	
	/**
	 * Get profile picture status
	 * 
	 * @return bool
	 */
	function hasProfilePicture()
	{
		return $this->ppic;
	}

	/**
	 * Toggle profile picture status
	 *
	 * @param bool $a_status
	 */
	function setProfilePicture($a_status)
	{
		$this->ppic = (bool)$a_status;
	}

	/**
	 * Get background color
	 * 
	 * @return string
	 */
	function getBackgroundColor()
	{
		if(!$this->bg_color)
		{
			$this->bg_color = "ffffff";
		}
		return $this->bg_color;
	}

	/**
	 * Set background color
	 *
	 * @param string $a_value
	 */
	function setBackgroundColor($a_value)
	{
		$this->bg_color = (string)$a_value;
	}
	
	/**
	 * Get font color
	 * 
	 * @return string
	 */
	function getFontColor()
	{
		if(!$this->font_color)
		{
			$this->font_color = "505050";
		}
		return $this->font_color;
	}

	/**
	 * Set font color
	 *
	 * @param string $a_value
	 */
	function setFontColor($a_value)
	{		
		$this->font_color = (string)$a_value;
	}
	
	/**
	 * Get banner image
	 * 
	 * @return string
	 */
	function getImage()
	{
		return $this->img;
	}

	/**
	 * Set banner image
	 *
	 * @param string $a_value
	 */
	function setImage($a_value)
	{		
		$this->img = (string)$a_value;
	}
	
	
	//
	// CRUD
	//
	
	protected function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM usr_portfolio".
				" WHERE id = ".$ilDB->quote($this->id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		
		$this->setOnline((bool)$row["is_online"]);
		$this->setPublicComments((bool)$row["comments"]);
		$this->setProfilePicture((bool)$row["ppic"]);		
		$this->setBackgroundColor($row["bg_color"]);
		$this->setFontColor($row["font_color"]);
		$this->setImage($row["img"]);
		
		$this->doReadCustom($row);
	}
	
	protected function doReadCustom(array $a_row)
	{
		
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO usr_portfolio (id,is_online)".
			" VALUES (".$ilDB->quote($this->id, "integer").",".
			$ilDB->quote(0, "integer").")");
	}
	
	protected function doUpdate()
	{
		global $ilDB;
		
		$fields = array(
			"is_online" => array("integer", $this->isOnline()),
			"comments" => array("integer", $this->hasPublicComments()),
			"ppic" => array("integer", $this->hasProfilePicture()),
			"bg_color" => array("text", $this->getBackgroundColor()),
			"font_color" => array("text", $this->getFontcolor()),
			"img" => array("text", $this->getImage())
		);
		$this->doUpdateCustom($fields);
				
		$ilDB->update("usr_portfolio", $fields,
			array("id"=>array("integer", $this->id)));
	}
	
	protected function doUpdateCustom(array &$a_fields)
	{
		
	}

	protected function doDelete()
	{
		global $ilDB;
		
		// delete pages
		include_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";
		$pages = ilPortfolioPage::getAllPages($this->id);
		foreach($pages as $page)
		{
			$page = $this->getPageInstance($page["id"]);
			$page->delete();
		}
		
		$this->deleteImage();

		$ilDB->manipulate("DELETE FROM usr_portfolio".
			" WHERE id = ".$ilDB->quote($this->id, "integer"));
	}
	
	
	//
	// IMAGES
	//	
	
	/**
	 * Get banner image incl. path
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
			include_once "Modules/Portfolio/classes/class.ilFSStoragePortfolio.php";
			$storage = new ilFSStoragePortfolio($this->id);
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
		include_once "Modules/Portfolio/classes/class.ilFSStoragePortfolio.php";
		$storage = new ilFSStoragePortfolio($a_id);
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
		
		// #10074
		$clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $a_upload["name"]);
	
		$path = $this->initStorage($this->id);
		$original = "org_".$this->id."_".$clean_name;
		$thumb = "thb_".$this->id."_".$clean_name;
		$processed = $this->id."_".$clean_name;
		
		if(@move_uploaded_file($a_upload["tmp_name"], $path.$original))
		{
			chmod($path.$original, 0770);
			
			$prfa_set = new ilSetting("prfa");	
			$dimensions = $prfa_set->get("banner_width")."x".
				$prfa_set->get("banner_height");

			// take quality 100 to avoid jpeg artefacts when uploading jpeg files
			// taking only frame [0] to avoid problems with animated gifs
			$original_file = ilUtil::escapeShellArg($path.$original);
			$thumb_file = ilUtil::escapeShellArg($path.$thumb);
			$processed_file = ilUtil::escapeShellArg($path.$processed);
			ilUtil::execConvert($original_file."[0] -geometry 100x100 -quality 100 JPEG:".$thumb_file);
			ilUtil::execConvert($original_file."[0] -geometry ".$dimensions."! -quality 100 JPEG:".$processed_file);
			
			$this->setImage($processed);
			
			return true;
		}
		return false;
	}
}

?>