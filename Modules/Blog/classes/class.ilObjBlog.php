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
	protected $rss; // [bool]
	protected $approval; // [bool]
	
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
		$this->setRSS($row["rss_active"]);
		$this->setApproval($row["approval"]);
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO il_blog (id,notes,ppic,rss_active,approval) VALUES (".
			$ilDB->quote($this->id, "integer").",".
			$ilDB->quote(true, "integer").",".
			$ilDB->quote(true, "integer").",".
			$ilDB->quote(true, "integer").",".
			$ilDB->quote(false, "integer").")");
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
					",rss_active = ".$ilDB->quote($this->hasRSS(), "text").
					",approval = ".$ilDB->quote($this->hasApproval(), "integer").
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
		
		// #10074
		$clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $a_upload["name"]);
	
		$path = $this->initStorage($this->id);
		$original = "org_".$this->id."_".$clean_name;
		$thumb = "thb_".$this->id."_".$clean_name;
		$processed = $this->id."_".$clean_name;
		
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
		
	/**
	 * Get RSS status
	 * 
	 * @return bool
	 */
	function hasRSS()
	{
		return $this->rss;
	}

	/**
	 * Toggle RSS status
	 *
	 * @param bool $a_status
	 */
	function setRSS($a_status)
	{
		$this->rss = (bool)$a_status;
	}
	
	/**
	 * Get approval status
	 * 
	 * @return bool
	 */
	function hasApproval()
	{
		return (bool)$this->approval;
	}

	/**
	 * Toggle approval status
	 *
	 * @param bool $a_status
	 */
	function setApproval($a_status)
	{
		$this->approval = (bool)$a_status;
	}
	
	static function sendNotification($a_action, $a_in_wsp, $a_blog_node_id, $a_posting_id, $a_comment = null)
	{
		global $ilUser, $ilAccess;
		
		// get blog object id (repository or workspace)		
		if($a_in_wsp)
		{				
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			$tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
			$blog_obj_id = $tree->lookupObjectId($a_blog_node_id);							
			$access_handler = new ilWorkspaceAccessHandler($tree); 
								
			$link = ilWorkspaceAccessHandler::getGotoLink($a_blog_node_id, $blog_obj_id, "_".$a_posting_id);		
		}
		else
		{
			$blog_obj_id = ilObject::_lookupObjId($a_blog_node_id);
			$access_handler = null;
			
			include_once "Services/Link/classes/class.ilLink.php";
			$link = ilLink::_getStaticLink($a_blog_node_id, "blog", true , "_".$a_posting_id);
		}
		if(!$blog_obj_id)
		{
			return;
		}	
				
		include_once "./Modules/Blog/classes/class.ilBlogPosting.php";
		$posting = new ilBlogPosting($a_posting_id);
						
		// approval handling	
		$admin_only = false;	
		if(!$posting->isApproved())
		{			
			$blog = new self($blog_obj_id, false);
			if($blog->hasApproval())
			{										
				switch($a_action)
				{
					case "update":
						// un-approved posting was updated - no notifications					
						return;

					case "new":
						// un-approved posting was activated - admin-only notification					
						$admin_only = true;									
						break;				
				}
			}
		}
		
		// recipients
		include_once "./Services/Notification/classes/class.ilNotification.php";		
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_BLOG, 
			$blog_obj_id, $a_posting_id, $admin_only);		
		if(!sizeof($users))
		{
			return;
		}
		
		
		// send mails
		
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";
		include_once("./Services/User/classes/class.ilUserUtil.php");
				
		$posting_title = $posting->getTitle();		
		$blog_title = ilObject::_lookupTitle($blog_obj_id);		
		$author = $posting->getAuthor();
				
		$notified = array();
		foreach(array_unique($users) as $idx => $user_id)
		{
			// the user responsible for the action should not be notified
			if($user_id == $ilUser->getId())
			{
				continue;
			}
						
			// workspace
			if($access_handler)
			{
				if($admin_only && 
					!$access_handler->checkAccessOfUser($tree, $user_id, 'write', '', $a_blog_node_id))
				{
					continue;
				}
				if(!$access_handler->checkAccessOfUser($tree, $user_id, 'read', '', $a_blog_node_id))
				{
					continue;
				}
			}
			// repository
			else
			{
				if($admin_only && 
					!$ilAccess->checkAccessOfUser($user_id, 'write', '', $a_blog_node_id))
				{
					continue;
				}
				if(!$ilAccess->checkAccessOfUser($user_id, 'read', '', $a_blog_node_id))
				{
					continue;
				}
			}
										
			// use language of recipient to compose message
			$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
			$ulng->loadLanguageModule('blog');

			$subject = sprintf($ulng->txt('blog_change_notification_subject'), $blog_title);
			$message = sprintf($ulng->txt('blog_change_notification_salutation'), ilObjUser::_lookupFullname($user_id))."\n\n";

			$message .= $ulng->txt('blog_change_notification_body_'.$a_action).":\n\n";
			$message .= $ulng->txt('obj_blog').": ".$blog_title."\n";
			$message .= $ulng->txt('blog_posting').": ".$posting_title."\n";
			$message .= $ulng->txt('blog_changed_by').": ".ilUserUtil::getNamePresentation($ilUser->getId())."\n";
			if($a_comment)
			{
				$message .= "\n".$ulng->txt('comment').":\n\"".trim($a_comment)."\"\n";
			}			
			$message .= "\n".$ulng->txt('blog_change_notification_link').": ".$link;				

			$mail_obj = new ilMail(ANONYMOUS_USER_ID);
			$mail_obj->appendInstallationSignature(true);
			$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id),
				"", "", $subject, $message, array(), array("system"));

			$notified[] = $user_id;			
		}
		
		if(sizeof($notified))
		{			
			ilNotification::updateNotificationTime(ilNotification::TYPE_BLOG, $blog_obj_id, $notified);		
		}
	}
			
	/**
	 * Deliver blog as rss feed
	 * 
	 * @param int $a_wsp_id
	 */
	static function deliverRSS($a_wsp_id)
	{
		global $tpl, $ilSetting;
		
		if(!$ilSetting->get('enable_global_profiles'))
		{
			return;
		}
		
		// #10827		
		if(substr($a_wsp_id, -4) != "_cll")
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$wsp_id = new ilWorkspaceTree(0);
			$obj_id = $wsp_id->lookupObjectId($a_wsp_id);		
			$is_wsp = "_wsp";
		}
		else
		{
			$a_wsp_id = substr($a_wsp_id, 0, -4);
			$obj_id = ilObject::_lookupObjId($a_wsp_id);
			$is_wsp = null;
		}	
		if(!$obj_id)
		{
			return;
		}
		
		$blog = new self($obj_id, false);		
		if(!$blog->hasRSS())
		{
			return;
		}
					
		include_once "Services/Feeds/classes/class.ilFeedWriter.php";
		$feed = new ilFeedWriter();
				
		include_once "Services/Link/classes/class.ilLink.php";
		$url = ilLink::_getStaticLink($a_wsp_id, "blog", true, $is_wsp);
		$url = str_replace("&", "&amp;", $url);
		
		$feed->setChannelTitle($blog->getTitle());
		$feed->setChannelDescription($blog->getDescription());
		$feed->setChannelLink($url);
		
		// needed for blogpostinggui / pagegui
		$tpl = new ilTemplate("tpl.main.html", true, true);
		
		include_once("./Modules/Blog/classes/class.ilBlogPosting.php");					
		include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");			
		foreach(ilBlogPosting::getAllPostings($obj_id) as $item)
		{
			$id = $item["id"];

			// only published items
			$is_active = ilBlogPosting::_lookupActive($id, "blp");
			if(!$is_active)
			{
				continue;
			}
									
			$snippet = strip_tags(ilBlogPostingGUI::getSnippet($id));
			$snippet = str_replace("&", "&amp;", $snippet);	

			$url = ilLink::_getStaticLink($a_wsp_id, "blog", true, "_".$id.$is_wsp);
			$url = str_replace("&", "&amp;", $url);				

			$feed_item = new ilFeedItem();
			$feed_item->setTitle($item["title"]);
			$feed_item->setDate($item["created"]->get(IL_CAL_DATETIME));
			$feed_item->setDescription($snippet);
			$feed_item->setLink($url);
			$feed_item->setAbout($url);				
			$feed->addItem($feed_item);
		}					
		
		$feed->showFeed();
		exit();		
	}	
	
	/**
	 * Get object id of parent course/group
	 * 
	 * @param int $a_node_id
	 * @return int
	 */
	function getParentContainerId($a_node_id)
	{
		global $tree;
		
		$crs_id = $tree->checkForParentType($a_node_id, "crs");
		if($crs_id)
		{
			return $crs_id;		
		}

		$grp_id = $tree->checkForParentType($a_node_id, "grp");
		if($grp_id)
		{
			return $grp_id;		
		}		
	}
	
	/**
	 * Get parent members object
	 * 
	 * @param int $a_node_id
	 * @return array
	 */
	function getParentMemberIds($a_node_id)
	{		
		$container_id = $this->getParentContainerId($a_node_id);		
		if($container_id)
		{			
			$members = null;
			
			if(ilObject::_lookupType($container_id) == "crs")
			{
				include_once "Modules/Course/classes/class.ilCourseParticipants.php";
				$members = new ilCourseParticipants(ilObject::_lookupObjId($container_id));				
			}
			else
			{			
				include_once "Modules/Group/classes/class.ilGroupParticipants.php";
				$members = new ilGroupParticipants(ilObject::_lookupObjId($container_id));								
			}
			
			// :TODO: review limit, members vs. participants
			if($members && $members->getCountParticipants() < 100)
			{
				return $members->getParticipants();							
			}
		}
	}
	
	function initDefaultRoles()
	{
		global $rbacadmin, $rbacreview, $ilDB;

		// SET PERMISSION TEMPLATE OF NEW LOCAL CONTRIBUTOR ROLE
		$set = $ilDB->query("SELECT obj_id FROM object_data ".
			" WHERE type=".$ilDB->quote("rolt", "text").
			" AND title=".$ilDB->quote("il_blog_contributor", "text"));
		$res = $ilDB->fetchAssoc($set);
		if($res["obj_id"])
		{
			$rolf_obj = $this->createRoleFolder();

			// CREATE ADMIN ROLE
			$role_obj = $rolf_obj->createRole("il_blog_contributor_".$this->getRefId(),
				"Contributor of blog obj_no.".$this->getId());

			$rbacadmin->copyRoleTemplatePermissions($res["obj_id"], ROLE_FOLDER_ID, 
				$rolf_obj->getRefId(), $role_obj->getId());

			// SET OBJECT PERMISSIONS OF BLOG OBJECT
			$ops = $rbacreview->getOperationsOfRole($role_obj->getId(), "blog", $rolf_obj->getRefId());
			$rbacadmin->grantPermission($role_obj->getId(), $ops, $this->getRefId());
			
			return true;
		}

		return false;
	}
	
	/**
	 * Get object id of local contributor role
	 * 
	 * @param int $a_node_id
	 * @return int
	 */
	function getLocalContributorRole($a_node_id)
	{
		global $rbacreview;
		
		foreach($rbacreview->getLocalRoles($a_node_id) as $role_id)
		{
			if(substr(ilObject::_lookupTitle($role_id), 0, 19)  == "il_blog_contributor")
			{
				return $role_id;
			}
		}
	}
	
	function getRolesWithContribute($a_node_id)
	{
		global $rbacreview;
		
		include_once "Services/AccessControl/classes/class.ilObjRole.php";
		
		$contr_op_id = ilRbacReview::_getOperationIdByName("contribute");
		$contr_role_id = $this->getLocalContributorRole($a_node_id);
		
		$res = array();
		foreach($rbacreview->getParentRoleIds($a_node_id) as $role_id => $role)
		{			
			if($role_id != $contr_role_id &&
				in_array($contr_op_id, $rbacreview->getActiveOperationsOfRole($a_node_id, $role_id)))
			{				
				$res[$role_id] = ilObjRole:: _getTranslation($role["title"]);
			}
		}
	
		return $res;
	}		
}

?>