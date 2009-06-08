<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


include_once "./classes/class.ilObjectListGUI.php";

/**
* Class ilObjForumListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
* @ingroup ModulesForum
*/
class ilObjForumListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjForumListGUI()
	{
		$this->ilObjectListGUI();
	}

	function setChildId($a_child_id)
	{
		$this->child_id = $a_child_id;
	}
	function getChildId()
	{
		return $this->child_id;
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "frm";
		$this->gui_class_name = "ilobjforumgui";
		
		// general commands array
		include_once('./Modules/Forum/classes/class.ilObjForumAccess.php');
		$this->commands = ilObjForumAccess::_getCommands();
	}

	/**
	* inititialize new item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		global $ilDB;
		
		parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
/*		$this->frm_obj =& ilObjectFactory::getInstanceByRefId($this->ref_id);
		$this->frm =& new ilForum();
		$this->frm->setForumRefId($a_ref_id);
		$this->frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($a_obj_id));
*/		
	}


	/**
	* Get item properties
	*
	* Overwrite this method to add properties at
	* the bottom of the item html
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilUser, $ilAccess, $ilDB;

		$props = array();

		// Return no properties if read access isn't granted (e.g. course visibility)
		if (!$ilAccess->checkAccess('read', '', $this->ref_id))
		{
			return array();
		}

/*		include_once('./Modules/Forum/classes/class.ilForum.php');
		include_once('./Modules/Forum/classes/class.ilObjForum.php');
		
		$forumObj = new ilObjForum($this->ref_id);
		$frm =& $forumObj->Forum;		
		$frm->setForumId($forumObj->getId());
		$frm->setForumRefId($forumObj->getRefId());
		$frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($frm->getForumId()));
		
		// Forum Data
		$frmData = $frm->getOneTopic();	
*/		
		// Moderators (Role: Moderator)
/*		include_once('./Services/User/classes/class.ilObjUser.php');
		$MODS = ilForum::_getModerators($this->ref_id);
		$moderators = "";
		for ($i = 0; $i < count($MODS); $i++)
		{
			if ($moderators != "")
			{
				$moderators .= ", ";
			}
			
			if (ilObjUser::_lookupPref($MODS[$i], 'public_profile') == 'y')
			{
				$moderators .= "<a class=\"il_ItemProperty\" target=\"".
				ilFrameTargetInfo::_getFrame("MainContent").
				"\" href=\"repository.php?cmd=showUser&cmdClass=ilobjforumgui&ref_id=".$this->ref_id."&user=".
				$MODS[$i]."&offset=".$Start."\">".ilObjUser::_lookupLogin($MODS[$i])."</a>";
			}
			else
			{
				$moderators .= ilObjUser::_lookupLogin($MODS[$i]);
			}			
		}
		$props[] = array('alert' => false, 'property' => $lng->txt('forums_moderators'),
			'value' => $moderators);
*/		
		// threads
//		$threads = $frm->getAllThreads($frmData['top_pk']);
//		$props[] = array('alert' => false, 'property' => $lng->txt('forums_threads'),
//			'value' => count($threads));	
			
		// Posts
/*		$num_posts_total = 0;
		$num_unread_total = 0;
		$num_new_total = 0;
		$visits_total = 0;
		
		$objLastPost = null;
		foreach ($threads as $thread)
		{			
			$objTmpLastPost = null;
			
			if ($ilAccess->checkAccess('moderate_frm', '', $this->ref_id))
			{				
				$num_posts = $thread->countPosts();	
				$num_posts_total += $num_posts;					
				$num_unread_total =	 $num_posts - $thread->countReadPosts($ilUser->getId());
				$num_new_total += $thread->countNewPosts($ilUser->getId());
				
				$objTmpLastPost = $thread->getLastPost();
			}
			else
			{
				$num_posts = $thread->countActivePosts();				
				$num_posts_total += $num_posts;
				$num_unread_total += $num_posts - $thread->countReadActivePosts($ilUser->getId());
				$num_new_total += $thread->countNewActivePosts($ilUser->getId());
				
				$objTmpLastPost = $thread->getLastActivePost();	
			}
			
			$visits_total += $thread->getVisits();
			
			// Last Post
			if ((!is_object($objLastPost) && is_object($objTmpLastPost)) ||
				(is_object($objLastPost) && is_object($objTmpLastPost) && $objLastPost->getCreateDate() < $objTmpLastPost->getCreateDate()))
			{
				$objLastPost = $objTmpLastPost;
			}
		}
*/

include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
if ($ilAccess->checkAccess('moderate_frm', '', $this->ref_id))
{				
	$num_posts_total = ilObjForumAccess::getNumberOfPostings($this->obj_id);
	$num_unread_total = $num_posts_total - ilObjForumAccess::getNumberOfReadPostings($this->obj_id);
	$num_new_total = ilObjForumAccess::getNumberOfNewPostings($this->obj_id);
	$last_post = ilObjForumAccess::getLastPost($this->obj_id);
}
else
{
	$num_posts_total = ilObjForumAccess::getNumberOfPostings($this->obj_id, true);
	$num_unread_total = $num_posts_total - ilObjForumAccess::getNumberOfReadPostings($this->obj_id, true);
	$num_new_total = ilObjForumAccess::getNumberOfNewPostings($this->obj_id, true);
	$last_post = ilObjForumAccess::getLastPost($this->obj_id);
}
		// Posts (Unread)		
		if($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$alert = ($num_unread_total > 0) ? true : false;
			$props[] = array('alert' => $alert, 'property' => $lng->txt('forums_articles').' ('.$lng->txt('unread').')',
				'value' => $num_posts_total.' ('.$num_unread_total.')');
				
			// New
			$alert = ($num_new_total > 0)	? true : false;
			$props[] = array('alert' => $alert, 'property' => $lng->txt('forums_new_articles'),
				'value' => $num_new_total);			
		}
		else
		{
			$alert = false;
			$props[] = array('alert' => $alert, 'property' => $lng->txt('forums_articles'),
				'value' => $num_posts_total);
				
			// New
			$alert = false;
			$props[] = array('alert' => $alert, 'property' => $lng->txt('forums_new_articles'),
				'value' => 0);
		}	

		// Visits
//		$props[] = array('alert' => false, 'property' => $lng->txt('visits'),
//			'value' => $visits_total);	

		/* Forum anonymized? */
		include_once("./Modules/Forum/classes/class.ilForumProperties.php");
		if (ilForumProperties::getInstance($this->obj_id)->isAnonymized())
		{
			$props[] = array(
						'alert' => false,
						'newline' => false,
						'property' => $lng->txt('forums_anonymized'),
						'value' => $lng->txt('yes')
			);
		}
		
		// Last Post
		if ($last_post["pos_pk"] > 0)
		{
			if (ilObject::_lookupType($last_post["pos_usr_id"]) == "usr")
			{
				$last_user = ilObjUser::_lookupName($last_post["pos_usr_id"]);
			}
			else
			{
				$last_user["login"] = $last_post["import_name"]
					? $last_post["import_name"]." (".$lng->txt("imported").")"
					: $lng->txt("unknown");
			}
			
			$lpCont = "<a class=\"il_ItemProperty\" target=\"".
			ilFrameTargetInfo::_getFrame('MainContent').
			"\" href=\"repository.php?cmd=showThreadFrameset&cmdClass=ilobjforumgui&target=true&pos_pk=".
			$last_post["pos_pk"]."&thr_pk=".$last_post["pos_thr_fk"]."&ref_id=".
			$this->ref_id."#".$last_post["pos_pk"]."\">".
				ilObjForumAccess::prepareMessageForLists($last_post["pos_message"])."</a> ".
			strtolower($lng->txt('from'))."&nbsp;";
			
			if (ilForumProperties::getInstance($this->obj_id)->isAnonymized())
			{
				if ($last_post["pos_usr_alias"] != '')
				{
					$lpCont .= $last_post["pos_usr_alias"];
					
				}
				else
				{
					$lpCont .= $lng->txt('forums_anonymous');					
				}
				$lpCont .= ', '.ilDatePresentation::formatDate(new ilDateTime($last_post["pos_date"],IL_CAL_DATETIME));
			}
			else
			{
				if (ilObject::_lookupType($last_post["pos_usr_id"]) == "usr" &&
					ilObjUser::_lookupPref($last_post["pos_usr_id"], "public_profile") == 'y')
				{
					$lpCont .= "<a class=\"il_ItemProperty\" target=\"".
					ilFrameTargetInfo::_getFrame('MainContent').
					"\" href=\"repository.php?cmd=showUser&cmdClass=ilobjforumgui&ref_id=".$this->ref_id."&user=".
						$last_user['user_id']."&offset=".$Start."\">".$last_user['login']."</a>, ";
					$lpCont .= ilDatePresentation::formatDate(new ilDateTime($last_post["pos_date"],IL_CAL_DATETIME));
				}
				else
				{
					$lpCont .= $last_user['login'].', '.ilDatePresentation::formatDate(new ilDateTime($last_post["pos_date"],IL_CAL_DATETIME));
				}
			}
						
			/* At least one (last) posting? */
			if ($lpCont != '')
			{
				$props[] = array(
							'alert' => false,
							'newline' => true,
							'property' => $lng->txt('forums_last_post'),
							'value' => $lpCont
				);
			}
		}

		return $props;
	}

	/**
	* Get command target
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandFrame($a_cmd)
	{
		// separate method for this line
		$target = ilFrameTargetInfo::_getFrame("MainContent");

		return $target;
	}

	function getCommandLink($a_cmd)
	{
		switch($a_cmd)
		{
			case 'thread':
				return "repository.php?cmd=showThreadFrameset&cmdClass=ilobjforumgui&ref_id=".$this->ref_id.
					"&thr_pk=".$this->getChildId();

			case 'posting':
				$thread_post = $this->getChildId();
				return "repository.php?cmd=showThreadFrameset&cmdClass=ilobjforumgui&target=1&ref_id=".$this->ref_id.
					"&thr_pk=".$thread_post[0].
					"&pos_pk=".$thread_post[1]."#".$thread_post[1];

			default:
				return parent::getCommandLink($a_cmd);
		}
	}
}
?>