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
	protected $ppic; // [string]
	
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
		$this->setNotesStatus((bool)$row["notes"]);
		$this->setProfilePicture((bool)$row["ppic"]);
		$this->setBackgroundColor($row["bg_color"]);
		$this->setFontColor($row["font_color"]);
		$this->setImage($row["img"]);
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO il_blog (id,notes,ppic) VALUES (".
			$ilDB->quote($this->id, "integer").",".
			$ilDB->quote(true, "integer").",".
			$ilDB->quote(true, "integer").")");
	}
	
	protected function doDelete()
	{
		global $ilDB;
		
		$this->deleteImage();

		include_once "Modules/Blog/classes/class.ilBlogPosting.php";
		ilBlogPosting::deleteAllBlogPostings($this->id);
		
		// remove all notifications
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::removeForObject(ilNotification::TYPE_BLOG, $this->id);

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
					",ppic = ".$ilDB->quote($this->hasProfilePicture(), "integer").
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

			$blga_set = new ilSetting("blga");	
			$dimensions = $blga_set->get("banner_width")."x".
				$blga_set->get("banner_height");
			
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
	
	static function sendNotification($a_action, $a_blog_wsp_id, $a_posting_id)
	{
		global $ilUser;
		
		// get blog object id
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
		$blog_obj_id = $tree->lookupObjectId($a_blog_wsp_id);		
		if(!$blog_obj_id)
		{
			return;
		}
		unset($tree);
	

		// recipients
		include_once "./Services/Notification/classes/class.ilNotification.php";		
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_BLOG, 
			$a_blog_wsp_id, $a_posting_id, ($a_action == "comment"));
		if(!sizeof($users))
		{
			return;
		}
		
		ilNotification::updateNotificationTime(ilNotification::TYPE_BLOG, $a_blog_wsp_id, $users);
		
		
		// prepare mail content
		
		include_once "./Modules/Blog/classes/class.ilBlogPosting.php";
		$posting = new ilBlogPosting($a_posting_id);
		$posting_title = $posting->getTitle();		
		$blog_title = ilObject::_lookupTitle($blog_obj_id);
				
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
		$link = ilWorkspaceAccessHandler::getGotoLink($a_blog_wsp_id, $blog_obj_id, "_".$a_posting_id);
		
		
		// send mails
		
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";
		include_once("./Services/User/classes/class.ilUserUtil.php");
		
		$owner = ilObject::_lookupOwner($blog_obj_id);
		
		foreach(array_unique($users) as $idx => $user_id)
		{
			// the blog owner should only get comments notifications
			if($a_action != "comment" && $user_id == $owner)
			{
				continue;
			}
			
			// the user responsible for the action should not be notified
			if($user_id != $ilUser->getId())
			{
				// use language of recipient to compose message
				$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
				$ulng->loadLanguageModule('blog');

				$subject = sprintf($ulng->txt('blog_change_notification_subject'), $blog_title);
				$message = sprintf($ulng->txt('blog_change_notification_salutation'), ilObjUser::_lookupFullname($user_id))."\n\n";

				$message .= $ulng->txt('blog_change_notification_body_'.$a_action).":\n\n";
				$message .= $ulng->txt('obj_blog').": ".$blog_title."\n";
				$message .= $ulng->txt('blog_posting').": ".$posting_title."\n";
				$message .= $ulng->txt('blog_changed_by').": ".ilUserUtil::getNamePresentation($ilUser->getId())."\n\n";
				$message .= $ulng->txt('blog_change_notification_link').": ".$link;				

				$mail_obj = new ilMail(ANONYMOUS_USER_ID);
				$mail_obj->appendInstallationSignature(true);
				$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id),
					"", "", $subject, $message, array(), array("system"));
			}
			else
			{
				unset($users[$idx]);
			}
		}
	}
}

?>