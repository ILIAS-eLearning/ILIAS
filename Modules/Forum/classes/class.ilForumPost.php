<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once './Modules/Forum/classes/class.ilForumTopic.php';

/**
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumPost
{
	private $id = 0;
	
	private $forum_id = 0;
	
	private $thread_id = 0;
	
	private $user_id = 0;
	
	private $user_alias = '';
	
	private $subject = '';
	
	private $message = '';
	
	private $createdate = '0000-00-00 00:00:00';
	
	private $changedate = '0000-00-00 00:00:00';
	
	private $user_id_update = 0;
	
	private $censored = 0;
	
	private $censorship_comment = '';
	
	private $notification = 0;
	
	private $import_name = '';
	
	private $status = 1;	
	
	private $tree_id = 0;
	
	private $parent_id = 0;
	
	private $lft = 0;
	
	private $rgt = 0;
	
	private $depth = 0;
	
	private $fullname = '';
	
	private $loginname = '';
	
	private $objThread = null;
	
	private $db = null;
	
	private $is_moderator = false;
	
	public function __construct($a_id = 0, $a_is_moderator = false)
	{
		global $ilDB;

		$this->is_moderator = $a_is_moderator;
		$this->db = $ilDB;
		$this->id = $a_id;
		$this->read();
	}
	
	public function __destruct()
	{
		unset($this->db);
		unset($this->objThread);
	}
	
	public function insert()
	{
		if ($this->forum_id && $this->thread_id)
		{		
			$query = "INSERT INTO frm_posts "
					."SET "
					."pos_pk = " . $this->db->quote($this->id). ", "
					."pos_top_fk = " . $this->db->quote($this->forum_id). ", "
					."pos_thr_fk = " . $this->db->quote($this->thread_id). ", "
					."pos_usr_id = " . $this->db->quote($this->user_id). ", "
					."pos_usr_alias = " . $this->db->quote($this->user_alias). ", "
					."pos_subject = " . $this->db->quote($this->subject). ", "
					."pos_message = " . $this->db->quote($this->message). ", "
					."pos_date = " . $this->db->quote($this->createdate). ", "					
					."pos_update = " . $this->db->quote($this->changedate). ", "
					."update_user = " . $this->db->quote($this->user_id_update). ", "
					."pos_cens = " . $this->db->quote($this->censored). ", "
					."pos_cens_com = " . $this->db->quote($this->censorship_comment). ", "
					."notify = " . $this->db->quote($this->notification). ", "
					."import_name = " . $this->db->quote($this->import_name). ", "
					."pos_status = " . $this->db->quote($this->status). " ";
			$this->db->query($query);
			
			$this->id = $this->db->getLastInsertId();
			
			return true;
		}
		
		return false;
	}
	
	public function update()
	{
		if ($this->id)
		{		
			$query = "UPDATE frm_posts "
					."SET "
					."pos_top_fk = " . $this->db->quote($this->forum_id). ", "
					."pos_thr_fk = " . $this->db->quote($this->thread_id). ", "
					."pos_subject = " . $this->db->quote($this->subject). ", "
					."pos_message = " . $this->db->quote($this->message). ", "
					."pos_update = " . $this->db->quote($this->changedate). ", "
					."update_user = " . $this->db->quote($this->user_id_update). ", "
					."pos_cens = " . $this->db->quote($this->censored). ", "
					."pos_cens_com = " . $this->db->quote($this->censorship_comment). ", "
					."notify = " . $this->db->quote($this->notification). " "					
					."WHERE pos_pk = ". $this->db->quote($this->id) ." ";
			$this->db->query($query);
			
			if ($this->objThread->getFirstPostId() == $this->id)
			{
				$this->objThread->setSubject($this->subject);
				$this->objThread->update();
				$this->objThread->reload();
			}
			
			return true;
		}
		
		return false;
	}
	
	public function getDataAsArray()
	{			
		$data = array(
			'pos_pk' => $this->id,
			'pos_top_fk' => $this->forum_id,
			'pos_thr_fk' => $this->thread_id,
			'pos_usr_id' => $this->user_id,
			'pos_usr_alias'	=> $this->user_alias,
			'title' => $this->fullname,
			'loginname' => $this->loginname,
			'pos_message' => $this->message,
			'pos_subject' => $this->subject,	
			'pos_cens_com' => $this->censorship_comment,
			'pos_cens' => $this->censored,
			'pos_date' => $this->createdate,
			'pos_update' => $this->changedate,					
			'update_user' => $this->user_id_update,					
			'notify' => $this->notification,
			'import_name' => $this->import_name,
			'pos_status' => $this->status
		);
		
		return $data;
	}
	
	public function getDataAsArrayForExplorer()
	{			
		$data = array(
			'pos_pk' => $this->id,
			'child' => $this->id,
			'author' => $this->user_id,
			'alias'	=> $this->user_alias,
			'title' => $this->fullname,
			'loginname' => $this->loginname,
			'type' => 'post',
			'message' => $this->message,
			'subject' => $this->subject,	
			'pos_cens_com' => $this->censorship_comment,
			'pos_cens' => $this->censored,
			'date' => $this->createdate,
			'create_date' => $this->createdate,
			'update' => $this->changedate,					
			'update_user' => $this->user_id_update,
			'tree' => $this->thread_id,				
			'parent' => $this->parent_id,
			'lft' => $this->lft,
			'rgt' => $this->rgt,
			'depth' => $this->depth,
			'id' => $this->tree_id,
			'notify' => $this->notification,
			'import_name' => $this->import_name,
			'pos_status' => $this->status
		);
		
		return $data;
	}
	
	private function read()
	{	
		if ($this->id)
		{
			$query = "SELECT * FROM frm_posts
					  INNER JOIN frm_posts_tree ON pos_fk = pos_pk
					  WHERE pos_pk = " . $this->db->quote($this->id) . " ";
			$row = $this->db->getrow($query);
	
			if (is_object($row))
			{
				$this->id = $row->pos_pk;
				$this->forum_id = $row->pos_top_fk;
				$this->thread_id = $row->pos_thr_fk;	
				$this->user_id = $row->pos_usr_id;
				$this->user_alias = $row->pos_usr_alias;	
				$this->subject = $row->pos_subject;
				$this->message = $row->pos_message;
				$this->createdate = $row->pos_date;	
				$this->changedate = $row->pos_update;
				$this->user_id_update = $row->update_user;
				$this->censored = $row->pos_cens;
				$this->censorship_comment = $row->pos_cens_com;
				$this->notification = $row->notify;
				$this->import_name = $row->import_name ;
				$this->status = $row->pos_status;				
				$this->tree_id = $row->fpt_pk;
				$this->parent_id = $row->parent_pos;
				$this->lft = $row->lft;
				$this->rgt = $row->rgt;
				$this->depth = $row->depth;
				
				$this->getUserData();
				
				$this->objThread = new ilForumTopic($this->thread_id, $this->is_moderator);
				
				return true;
			}
			
			return false;
		}
		
		return false;
	}
	
	public function isAnyParentDeactivated()
	{
		if ($this->id)
		{
			$query = "SELECT * 
					  FROM frm_posts_tree
					  INNER JOIN frm_posts ON pos_pk = pos_fk
				  	  WHERE 1
					  AND pos_status = '0'
					  AND lft < ".$this->db->quote($this->lft)." AND rgt > ".$this->db->quote($this->rgt)." 
				  	  AND thr_fk = ".$this->db->quote($this->thread_id)." ";
			
			$res = $this->db->query($query);
			
			return $res->numRows();
		}
		
		return false;
	}
	
	private function getUserData()
	{
		global $lng;
		
		if ($this->id && $this->user_id)
		{
			require_once("./Services/User/classes/class.ilObjUser.php");
		
			if (ilObject::_exists($this->user_id))
			{
				$tmp_user = new ilObjUser($this->user_id);
				$this->fullname = $tmp_user->getFullname();
				$this->loginname = $tmp_user->getLogin();
				unset($tmp_user);
			}
		
			$this->fullname = $this->fullname ? $this->fullname : ($this->import_name ? $this->import_name : $lng->txt('unknown'));
			
			return true;
		}
		
		return false;
	}
	
	public function reload()
	{
		return $this->read();
	}
	
	public function setFullname($a_fullname)
	{
		$this->fullname = $a_fullname;
	}
	public function getFullname()
	{
		return $this->fullname;	
	}
	public function setLoginName($a_loginname)
	{
		$this->loginname = $a_loginname;
	}
	public function getLoginName()
	{
		return $this->loginname;	
	}

	public function activatePost()
	{
		if ($this->id)
		{
			$query = "UPDATE frm_posts SET pos_status = '1' WHERE pos_pk = " . $this->db->quote($this->id) . " ";
			$this->db->query($query);
			
			$this->activateParentPosts();
			
			return true;
		}
		
		return false;
	}
	
	public function activatePostAndChildPosts()
	{
		if ($this->id)
		{					  
			$query = "UPDATE frm_posts_tree	AS treea		   
					  INNER JOIN frm_posts_tree AS treeb ON treeb.thr_fk = treea.thr_fk  AND treeb.lft BETWEEN treea.lft AND treea.rgt
					  INNER JOIN frm_posts ON pos_pk = treeb.pos_fk
					  SET pos_status = '1' 
					  WHERE 1 
					  AND treea.pos_fk = " . $this->db->quote($this->id) . " ";
					  
			$this->db->query($query);
			
			$this->activateParentPosts();
				
			return true;
		}
		
		return false;
	}
	
	public function activateParentPosts()
	{
		if ($this->id)
		{					  
			$query = "UPDATE frm_posts
					  INNER JOIN frm_posts_tree ON pos_fk = pos_pk
					  SET pos_status = '1'
				  	  WHERE 1					  
					  AND lft < ".$this->db->quote($this->lft)." AND rgt > ".$this->db->quote($this->rgt)." 
				  	  AND thr_fk = ".$this->db->quote($this->thread_id)." ";

			$this->db->query($query);
				
			return true;
		}
		
		return false;
	}

	public function deactivatePostAndChildPosts()
	{
		if ($this->id)
		{
			$query = "UPDATE frm_posts_tree	AS treea		   
					  INNER JOIN frm_posts_tree AS treeb ON treeb.thr_fk = treea.thr_fk  AND treeb.lft BETWEEN treea.lft AND treea.rgt
					  INNER JOIN frm_posts ON pos_pk = treeb.pos_fk
					  SET pos_status = '0' 
					  WHERE 1 
					  AND treea.pos_fk = " . $this->db->quote($this->id) . " ";

			$this->db->query($query);
			
			return true;
		}
		
		return false;
	}
	
	public function isRead($a_user_id = 0)
	{
		if ($a_user_id && $this->id)
		{
			$query = "SELECT * 
					  FROM frm_user_read 
					  WHERE 1
					  AND usr_id = ".$this->db->quote($a_user_id)."
					  AND post_id = ".$this->db->quote($this->id)." ";
			$res = $this->db->query($query);

			return $res->numRows() ? true : false;
		}
		
		return false;
	}
	
	public function hasReplies()
	{
		if ($this->id && $this->rgt && $this->lft)
		{
			$query = "SELECT *
				 	  FROM frm_posts_tree			  		 
				  	  WHERE 1 
				  	  AND lft > ".$this->db->quote($this->lft)." AND rgt < ".$this->db->quote($this->rgt)." 
				  	  AND thr_fk = ".$this->db->quote($this->thread_id);
		
			$res = $this->db->query($query);

			return $res->numRows() ? true : false;
		}
		
		return false;
	}
	
	public function isOwner($a_user_id = 0)
	{
		if ($this->user_id && $a_user_id)
		{
			if ((int) $this->user_id == (int) $a_user_id)
			{
				return true;
			}
			return false;
		}
		return false;
	}
	
	public function setId($a_id)
	{
		$this->id = $a_id;
	}
	public function getId()
	{
		return $this->id;
	}
	public function setForumId($a_forum_id)
	{
		$this->forum_id = $a_forum_id;
	}
	public function getForumId()
	{
		return $this->forum_id;
	}
	public function setThreadId($a_thread_id)
	{
		$this->thread_id = $a_thread_id;
	}
	public function getThreadId()
	{
		return $this->thread_id;
	}
	public function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;		
	}
	public function getUserId()
	{
		return $this->user_id;
	}
	public function setUserAlias($a_user_alias)
	{
		$this->user_alias = $a_user_alias;
	}
	public function getUserAlias()
	{
		return $this->user_alias;
	}
	public function setSubject($a_subject)
	{
		$this->subject = $a_subject;
	}
	public function getSubject()
	{
		return $this->subject;
	}
	public function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	public function getMessage()
	{
		return $this->message;
	}
	public function setCreateDate($a_createdate)
	{
		$this->createdate = $a_createdate;
	}
	public function getCreateDate()
	{
		return $this->createdate;
	}
	public function setChangeDate($a_changedate)
	{
		$this->changedate = $a_changedate;
	}
	public function getChangeDate()
	{
		return $this->changedate;
	}	
	public function setUpdateUserId($a_user_id_update)
	{
		$this->user_id_update = $a_user_id_update;
	}
	public function getUpdateUserId()
	{
		return $this->user_id_update;
	}
	public function setCensorship($a_censorship)
	{
		$this->censored = $a_censorship;
	}
	public function isCensored()
	{
		return $this->censored == 1 ? true : false;
	}
	public function setCensorshipComment($a_comment)
	{
		$this->censorship_comment = $a_comment;
	}
	public function getCensorshipComment()
	{
		return $this->censorship_comment;
	}
	public function setNotification($a_notification)
	{
		$this->notification = $a_notification;
	}
	public function isNotificationEnabled()
	{
		return $this->notification == 1 ? true : false;
	}
	public function setImportName($a_import_name)
	{
		$this->import_name = $a_import_name;
	}
	public function getImportName()
	{
		return $this->import_name;
	}
	public function setStatus($a_status)
	{
		$this->status = $a_status;
	}
	public function isActivated()
	{
		return $this->status == 1 ? true : false;
	}
	public function setTreeId($a_tree_id)
	{
		$this->tree_id = $a_tree_id;
	}
	public function getTreeId()
	{
		return $this->tree_id;
	}
	public function setParentId($a_parent_id)
	{
		$this->parent_id = $a_parent_id;
	}
	public function getParentId()
	{
		return $this->parent_id;
	}
	public function setLft($a_lft)
	{
		$this->lft = $a_lft;
	}
	public function getLft()
	{
		return $this->lft;
	}
	public function setRgt($a_rgt)
	{
		$this->rgt = $a_rgt;
	}
	public function getRgt()
	{
		return $this->rgt;
	}
	public function setDepth($a_depth)
	{
		$this->depth = $a_depth;
	}
	public function getDepth()
	{
		return $this->depth;
	}
	public function setThread(ilForumTopic $thread)
	{
		$this->objThread = $thread;
	}
	public function getThread()
	{
		return $this->objThread;
	}
}
?>
