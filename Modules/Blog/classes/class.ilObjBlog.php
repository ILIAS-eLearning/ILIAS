<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
* Class ilObjBlog
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjBlog extends ilObject2
{
	protected $notes; // [bool]
	protected $bg_color; // [string]
	protected $font_color; // [string]
	protected $img; // [string]
	
	function initType()
	{
		$this->type = "blog";
	}

	protected function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM il_blog".
				" WHERE id = ".$ilDB->quote($this->id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		$this->setNotesStatus($row["notes"]);
		$this->setBackgroundColor($row["bg_color"]);
		$this->setFontColor($row["font_color"]);
		$this->setImage($row["img"]);
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO il_blog (id,notes) VALUES (".
			$ilDB->quote($this->id, "integer").",".
			$ilDB->quote(true, "integer").")");
	}
	
	protected function doDelete()
	{
		global $ilDB;
		
		$this->deleteImage();

		include_once "Modules/Blog/classes/class.ilBlogPosting.php";
		ilBlogPosting::deleteAllBlogPostings($this->id);

		$ilDB->manipulate("DELETE FROM il_blog".
			" WHERE id = ".$ilDB->quote($this->id, "integer"));
	}
	
	protected function doUpdate()
	{
		global $ilDB;
	
		if($this->id)
		{
			$ilDB->manipulate("UPDATE il_blog".
					" SET notes = ".$ilDB->quote($this->getNotesStatus(), "integer").
					",bg_color = ".$ilDB->quote($this->getBackgroundColor(), "text").
					",font_color = ".$ilDB->quote($this->getFontcolor(), "text").
					",img = ".$ilDB->quote($this->getImage(), "text").
					" WHERE id = ".$ilDB->quote($this->id, "integer"));
		}
	}

	/**
	 * Get notes status
	 * 
	 * @return bool
	 */
	function getNotesStatus()
	{
		return $this->notes;
	}

	/**
	 * Toggle notes status
	 *
	 * @param bool $a_status
	 */
	function setNotesStatus($a_status)
	{
		$this->notes = (bool)$a_status;
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
			include_once "Modules/Blog/classes/class.ilFSStorageBlog.php";
			$storage = new ilFSStorageBlog($this->id);
			$storage->delete();
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
		include_once "Modules/Blog/classes/class.ilFSStorageBlog.php";
		$storage = new ilFSStorageBlog($a_id);
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
			ilUtil::execConvert($original_file."[0] -geometry 880x200! -quality 100 JPEG:".$processed_file);
			
			$this->setImage($processed);
			return true;
		}
		return false;
	}
}

?>