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
	protected $style; // [bool]
	protected $abstract_shorten = false; // [bool]
	protected $abstract_shorten_length = self::ABSTRACT_DEFAULT_SHORTEN_LENGTH; // [int]
	protected $abstract_image = false; // [bool]
	protected $abstract_image_width = self::ABSTRACT_DEFAULT_IMAGE_WIDTH; // [int]
	protected $abstract_image_height= self::ABSTRACT_DEFAULT_IMAGE_HEIGHT; // [int]
	protected $keywords = true; // [bool]
	protected $nav_mode = self::NAV_MODE_LIST; // [int]
	protected $nav_mode_list_postings = self::NAV_MODE_LIST_DEFAULT_POSTINGS; // [int]
	protected $nav_mode_list_months; // [int]
	protected $overview_postings; // [int]
	protected $authors = true; // [bool]
	protected $order;
	
	const NAV_MODE_LIST = 1;
	const NAV_MODE_MONTH = 2;
	
	const ABSTRACT_DEFAULT_SHORTEN_LENGTH = 500;
	const ABSTRACT_DEFAULT_IMAGE_WIDTH = 144;
	const ABSTRACT_DEFAULT_IMAGE_HEIGHT = 144;
	const NAV_MODE_LIST_DEFAULT_POSTINGS = 10;
	
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
		$this->setProfilePicture((bool)$row["ppic"]);
		$this->setBackgroundColor($row["bg_color"]);
		$this->setFontColor($row["font_color"]);
		$this->setImage($row["img"]);
		$this->setRSS($row["rss_active"]);
		$this->setApproval($row["approval"]);
		$this->setAbstractShorten($row["abs_shorten"]);
		$this->setAbstractShortenLength($row["abs_shorten_len"]);
		$this->setAbstractImage($row["abs_image"]);
		$this->setAbstractImageWidth($row["abs_img_width"]);
		$this->setAbstractImageHeight($row["abs_img_height"]);		
		$this->setKeywords($row["keywords"]);
		$this->setAuthors($row["authors"]);
		$this->setNavMode($row["nav_mode"]);
		$this->setNavModeListPostings($row["nav_list_post"]);
		$this->setNavModeListMonths($row["nav_list_mon"]);
		$this->setOverviewPostings($row["ov_post"]);
		if(trim($row["nav_order"]))
		{
			$this->setOrder(explode(";", $row["nav_order"]));
		}
		
		// #14661
		include_once("./Services/Notes/classes/class.ilNote.php");
		$this->setNotesStatus(ilNote::commentsActivated($this->id, 0, "blog"));
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->setStyleSheetId(ilObjStyleSheet::lookupObjectStyle($this->id));
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO il_blog (id,ppic,rss_active,approval".
			",abs_shorten,abs_shorten_len,abs_image,abs_img_width,abs_img_height".
			",keywords,authors,nav_mode,nav_list_post) VALUES (".
			$ilDB->quote($this->id, "integer").",".			
			$ilDB->quote(true, "integer").",".
			$ilDB->quote(true, "integer").",".
			$ilDB->quote(false, "integer").",".		
			$ilDB->quote($this->hasAbstractShorten(), "integer").",".
			$ilDB->quote($this->getAbstractShortenLength(), "integer").",".
			$ilDB->quote($this->hasAbstractImage(), "integer").",".
			$ilDB->quote($this->getAbstractImageWidth(), "integer").",".
			$ilDB->quote($this->getAbstractImageHeight(), "integer").",".	
			$ilDB->quote($this->hasKeywords(), "integer").",".	
			$ilDB->quote($this->hasAuthors(), "integer").",".	
			$ilDB->quote($this->getNavMode(), "integer").",".	
			$ilDB->quote($this->getNavModeListPostings(), "integer").
			")");
		
		// #14661
		include_once("./Services/Notes/classes/class.ilNote.php");
		ilNote::activateComments($this->id, 0, "blog", true);
		
		/*
		if ($this->getStyleSheetId() > 0)
		{
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			ilObjStyleSheet::writeStyleUsage($this->id, $this->getStyleSheetId());
		}		 
		*/
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
					" SET ppic = ".$ilDB->quote($this->hasProfilePicture(), "integer").
					",bg_color = ".$ilDB->quote($this->getBackgroundColor(), "text").
					",font_color = ".$ilDB->quote($this->getFontcolor(), "text").
					",img = ".$ilDB->quote($this->getImage(), "text").
					",rss_active = ".$ilDB->quote($this->hasRSS(), "text").
					",approval = ".$ilDB->quote($this->hasApproval(), "integer").
					",abs_shorten = ".$ilDB->quote($this->hasAbstractShorten(), "integer").
					",abs_shorten_len = ".$ilDB->quote($this->getAbstractShortenLength(), "integer").
					",abs_image = ".$ilDB->quote($this->hasAbstractImage(), "integer").
					",abs_img_width = ".$ilDB->quote($this->getAbstractImageWidth(), "integer").
					",abs_img_height = ".$ilDB->quote($this->getAbstractImageHeight(), "integer").
					",keywords = ".$ilDB->quote($this->hasKeywords(), "integer").
					",authors = ".$ilDB->quote($this->hasAuthors(), "integer").
					",nav_mode = ".$ilDB->quote($this->getNavMode(), "integer").
					",nav_list_post = ".$ilDB->quote($this->getNavModeListPostings(), "integer").
					",nav_list_mon = ".$ilDB->quote($this->getNavModeListMonths(), "integer").
					",ov_post = ".$ilDB->quote($this->getOverviewPostings(), "integer").
					",nav_order = ".$ilDB->quote(implode(";", $this->getOrder()), "text").
					" WHERE id = ".$ilDB->quote($this->id, "integer"));			
						
			// #14661
			include_once("./Services/Notes/classes/class.ilNote.php");
			ilNote::activateComments($this->id, 0, "blog", $this->getNotesStatus());
			
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			ilObjStyleSheet::writeStyleUsage($this->id, $this->getStyleSheetId());			
		}
	}

	protected function doCloneObject(ilObjBlog $new_obj, $a_target_id, $a_copy_id = null)
	{
		// banner?
		$img = $this->getImage();
		if($img)
		{
			$new_obj->setImage($img);
			
			$source = $this->initStorage($this->getId());
			$target = $new_obj->initStorage($new_obj->getId());
			
			copy($source.$img, $target.$img);
		}
		
		$new_obj->setNotesStatus($this->getNotesStatus());
		$new_obj->setProfilePicture($this->hasProfilePicture());
		$new_obj->setBackgroundColor($this->getBackgroundColor());
		$new_obj->setFontColor($this->getFontColor());
		$new_obj->setRSS($this->hasRSS());
		$new_obj->setApproval($this->hasApproval());
		$new_obj->setAbstractShorten($this->hasAbstractShorten());
		$new_obj->setAbstractShortenLength($this->getAbstractShortenLength());
		$new_obj->setAbstractImage($this->hasAbstractImage());
		$new_obj->setAbstractImageWidth($this->getAbstractImageWidth());
		$new_obj->setAbstractImageHeight($this->getAbstractImageHeight());
		$new_obj->update();		
		
		// set/copy stylesheet
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$style_id = $this->getStyleSheetId();
		if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id))
		{
			$style_obj = ilObjectFactory::getInstanceByObjId($style_id);
			$new_id = $style_obj->ilClone();
			$new_obj->setStyleSheetId($new_id);
			$new_obj->update();
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
			
			$this->handleQuotaUpdate();
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
			/* as banner height should overflow, we only handle width
			$dimensions = $blga_set->get("banner_width")."x".
				$blga_set->get("banner_height");
			*/
			$dimensions = $blga_set->get("banner_width");
			
			// take quality 100 to avoid jpeg artefacts when uploading jpeg files
			// taking only frame [0] to avoid problems with animated gifs
			$original_file = ilUtil::escapeShellArg($path.$original);
			$thumb_file = ilUtil::escapeShellArg($path.$thumb);
			$processed_file = ilUtil::escapeShellArg($path.$processed);
			ilUtil::execConvert($original_file."[0] -geometry 100x100 -quality 100 JPEG:".$thumb_file);
			ilUtil::execConvert($original_file."[0] -geometry ".$dimensions." -quality 100 JPEG:".$processed_file);
			
			$this->setImage($processed);
			
			$this->handleQuotaUpdate();
			
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
	
	/**
	 * Get style sheet id
	 * 
	 * @return bool
	 */
	function getStyleSheetId()
	{
		return (int)$this->style;
	}

	/**
	 * Set style sheet id
	 *
	 * @param int $a_style
	 */
	function setStyleSheetId($a_style)
	{
		$this->style = (int)$a_style;
	}
	
	function hasAbstractShorten()
	{
		return $this->abstract_shorten;
	}
	
	function setAbstractShorten($a_value)
	{
		$this->abstract_shorten = (bool)$a_value;
	}
	
	function getAbstractShortenLength()
	{
		return $this->abstract_shorten_length;
	}
	
	function setAbstractShortenLength($a_value)
	{
		$this->abstract_shorten_length = (int)$a_value;
	}
			
	function hasAbstractImage()
	{
		return $this->abstract_image;
	}
	
	function setAbstractImage($a_value)
	{
		$this->abstract_image = (bool)$a_value;
	}
	
	function getAbstractImageWidth()
	{
		return $this->abstract_image_width;
	}
	
	function setAbstractImageWidth($a_value)
	{
		$this->abstract_image_width = (int)$a_value;
	}
	
	function getAbstractImageHeight()
	{
		return $this->abstract_image_height;
	}
	
	function setAbstractImageHeight($a_value)
	{
		$this->abstract_image_height = (int)$a_value;
	}
	
	function setKeywords($a_value)
	{
		$this->keywords = (bool)$a_value;
	}
	
	function hasKeywords()
	{		
		return $this->keywords;
	}
	
	function setAuthors($a_value)
	{
		$this->authors = (bool)$a_value;
	}
	
	function hasAuthors()
	{		
		return $this->authors;
	}
	
	function setNavMode($a_value)
	{
		$a_value = (int)$a_value;
		if(in_array($a_value, array(self::NAV_MODE_LIST, self::NAV_MODE_MONTH)))
		{
			$this->nav_mode = $a_value;
		}
	}
	
	function getNavMode()
	{
		return $this->nav_mode;
	}
	
	function setNavModeListPostings($a_value)
	{
		$this->nav_mode_list_postings = (int)$a_value;
	}
	
	function getNavModeListPostings()
	{
		return $this->nav_mode_list_postings;
	}
	
	function setNavModeListMonths($a_value)
	{
		if(!$a_value)
		{
			$a_value = null;
		}
		else
		{
			$a_value = (int)$a_value;
		}
		$this->nav_mode_list_months = $a_value;
	}
	
	function getNavModeListMonths()
	{
		return $this->nav_mode_list_months;
	}
	
	function setOverviewPostings($a_value)
	{
		if(!$a_value)
		{
			$a_value = null;
		}
		else
		{
			$a_value = (int)$a_value;
		}
		$this->overview_postings = $a_value;
	}
	
	function getOverviewPostings()
	{
		return $this->overview_postings;
	}
	
	function setOrder(array $a_values = null)
	{
		$this->order = $a_values;
	}
	
	function getOrder()
	{
		return (array)$this->order;
	}
		
	static function sendNotification($a_action, $a_in_wsp, $a_blog_node_id, $a_posting_id, $a_comment = null)
	{
		global $ilUser;
		
		// get blog object id (repository or workspace)		
		if($a_in_wsp)
		{				
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			$tree = new ilWorkspaceTree($ilUser->getId()); // owner of tree is irrelevant
			$blog_obj_id = $tree->lookupObjectId($a_blog_node_id);							
			$access_handler = new ilWorkspaceAccessHandler($tree); 	
		}
		else
		{
			$blog_obj_id = ilObject::_lookupObjId($a_blog_node_id);
			$access_handler = null;
		}
		if(!$blog_obj_id)
		{
			return;
		}	
				
		include_once "./Modules/Blog/classes/class.ilBlogPosting.php";
		$posting = new ilBlogPosting($a_posting_id);	
								
		// #11138
		$ignore_threshold = ($a_action == "comment");	
		
		$admin_only = false;	
		
		// approval handling					
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
						$ignore_threshold = true;
						$a_action = "approve";
						break;				
				}
			}
		}
		
		// recipients
		include_once "./Services/Notification/classes/class.ilNotification.php";		
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_BLOG, 
			$blog_obj_id, $a_posting_id, $ignore_threshold);			
		if(!sizeof($users))
		{
			return;
		}						
								
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification($a_in_wsp);		
		$ntf->setLangModules(array("blog"));
		$ntf->setRefId($a_blog_node_id);
		$ntf->setChangedByUserId($ilUser->getId());
		$ntf->setSubjectLangId('blog_change_notification_subject');
		$ntf->setIntroductionLangId('blog_change_notification_body_'.$a_action);
		$ntf->addAdditionalInfo('blog_posting', $posting->getTitle());
		if($a_comment)
		{
			$ntf->addAdditionalInfo('comment', $a_comment, true);
		}	
		$ntf->setGotoLangId('blog_change_notification_link');				
		$ntf->setReasonLangId('blog_change_notification_reason');		
		
		$abstract = $posting->getNotificationAbstract();
		if($abstract)
		{
			$ntf->addAdditionalInfo('content', $abstract, true);
		}	
				
		$notified = $ntf->sendMail($users, "_".$a_posting_id, 
			($admin_only ? "write" : "read"));			
				
		// #14387
		if(sizeof($notified))
		{
			ilNotification::updateNotificationTime(ilNotification::TYPE_BLOG, $blog_obj_id, $notified, $a_posting_id);		
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
		
		// #11870
		$feed->setChannelTitle(str_replace("&", "&amp;", $blog->getTitle()));
		$feed->setChannelDescription(str_replace("&", "&amp;", $blog->getDescription()));
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
	
	function initDefaultRoles()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role = ilObjRole::createDefaultRole(
				'il_blog_contributor_'.$this->getRefId(),
				"Contributor of blog obj_no.".$this->getId(),
				'il_blog_contributor',
				$this->getRefId()
		);
		
		return array();
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
	
	protected function handleQuotaUpdate()
	{								
		include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
		ilDiskQuotaHandler::handleUpdatedSourceObject($this->getType(), 
			$this->getId(),
			ilUtil::dirsize($this->initStorage($this->getId())), 
			array($this->getId()));	
	}
}

?>