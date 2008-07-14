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

require_once './Modules/Forum/classes/class.ilForumProperties.php';
require_once './Modules/Forum/classes/class.ilObjForum.php';
require_once './Modules/Forum/classes/class.ilForumTopic.php';
require_once './Modules/Forum/classes/class.ilForumPost.php';

/**
* Class Forum
* core functions for forum
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForum
{
	const SORT_TITLE = 1;
	const SORT_DATE = 2;	
	
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $lng;
	
	/**
	* database table name
	* @var string
	* @see setDbTable(), getDbTable()
	* @access private
	*/
	var $dbTable;
	
	/**
	* class name
	* @var string class name
	* @access private
	*/
	var $className="ilForum";
	
	/**
	* database table field for sorting the results
	* @var string
	* @see setOrderField()
	* @access private
	*/
	var $orderField;
	
	var $whereCondition = "1";
	
	var $txtQuote1 = "[quote]"; 
	var $txtQuote2 = "[/quote]"; 
	var $replQuote1 = '<blockquote class="ilForumQuote">'; 
	var $replQuote2 = '</blockquote>'; 
	
	// max. datasets per page
	var $pageHits = 30;

	// object id
	var $id;
	
	private $threads = array();
	
	/**
	* Constructor
	* @access	public
	*/
	function ilForum()
	{
		global $ilias,$lng;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
	}

	function setLanguage($lng)
	{
		$this->lng =& $lng;
	}

	/**
	* set object id which refers to ILIAS obj_id
	* @param	integer	object id
	* @access	public
	*/
	function setForumId($a_obj_id)
	{
		if (!isset($a_obj_id))
		{
			$message = get_class($this)."::setForumId(): No obj_id given!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);	
		}
		
		$this->id = $a_obj_id;
	}

	/**
	* set reference id which refers to ILIAS obj_id
	* @param	integer	object id
	* @access	public
	*/
	function setForumRefId($a_ref_id)
	{
		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::setForumRefId(): No ref_id given!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);	
		}
		
		$this->ref_id = $a_ref_id;
	}
	
	/**
	* get forum id
	* @access	public
	* @return	integer	object id of forum
	*/
	function getForumId()
	{
		return $this->id;
	}
	
	/**
	* get forum ref_id
	* @access	public
	* @return	integer	reference id of forum
	*/
	function getForumRefId()
	{
		return $this->ref_id;
	}
	
	/**
	* set database field for sorting results
	* @param	string	$orderField database field for sorting
	* @see				$orderField
	* @access	private
	*/
	function setOrderField($orderField)
	{
		if ($orderField == "")
		{
			die($this->className . "::setOrderField(): No orderField given.");
		}
		else
		{
			$this->orderField = $orderField;
		}
	}

	/**
	* get name of orderField
	* @return	string	name of orderField
	* @see				$orderField
	* @access	public
	*/
	function getOrderField()
	{
		return $this->orderField;
	}
	
	/**
	* set database table
	* @param	string	$dbTable database table
	* @see				$dbTable
	* @access	public
	*/
	function setDbTable($dbTable)
	{
		if ($dbTable == "")
		{
			die($this->className . "::setDbTable(): No database table given.");
		}
		else
		{
			$this->dbTable = $dbTable;
		}
	}

	/**
	* get name of database table
	* @return	string	name of database table
	* @see				$dbTable
	* @access	public
	*/
	function getDbTable()
	{
		return $this->dbTable;
	}
	
	/**
	* set content of WHERE condition
	* @param	string	$whereCondition 
	* @see				$whereCondition
	* @access	public
	*/
	function setWhereCondition($whereCondition = "1")
	{
		$this->whereCondition = $whereCondition;
		return true;
	}
	
	/**
	* get content of whereCondition
	* @return	string 
	* @see				$whereCondition
	* @access	public
	*/
	function getWhereCondition()
	{
		return $this->whereCondition;
	}
	
	/**
	* set number of max. visible datasets
	* @param	integer	$pageHits 
	* @see				$pageHits
	* @access	public
	*/
	function setPageHits($pageHits)
	{
		if ($pageHits < 1)
		{
			die($this->className . "::setPageHits(): No int pageHits given.");
		}
		else
		{
			$this->pageHits = $pageHits;
			return true;
		}
	}
	
	/**
	* get number of max. visible datasets
	* @return	integer	$pageHits 
	* @see				$pageHits
	* @access	public
	*/
	function getPageHits()
	{
		return $this->pageHits;
	}
	
	// *******************************************************************************
	
	/**
	* get one dataset from set Table and set WhereCondition
	* @return	array	$res dataset 
	* @access	public
	*/
	function getOneDataset()
	{	
		
		$q = "SELECT * FROM ".$this->dbTable." WHERE ( ".$this->whereCondition." )";
		
		if ($this->orderField != "")
			$q .= " ORDER BY ".$this->orderField;

		$res = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);

		$this->setWhereCondition("1");

		return $res;
	}

	/**
	* get one topic-dataset by WhereCondition
	* @return	array	$result dataset of the topic
	* @access	public
	*/
	function getOneTopic()
	{
		$query = "SELECT * FROM frm_data WHERE ( ".$this->whereCondition." )";

		$result = $this->ilias->db->getRow($query, DB_FETCHMODE_ASSOC);
		$this->setWhereCondition("1");
		
		if(is_null($result)) return NULL;

		$result["top_name"] = trim($result["top_name"]);
		$result["top_description"] = nl2br($result["top_description"]);

		return $result;
	}
	
	/**
	* In some rare cases the thread number in frm_data is incorrect.
	* This function fixes this. (called in ilObjForumGUI->showThreadsObject())
	*/
	function fixThreadNumber($a_top_pk, $a_num_threads)
	{
		global $ilDB;
		
		if ($a_top_pk > 0)
		{
			$query = "UPDATE frm_data SET top_num_threads = ".
				$ilDB->quote($a_num_threads)." WHERE top_pk = ".
				$ilDB->quote($a_top_pk);

			$ilDB->query($query);
		}
	}

	/**
	* lookup forum data
	*/
	function _lookupForumData($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM frm_data WHERE top_frm_fk = ".$ilDB->quote($a_obj_id);

		$result = $ilDB->getRow($query, DB_FETCHMODE_ASSOC);
		$result["top_name"] = trim($result["top_name"]);
		$result["top_description"] = nl2br($result["top_description"]);

		return $result;
	}

	/**
	* get one thread-dataset by WhereCondition
	* @return	array	$result dataset of the thread
	* @access	public
	*/
	function getOneThread()
	{		
		$query = "SELECT * FROM frm_threads WHERE ( ".$this->whereCondition." )";

		$result = $this->ilias->db->getRow($query, DB_FETCHMODE_ASSOC);

		$this->setWhereCondition("1");
		
		$result["thr_subject"] = trim($result["thr_subject"]);
				
		return $result;	
	}
	
	/**
	* get one post-dataset 
	* @param    integer post id 
	* @return	array result dataset of the post
	* @access	public
	*/
	function getOnePost($post)
	{
		global $ilDB;
					
		$q = "SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data WHERE ";		
		$q .= "pos_pk = ".$ilDB->quote($post)." AND ";
		$q .= "pos_usr_id = usr_id";		

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);
					
		$result["pos_date"] = $this->convertDate($result["pos_date"]);		
		$result["pos_message"] = nl2br($result["pos_message"]);
					
		return $result;
	}

	function getPostById($a_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM frm_posts WHERE pos_pk = ".$ilDB->quote($a_id)."";
		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $row;
		}
		return array();
	}

	function _lookupPostMessage($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM frm_posts WHERE pos_pk = ".$ilDB->quote($a_id)."";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->pos_message;
		}
		return '';
	}
	

	/**
	* generate new dataset in frm_posts
	* @param	integer	$topic
	* @param	integer	$thread
	* @param	integer	$user
	* @param	string	$message	
	* @param	integer	$parent_pos	
	* @param	integer	$notify	
	* @param	integer	$anonymize	
	* @param	string	$subject	
	* @param	datetime	$date	
	* @return	integer	$lastInsert: new post ID
	* @access	public
	*/
	function generatePost($forum_id, $thread_id, $user, $message, $parent_pos, $notify, $subject = '', $alias = '', $date = '', $status = 1, $send_activation_mail = false)
	{
		global $ilUser, $ilDB;
		
		if ($alias != '')
		{
			$user = 0;
		}
		
		$objNewPost = new ilForumPost();
		$objNewPost->setForumId($forum_id);
		$objNewPost->setThreadId($thread_id);
		$objNewPost->setSubject($subject);
		$objNewPost->setMessage(strip_tags($message));
		$objNewPost->setUserId($user);
		$objNewPost->setUserAlias($alias);
		if ($date == "")
		{
			$objNewPost->setCreateDate(date("Y-m-d H:i:s"));
		}
		else
		{
			if (strpos($date, "-") >  0)		// in mysql format
			{
				$objNewPost->setCreateDate($date);
			}
			else								// a timestamp
			{
				$objNewPost->setCreateDate(date("Y-m-d H:i:s", $date));
			}
		}
		$objNewPost->setImportName($this->getImportName());
		$objNewPost->setNotification($notify);
		$objNewPost->setStatus($status);
		$objNewPost->insert();
		
		// entry in tree-table
		if ($parent_pos == 0)
		{
			$this->addPostTree($objNewPost->getThreadId(), $objNewPost->getId(), $objNewPost->getCreateDate());
		}
		else
		{
			$this->insertPostNode($objNewPost->getId(), $parent_pos, $objNewPost->getThreadId(), $objNewPost->getCreateDate());
		}
//echo "<br>->".$objNewPost->getId()."-".$parent_pos."-".$objNewPost->getThreadId()."-".
//	$objNewPost->getCreateDate()."-".$forum_id."-".$message."-".$user."-";
		// string last post
		$lastPost = $objNewPost->getForumId()."#".$objNewPost->getThreadId()."#".$objNewPost->getId();
			
		// update thread
		$q = "UPDATE frm_threads SET thr_num_posts = thr_num_posts + 1, ";
		$q .= "thr_last_post = ".$ilDB->quote($lastPost). " ";        
		$q .= "WHERE thr_pk = ".$ilDB->quote($objNewPost->getThreadId())."";
		$result = $this->ilias->db->query($q);

		// update forum
		$q = "UPDATE frm_data SET top_num_posts = top_num_posts + 1, ";
		$q .= "top_last_post = ".$ilDB->quote($lastPost). " ";
		$q .= "WHERE top_pk = ".$ilDB->quote($objNewPost->getForumId())."";
		$result = $this->ilias->db->query($q);

		// MARK READ
		$forum_obj = ilObjectFactory::getInstanceByRefId($this->getForumRefId());
		$forum_obj->markPostRead($objNewPost->getUserId(), $objNewPost->getThreadId(), $objNewPost->getId());
		
		$pos_data = $objNewPost->getDataAsArray();
		$pos_data["ref_id"] = $this->getForumRefId();

		// FINALLY SEND MESSAGE
		$this->__sendMessage($parent_pos, $pos_data);

		// SEND NOTIFICATIONS ABOUT NEW POSTS IN A SPECIFIED TOPIC
		if ($this->ilias->getSetting("forum_notification") == 1)
		{
			$pos_data["top_name"] = $forum_obj->getTitle();			
			$this->sendForumNotifications($pos_data);
			$this->sendThreadNotifications($pos_data);
		}
		
		// Send notification to moderators if they have to enable a post
		if (!$status && $send_activation_mail)
		{
			$pos_data["top_name"] = $forum_obj->getTitle();			
			$this->sendPostActivationNotification($pos_data);
		}
		
		// Add Notification to news
		if ($status)
		{
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$news_item = new ilNewsItem();
			$news_item->setContext($forum_obj->getId(), 'frm', $objNewPost->getId(), 'pos');
			$news_item->setPriority(NEWS_NOTICE);
			$news_item->setTitle($objNewPost->getSubject());
			$news_item->setContent(nl2br($this->prepareText($objNewPost->getMessage(), 0)));
			$news_item->setUserId($user);
			$news_item->setVisibility(NEWS_USERS);
			$news_item->create();
		}
		
		return $objNewPost->getId();
	}
	
	/**
	* generate new dataset in frm_threads
	* @param	integer	$topic
	* @param	integer	$user
	* @param	string	$subject
	* @param	string	$message
	* @param	integer	$notify
	* @param	integer	$notify_posts
	* @param	integer	$anonymize
	* @param	datetime	$date
	* @return	integer	new post ID
	* @access public
	*/
	function generateThread($forum_id, $user, $subject, $message, $notify, $notify_posts, $alias = '', $date = '')
	{	
		global $ilDB;
		
		if ($alias != '')
		{
			$user = 0;
		}		
			
		$objNewThread = new ilForumTopic();
		$objNewThread->setForumId($forum_id);
		$objNewThread->setUserId($user);
		$objNewThread->setSubject($subject);
		if ($date == "")
		{
			$objNewThread->setCreateDate(date("Y-m-d H:i:s"));
		}
		else
		{
			if (strpos($date, "-") >  0)		// in mysql format
			{
				$objNewThread->setCreateDate($date);
			}
			else								// a timestamp
			{
				$objNewThread->setCreateDate(date("Y-m-d H:i:s", $date));
			}
		}
		$objNewThread->setImportName($this->getImportName());
		$objNewThread->setUserAlias($alias);
		$objNewThread->insert();
		
		if ($notify_posts == 1)
		{
			$objNewThread->enableNotification($user);
		}
			
		// update forum
		$query = "UPDATE frm_data SET top_num_threads = top_num_threads + 1 
				  WHERE top_pk = ".$ilDB->quote($forum_id)." ";
		$this->ilias->db->query($query);
		
		return $this->generatePost($forum_id, $objNewThread->getId(), $user, $message, 0, $notify, $subject, $alias, $date);
	}

	/**
	* update dataset in frm_posts
	* @param	string	message
	* @param	integer	pos_pk
	* @return	boolean
	* @access	public
	*/
	function updatePost($message, $pos_pk,$notify,$subject,$thr_pk=0)
	{	
		global $ilDB;
			
		$q = "UPDATE frm_posts ".
				 "SET ".
				 "pos_message = ".$ilDB->quote($message).",".
				 "pos_subject = ".$ilDB->quote($subject).",".
				 "pos_update = '".date("Y-m-d H:i:s")."',".
				 "update_user = ".$ilDB->quote($_SESSION["AccountId"]).", ".
			     "notify = ".$ilDB->quote($notify)." ".
				 "WHERE pos_pk = ".$ilDB->quote($pos_pk)."";
		$this->ilias->db->query($q);
	
		if ($thr_pk > 0 &&
			$pos_pk == $this->getFirstPostByThread($thr_pk))
		{
			$this->updateThread($thr_pk,$subject);
		}
		
		// Change news item accordingly
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_id = ilNewsItem::getFirstNewsIdForContext($this->id,
			"frm", $pos_pk, "pos");
		if ($news_id > 0)
		{
			$news_item = new ilNewsItem($news_id);
			$news_item->setTitle($subject);
			$news_item->setContent(nl2br($this->prepareText($message, 0)));
			$news_item->update();
		}

		return true;		
	}
	
	
	/**
	* update dataset in frm_threads
	* @param	integer	thr_pk
	* @param	string	subject
	* @return	boolean
	* @access	public
	*/
	function updateThread($thr_pk,$subject)
	{	
		global $ilDB;
			
		$q = "UPDATE frm_threads ".
				 "SET ".
				 "thr_subject = ".$ilDB->quote($subject)." ".
				 "WHERE thr_pk = ".$ilDB->quote($thr_pk)."";
		$this->ilias->db->query($q);

		return true;		
	}
	
	public function getAllForums()
	{
		$query = "SELECT * 
				  FROM frm_data 
				  WHERE 1 ";
		
		if ($this->whereCondition != '') $query .= $this->whereCondition;
	
		$res = $this->ilias->db->query($query);	
		$counter = 0;
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$forums[$counter] = $row;			
			++$counter;
		}	

		return is_array($forums) ? $forums : array();
	}
	
	/**
	* Moves all chosen threads and their posts to a new forum
	* 
	* @param    array	chosen thread pks
	* @param    integer	object id of src forum
	* @param    integer	object id of dest forum
	* @access	public
	*/
	public function moveThreads($tread_ids = array(), $src_ref_id = 0, $dest_top_frm_fk = 0)
	{	
		$src_top_frm_fk = ilObject::_lookupObjectId($src_ref_id);		
		
		if (is_numeric($src_top_frm_fk) && $src_top_frm_fk > 0 && is_numeric($dest_top_frm_fk) && $dest_top_frm_fk > 0)
		{			
			$this->setWhereCondition(" top_frm_fk = ".$this->ilias->db->quote($src_top_frm_fk));
			$oldFrmData = $this->getOneTopic();			
			
			$this->setWhereCondition(" top_frm_fk = ".$this->ilias->db->quote($dest_top_frm_fk));
			$newFrmData = $this->getOneTopic();
			
			if ($oldFrmData['top_pk'] && $newFrmData['top_pk'])
			{
				$moved_posts = 0;
				$moved_threads = 0;
				$visits = 0;
				foreach ($tread_ids as $id)
				{
					$objTmpThread = new ilForumTopic($id);					

					$numPosts = $objTmpThread->movePosts($src_top_frm_fk, $oldFrmData['top_pk'], $dest_top_frm_fk, $newFrmData['top_pk']);					
					if (($last_post_string = $objTmpThread->getLastPostString()) != '')
					{
						$last_post_string = explode('#', $last_post_string);
						$last_post_string[0] = $newFrmData['top_pk'];
						$last_post_string = implode('#', $last_post_string);
						$objTmpThread->setLastPostString($last_post_string);
					}
					
					$visits += $objTmpThread->getVisits();
					
					$moved_posts += $numPosts;
					++$moved_threads;
					
					$objTmpThread->setForumId($newFrmData['top_pk']);
					$objTmpThread->update();
					
					unset($objTmpThread);
				}				
				
				// update frm_data source forum
				$query = "SELECT pos_thr_fk, pos_pk 
						  FROM frm_posts						  
						  WHERE 1 
						  AND pos_top_fk = ".$this->ilias->db->quote($oldFrmData['top_pk'])." 
						  ORDER BY pos_date DESC
						  LIMIT 1";
				$res = $this->ilias->db->query($query);
				$row = $res->fetchRow(DB_FETCHMODE_OBJECT);				
				$last_post_src = $oldFrmData['top_pk'] . '#' . $row->pos_thr_fk . '#' . $row->pos_pk;
				
				$query = "UPDATE frm_data
						  SET
						  top_num_posts = top_num_posts - ".$this->ilias->db->quote($moved_posts).",
						  top_num_threads = top_num_threads - ".$this->ilias->db->quote($moved_threads).",
						  visits = visits - ".$this->ilias->db->quote($visits).",
						  top_last_post = ".$this->ilias->db->quote($last_post_src)."
						  WHERE 1 
						  AND top_pk = ".$this->ilias->db->quote($oldFrmData['top_pk'])." ";
				$this->ilias->db->query($query);
				
				// update frm_data destination forum
				$query = "SELECT pos_thr_fk, pos_pk 
						  FROM frm_posts						  
						  WHERE 1 
						  AND pos_top_fk = ".$this->ilias->db->quote($newFrmData['top_pk'])." 
						  ORDER BY pos_date DESC
						  LIMIT 1";
				$res = $this->ilias->db->query($query);
				$row = $res->fetchRow(DB_FETCHMODE_OBJECT);				
				$last_post_dest = $newFrmData['top_pk'] . '#' . $row->pos_thr_fk . '#' . $row->pos_pk;							
				
				$query = "UPDATE frm_data
						  SET
						  top_num_posts = top_num_posts + ".$this->ilias->db->quote($moved_posts).",
						  top_num_threads = top_num_threads + ".$this->ilias->db->quote($moved_threads).",
						  visits = visits + ".$this->ilias->db->quote($visits).",
						  top_last_post = ".$this->ilias->db->quote($last_post_dest)."
						  WHERE 1 
						  AND top_pk = ".$this->ilias->db->quote($newFrmData['top_pk'])." ";
				$this->ilias->db->query($query);
				
				/*
				// update news items
				include_once("./Services/News/classes/class.ilNewsItem.php");
				$objNewsItem = new ilNewsItem();
				$news_items = $objNewsItem->getNewsForRefId($src_ref_id);
				foreach ($news_items as $news_item)
				{
					$tmpObjNewsItem = new ilNewsItem($news_item['id']);
					if ($tmpObjNewsItem->getContextObjId() == $src_top_frm_fk)
					{
						$tmpObjNewsItem->setContextObjId($dest_top_frm_fk);
						$tmpObjNewsItem->update();
					}
					unset($tmpObjNewsItem);
				}
				*/
			}
		}
	}
	
	
	/**
	* update dataset in frm_posts with censorship info
	* @param	string	message	
	* @param	integer	pos_pk	
	* @return	boolean
	* @access	public
	*/
	function postCensorship($message, $pos_pk, $cens = 0)
	{		
		global $ilDB;
		
		$q = "UPDATE frm_posts ".
				 "SET ".
				 "pos_cens_com = ".$ilDB->quote($message).",".
				 "pos_update = '".date("Y-m-d H:i:s")."',".
				 "pos_cens = ".$ilDB->quote($cens).",".
				 "update_user = ".$ilDB->quote($_SESSION["AccountId"])." ".				 
				 "WHERE pos_pk = ".$ilDB->quote($pos_pk)."";
		$this->ilias->db->query($q);
	
		// Change news item accordingly
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$news_id = ilNewsItem::getFirstNewsIdForContext($this->id,
			"frm", $pos_pk, "pos");
		if ($news_id > 0)
		{
			if ($cens > 0)		// censor
			{
				$news_item = new ilNewsItem($news_id);
				//$news_item->setTitle($subject);
				$news_item->setContent(nl2br($this->prepareText($message, 0)));
				$news_item->update();
			}
			else				// revoke censorship
			{
				// get original message
				$q = "SELECT * FROM frm_posts ".
					"WHERE pos_pk = ".$ilDB->quote($pos_pk)."";
				$set = $ilDB->query($q);
				$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
				
				$news_item = new ilNewsItem($news_id);
				//$news_item->setTitle($subject);
				$news_item->setContent(nl2br($this->prepareText($rec["pos_message"], 0)));
				$news_item->update();
			}
		}

		return true;		
	}
	
	/**
	* delete post and sub-posts
	* @param	integer	$post: ID	
	* @access	public
	* @return	integer	0 or thread-ID
	*/
	function deletePost($post)
	{
		global $ilDB;
		
		include_once "./Modules/Forum/classes/class.ilObjForum.php";
		
		// delete tree and get id's of all posts to delete
		$p_node = $this->getPostNode($post);	
		$del_id = $this->deletePostTree($p_node);

		// Delete User read entries
		foreach($del_id as $post_id)
		{
			ilObjForum::_deleteReadEntries($post_id);
		}

		// DELETE ATTACHMENTS ASSIGNED TO POST
		$this->__deletePostFiles($del_id);
		
		$dead_pos = count($del_id);
		$dead_thr = 0;

		// if deletePost is thread opener ...
		if ($p_node["parent"] == 0)
		{
			// delete thread access data
			include_once './Modules/Forum/classes/class.ilObjForum.php';

			ilObjForum::_deleteAccessEntries($p_node['tree']);

			// delete thread
			$dead_thr = $p_node["tree"];
			$query = "DELETE FROM frm_threads ".
					 "WHERE thr_pk = ".$ilDB->quote($p_node["tree"])."";					 
			$this->ilias->db->query($query);
			// update num_threads
			$query2 = "UPDATE frm_data ".
					 "SET ".
					 "top_num_threads = top_num_threads - 1 ".					
					 "WHERE top_frm_fk = ".$ilDB->quote($this->id)."";
			$this->ilias->db->query($query2);
			
			// delete all related news
			$q = "SELECT * FROM frm_posts ".
				"WHERE pos_thr_fk = ".$ilDB->quote($p_node["tree"])."";
			$posset = $this->ilias->db->query($q);
			while ($posrec = $posset->fetchRow(DB_FETCHMODE_ASSOC))
			{
				include_once("./Services/News/classes/class.ilNewsItem.php");
				$news_id = ilNewsItem::getFirstNewsIdForContext($this->id,
					"frm", $posrec["pos_pk"], "pos");
				if ($news_id > 0)
				{
					$news_item = new ilNewsItem($news_id);
					$news_item->delete();
				}
			}
			
			
			// delete all posts of this thread
			$query3 = "DELETE FROM frm_posts ".
					 "WHERE pos_thr_fk = ".$ilDB->quote($p_node["tree"])."";					 
			$this->ilias->db->query($query3);
		}
		else
		{
			// delete this post and its sub-posts
			for ($i = 0; $i < $dead_pos; $i++)
			{
				$query = "DELETE FROM frm_posts ".
						 "WHERE pos_pk = ".$ilDB->quote($del_id[$i])."";					 
				$this->ilias->db->query($query);
				
				// delete related news item
				include_once("./Services/News/classes/class.ilNewsItem.php");
				$news_id = ilNewsItem::getFirstNewsIdForContext($this->id,
					"frm", $del_id[$i], "pos");
				if ($news_id > 0)
				{
					$news_item = new ilNewsItem($news_id);
					$news_item->delete();
				}
			}
			
			// update num_posts in frm_threads
			$query2 = "UPDATE frm_threads ".
					 "SET ".
					 "thr_num_posts = thr_num_posts - ".$ilDB->quote($dead_pos)." ".					
					 "WHERE thr_pk = ".$ilDB->quote($p_node["tree"])."";
			$this->ilias->db->query($query2);
			
			// get latest post of thread and update last_post
			$q = "SELECT * FROM frm_posts WHERE ";
			$q .= "pos_thr_fk = ".$ilDB->quote($p_node["tree"])." ";
			$q .= "ORDER BY pos_date DESC";
			
			$res1 = $this->ilias->db->query($q);
			
			if ($res1->numRows() == 0)
			{
				$lastPost_thr = "";
			}
			else
			{
				$z = 0;

				while ($selData = $res1->fetchRow(DB_FETCHMODE_ASSOC))
				{
					if ($z > 0)
					{
						break;
					}

					$lastPost_thr = $selData["pos_top_fk"]."#".$selData["pos_thr_fk"]."#".$selData["pos_pk"];
					$z ++;
				}
			}
			
			$query4 = "UPDATE frm_threads ".
					  "SET ".
					  "thr_last_post = ".$ilDB->quote($lastPost_thr)." ".					
					  "WHERE thr_pk = ".$ilDB->quote($p_node["tree"])."";
			$this->ilias->db->query($query4);	
		}
		
		// update num_posts in frm_data
		$qu = "UPDATE frm_data ".
			"SET ".
			"top_num_posts = top_num_posts - ".$ilDB->quote($dead_pos)." ".					
			"WHERE top_frm_fk = ".$ilDB->quote($this->id)."";
		$this->ilias->db->query($qu);
		
		// get latest post of forum and update last_post
		$q = "SELECT * FROM frm_posts, frm_data WHERE ";
		$q .= "pos_top_fk = top_pk AND ";
		$q .= "top_frm_fk = ".$ilDB->quote($this->id)." ";
		$q .= "ORDER BY pos_date DESC";
		
		$res2 = $this->ilias->db->query($q);
		
		if ($res2->numRows() == 0)
		{
			$lastPost_top = "";
		}
		else
		{
			$z = 0;

			while ($selData = $res2->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if ($z > 0)
				{
					break;
				}

				$lastPost_top = $selData["pos_top_fk"]."#".$selData["pos_thr_fk"]."#".$selData["pos_pk"];
				$z ++;
			}
		}
		
		$query5 = "UPDATE frm_data ".
				 "SET ".
				 "top_last_post = ".$ilDB->quote($lastPost_top)." ".					
				 "WHERE top_frm_fk = ".$ilDB->quote($this->id)."";
		$this->ilias->db->query($query5);		

		return $dead_thr;		
	}
	
	/**
	* Get all threads of given thread
	*
	* @param	integer	topic: Forum-ID
	* @return	array	this->threads array ob thread objects
	* @access	public
	*/
	function getAllThreads($a_topic_id, $is_moderator = false)
	{
		global $ilDB, $ilUser;
		
		$this->threads = array();
		
		$query = "SELECT *, MAX(pos_date) AS post_date 
				  FROM `frm_threads`
				  LEFT JOIN frm_posts ON pos_thr_fk = thr_pk ";
		if (!$is_moderator) $query .= " AND (pos_status = '1' OR (pos_status = '0' AND pos_usr_id = ".$ilDB->quote($ilUser->getId()).")) ";
		$query .="WHERE 1
				  AND thr_top_fk = ".$ilDB->quote($a_topic_id)."
				  GROUP BY thr_pk
				  ORDER BY is_sticky DESC, post_date DESC, thr_date DESC";
		$res = $this->ilias->db->query($query);	

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->threads[] = new ilForumTopic($row->thr_pk);
		}
		
		return $this->threads;	
	}	
	
	/**
	* get all posts of given thread
	*
	* @param	integer	topic: forum-ID
	* @param	integer	thread: thread-ID
	* @return	object	res result identifier for use with fetchRow
	* @access	public
	*/
	function getPostList($topic, $thread)
	{
		global $ilDB;
		
		$q = "SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data WHERE ";
		$q .= "pos_top_fk = ".$ilDB->quote($topic)." AND ";
		$q .= "pos_thr_fk = ".$ilDB->quote($thread)." AND ";
		$q .= "pos_usr_id = usr_id";

		if ($this->orderField != "")
		{
			$q .= " ORDER BY ".$this->orderField;
		}
		
		$res = $this->ilias->db->query($q);			

		return $res;
	}
	
	public function getUserStatistic($is_moderator = false)
	{
		global $ilDB, $ilUser;
		
		$statistic = array();
		
 		$query = "SELECT COUNT(f.pos_usr_id) as ranking, u.login, IF(p.value<>'n',u.lastname,'') as lastname, IF (p.value<>'n',u.firstname,'') as firstname 
 						FROM frm_posts f, frm_posts_tree t, frm_threads th, usr_data u, frm_data d , usr_pref p
						WHERE p.usr_id = u.usr_id AND p.keyword='public_profile'";
		               
		if (!$is_moderator) $query .= " AND (pos_status = '1' OR (pos_status = '0' AND pos_usr_id = ".$ilDB->quote($ilUser->getId()).")) ";
                  
		$query .="AND f.pos_pk = t.pos_fk 
				  AND t.thr_fk = th.thr_pk
				  AND u.usr_id = f.pos_usr_id
				  AND d.top_pk = f.pos_top_fk
				  AND d.top_frm_fk = ".$ilDB->quote($this->getForumId())."
                  GROUP BY pos_usr_id";
				  
		$res = $this->ilias->db->query ($query);
		
		$counter = 0;
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
		    $statistic[$counter][] = $row['ranking'];
		    $statistic[$counter][] = $row['login'];
		    $statistic[$counter][] = $row['lastname'];
		    $statistic[$counter][] = $row['firstname'];
		    
		    ++$counter;
		}	  
				  
		return is_array($statistic) ? $statistic : array(); 
	}
	
	
	/**
	 * Get first post of thread
	 *
	 * @access public
	 * @param int thread id
	 * @return
	 */
	public function getFirstPostByThread($a_thread_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM frm_posts_tree ".
			"WHERE thr_fk = ".$ilDB->quote($a_thread_id)." ".
			"AND parent_pos = 0";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->pos_fk ? $row->pos_fk : 0;
	}

	/**
	* get content of given ID's
	* @param	string	$lastPost: ID's, separated with #
	* @return	array	$result 
	* @access	public
	*/
	function getLastPost($lastPost)
	{
		$data = explode('#', $lastPost);
		
		$objLastPost = new ilForumPost($data[2]);	

		// limit the message-size
		$message = $this->prepareText($objLastPost->getMessage(), 2);
		
		if (strpos($message, $this->txtQuote2) > 0)
		{
			$viewPos = strrpos($message, $this->txtQuote2) + strlen($this->txtQuote2);
			$message = substr($message, $viewPos);				
		}
		
		if (strlen($message) > 40)
		{
			$message = substr($objLastPost->getMessage(), 0, 37).'...';
		}
		
		$result["pos_message"] = $result["pos_message"];
	
		// convert date
		$objLastPost->setCreateDate($this->convertDate($objLastPost->getCreateDate()));
				
		return $objLastPost;
	}	
	
	/**
	* get content of given user-ID
	*
	* @param	integer $a_user_id: user-ID
	* @return	object	user object 
	* @access	public
   	*/
	function getUser($a_user_id)
	{
		$userObj = new ilObjUser($a_user_id);

		return $userObj;
	}

	/**
	* get all users assigned to local role il_frm_moderator_<frm_ref_id>
	*
	* @return	array	user_ids
	* @access	public
   	*/
	function getModerators()
	{
		global $rbacreview;

		return $this->_getModerators($this->getForumRefId());
	}

	/**
	* get all users assigned to local role il_frm_moderator_<frm_ref_id> (static)
	*
	* @param	int		$a_ref_id	reference id
	* @return	array	user_ids
	* @access	public
	*/
	function _getModerators($a_ref_id)
	{
		global $rbacreview;

		$rolf 	   = $rbacreview->getRoleFolderOfObject($a_ref_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			//$roleObj = $this->ilias->obj_factory->getInstanceByObjId($role_id);
			$title = ilObject::_lookupTitle($role_id);
			if ($title == "il_frm_moderator_".$a_ref_id)			
			{
				#return $rbacreview->assignedUsers($roleObj->getId());
				return $title = $rbacreview->assignedUsers($role_id);
			}
		}

		return array();
	}
	
	/**
	* checks whether a user is moderator of a given forum object
	*
	* @param	int		$a_ref_id	reference id
	* @param	int		$a_usr_id	user id
	* @return	bool
	* @access	public
	*/
	function _isModerator($a_ref_id, $a_usr_id)
	{
		return in_array($a_usr_id, ilForum::_getModerators($a_ref_id));
	}	
	
	/**
   	* get number of articles from given user-ID
	*
	* @param	integer	$user: user-ID
	* @return	integer
	* @access	public
   	*/
	function countUserArticles($a_user_id)
	{
		global $ilDB;

		$q = "SELECT * 
			  FROM frm_data
			  INNER JOIN frm_posts ON pos_top_fk = top_pk 
			  WHERE 1
			  AND top_frm_fk = ".$ilDB->quote($this->getForumId())."
			  AND pos_usr_id = ".$ilDB->quote($a_user_id)." ";				
		$res = $this->ilias->db->query($q);			
		
		return $res->numRows();
	}	
	
	public function countActiveUserArticles($a_user_id)
	{
		global $ilDB, $ilUser;
		
		$q = "SELECT * 
			  FROM frm_data
			  INNER JOIN frm_posts ON pos_top_fk = top_pk
			  WHERE 1
			  AND top_frm_fk = ".$ilDB->quote($this->getForumId())."
			  AND (pos_status = '1' OR (pos_status = '0' AND pos_usr_id = ".$ilDB->quote($ilUser->getId())."))	   
			  AND pos_usr_id = ".$ilDB->quote($a_user_id)." ";				
		$res = $this->ilias->db->query($q);			
		
		return $res->numRows();
	}
	
	/**
   	* builds a string to show the forum-context
	* @param	integer	ref_id
	* @return	string
	* @access	public
   	*/
	function getForumPath($a_ref_id)
	{
		global $tree;		
		
		$path = "";		
					
		$tmpPath = $tree->getPathFull($a_ref_id);		
		// count -1, to exclude the forum itself
		for ($i = 0; $i < (count($tmpPath)-1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];						
		}						
		
		return $path;
	}
	
	/**
    * converts the date format
    * @param	string	$date 
    * @return	string	formatted datetime
    * @access	public
    */
    function convertDate($date)
    {
        global $lng;
        
        return ilDatePresentation::formatDate(new ilDateTime($date,IL_CAL_DATETIME));
    }
	
	/**
	* create a new post-tree
	* @param	integer		a_tree_id: id where tree belongs to
	* @param	integer		a_node_id: root node of tree (optional; default is tree_id itself)
	* @return	boolean		true on success
	* @access	public
	*/
	function addPostTree($a_tree_id, $a_node_id = -1, $a_date = '')
	{
		global $ilDB;
		
		$a_date = $a_date ? $a_date : date("Y-m-d H:i:s");
		
		if ($a_node_id <= 0)
		{
			$a_node_id = $a_tree_id;
		}
		
		$query = "INSERT INTO frm_posts_tree (thr_fk, pos_fk, parent_pos, lft, rgt, depth, date) ".
				 "VALUES ".
				 "(".$ilDB->quote($a_tree_id).",".$ilDB->quote($a_node_id).", 0, 1, 2, 1, ".$ilDB->quote($a_date).")";
		$this->ilias->db->query($query);
		
		return true;
	}
	
	/**
	* insert node under parent node
	* @access	public
	* @param	integer		node_id
	* @param	integer		tree_id
	* @param	integer		parent_id (optional)
	*/
	function insertPostNode($a_node_id, $a_parent_id, $tree_id, $a_date = '')
	{		
		global $ilDB;
		
		$a_date = $a_date ? $a_date : date("Y-m-d H:i:s");
		
		// get left value
	    $query = "SELECT * FROM frm_posts_tree ".
		   "WHERE pos_fk = ".$ilDB->quote($a_parent_id)." ".		   
		   "AND thr_fk = ".$ilDB->quote($tree_id)."";
	    $res = $this->ilias->db->getRow($query);
		
		$left = $res->lft;

		$lft = $left + 1;
		$rgt = $left + 2;

		// spread tree
		$query = "UPDATE frm_posts_tree SET ".
				 "lft = CASE ".
				 "WHEN lft > ".$ilDB->quote($left)." ".
				 "THEN lft + 2 ".
				 "ELSE lft ".
				 "END, ".
				 "rgt = CASE ".
				 "WHEN rgt > ".$ilDB->quote($left)." ".
				 "THEN rgt + 2 ".
				 "ELSE rgt ".
				 "END ".
				 "WHERE thr_fk = ".$ilDB->quote($tree_id)."";
		$this->ilias->db->query($query);
		
		$depth = $this->getPostDepth($a_parent_id, $tree_id) + 1;
	
		// insert node
		$query = "INSERT INTO frm_posts_tree (thr_fk,pos_fk,parent_pos,lft,rgt,depth,date) ".
				 "VALUES ".
				 "(".$ilDB->quote($tree_id).",".$ilDB->quote($a_node_id).",".$ilDB->quote($a_parent_id).",".$ilDB->quote($lft).",".
				 	$ilDB->quote($rgt).",".$ilDB->quote($depth).",".$ilDB->quote($a_date).")";
		$this->ilias->db->query($query);
	}

	/**
	* Return depth of an object
	* @access	private
	* @param	integer		node_id of parent's node_id
	* @param	integer		node_id of parent's node parent_id
	* @return	integer		depth of node
	*/
	function getPostDepth($a_node_id, $tree_id)
	{
		global $ilDB;
		
		if ($tree_id)
		{
			$query = "SELECT depth FROM frm_posts_tree ".
					 "WHERE pos_fk = ".$ilDB->quote($a_node_id)." ".					 
					 "AND thr_fk = ".$ilDB->quote($tree_id)."";
	
			$res = $this->ilias->db->getRow($query);
	
			return $res->depth;
		}
		else
		{
			return 0;
		}
	}

	/**
	* get all nodes in the subtree under specified node
	* 
	* @access	public
	* @param	array		node_data
	* @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
	*/
	function getPostTree($a_node)
	{
		global $ilDB;

	    $subtree = array();

		$query = "SELECT * FROM frm_posts_tree ".
				 "LEFT JOIN frm_posts ON frm_posts.pos_pk = frm_posts_tree.pos_fk ".
				 "WHERE frm_posts_tree.lft BETWEEN ".$ilDB->quote($a_node["lft"])." AND ".$ilDB->quote($a_node["rgt"])." ".
				 "AND thr_fk = ".$ilDB->quote($a_node["tree"])."";
		if ($this->orderField == "frm_posts_tree.date")
			$query .= " ORDER BY ".$this->orderField." ASC";
		else if ($this->orderField != "")
			$query .= " ORDER BY ".$this->orderField." DESC";
//echo ":".$this->orderField.":<br>";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$subtree[] = $this->fetchPostNodeData($row);
		}

		return $subtree;
	}

	/**
	* get child nodes of given node
	* @access	public
	* @param	integer		node_id
	* @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
	* @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
	* @return	array		with node data of all childs or empty array
	*/
	function getPostChilds($a_node_id, $a_thr_id)
	{
		global $ilDB;
		
		// init childs
		$childs = array();

		// number of childs
		$count = 0;

		$q = "SELECT * FROM frm_posts_tree,frm_posts ".
			"WHERE frm_posts.pos_pk = frm_posts_tree.pos_fk ".
			"AND frm_posts_tree.parent_pos = ".$ilDB->quote($a_node_id)." ".
			"AND frm_posts_tree.thr_fk = ".$ilDB->quote($a_thr_id)." ".
			"ORDER BY frm_posts_tree.lft DESC";
		$r = $this->ilias->db->query($q);

		$count = $r->numRows();

		if ($count > 0)
		{
			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$childs[] = $this->fetchPostNodeData($row);
			}

			// mark the last child node (important for display)
			$childs[$count - 1]["last"] = true;

			return $childs;
		}
		else
		{
			return $childs;
		}
	}
	/**
	* get data of the first node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		tree id	
	* @return	object		db result object
	*/
	function getFirstPostNode($tree_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM frm_posts, frm_posts_tree ".
				 "WHERE pos_pk = pos_fk ".				 
				 "AND parent_pos = 0 ".
				 "AND thr_fk = ".$ilDB->quote($tree_id)."";

		$res = $this->ilias->db->query($query);
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return $this->fetchPostNodeData($row);
	}

	/**
	* get data of given node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		post_id	
	* @return	object		db result object
	*/
	function getPostNode($post_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM frm_posts, frm_posts_tree ".
				 "WHERE pos_pk = pos_fk ".				 
				 "AND pos_pk = ".$ilDB->quote($post_id)."";
		$res = $this->ilias->db->query($query);
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return $this->fetchPostNodeData($row);
	}

	/**
	* get data of parent node from frm_posts_tree and frm_posts
	* @access	private
 	* @param	object	db	db result object containing node_data
	* @return	array		2-dim (int/str) node_data
	*/
	function fetchPostNodeData($a_row)
	{
		global $lng;

		require_once('./Services/User/classes/class.ilObjUser.php');
		
		if (ilObject::_exists($a_row->pos_usr_id))
		{
			$tmp_user = new ilObjUser($a_row->pos_usr_id);
			$fullname = $tmp_user->getFullname();
			$loginname = $tmp_user->getLogin();
		}
	
		$fullname = $fullname ? $fullname : ($a_row->import_name ? $a_row->import_name : $lng->txt("unknown"));

		$data = array(
					"pos_pk"		=> $a_row->pos_pk,
					"child"         => $a_row->pos_pk,
					"author"		=> $a_row->pos_usr_id,
					"alias"			=> $a_row->pos_usr_alias,
					"title"         => $fullname,
					"loginname"		=> $loginname,
					"type"          => "post",
					"message"		=> $a_row->pos_message,
					"subject"		=> $a_row->pos_subject,	
					"pos_cens_com"	=> $a_row->pos_cens_com,
					"pos_cens"		=> $a_row->pos_cens,
					"date"			=> $a_row->date,
					"create_date"	=> $a_row->pos_date,
					"update"		=> $a_row->pos_update,					
					"update_user"	=> $a_row->update_user,
					"tree"			=> $a_row->thr_fk,					
					"parent"		=> $a_row->parent_pos,
					"lft"			=> $a_row->lft,
					"rgt"			=> $a_row->rgt,
					"depth"			=> $a_row->depth,
					"id"			=> $a_row->fpt_pk,
					"notify"		=> $a_row->notify,
					"import_name"   => $a_row->import_name,
					"pos_status"   => $a_row->pos_status
					);
		
		// why this line? data should be stored without slashes in db
		//$data["message"] = stripslashes($data["message"]);
		
		return $data ? $data : array();
	}

	/**
	* Return the maximum depth in tree
	* @access	public
	* @return	integer	max depth level of tree
	*/
	function getPostMaximumDepth($a_thr_id)
	{
		global $ilDB;
		
		$q = "SELECT MAX(depth) FROM frm_posts_tree ".
			"WHERE thr_fk = ".$ilDB->quote($a_thr_id)."";
		$r = $this->ilias->db->query($q);
		
		$row = $r->fetchRow();
		
		return $row[0];
	}


	/**
	* delete node and the whole subtree under this node
	* @access	public
	* @param	array	node_data of a node
	* @return	array	ID's of deleted posts
	*/
	function deletePostTree($a_node)
	{
		global $ilDB;
		
		// GET LEFT AND RIGHT VALUES
		$query = "SELECT * FROM frm_posts_tree ".
			"WHERE thr_fk = ".$ilDB->quote($a_node["tree"])." ".
			"AND pos_fk = ".$ilDB->quote($a_node["pos_pk"])." ".
			"AND parent_pos = ".$ilDB->quote($a_node["parent"])."";
		$res = $this->ilias->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$a_node["lft"] = $row->lft;
			$a_node["rgt"] = $row->rgt;
		}

		$diff = $a_node["rgt"] - $a_node["lft"] + 1;		
		
		// get data of posts
		$query = "SELECT * FROM frm_posts_tree ".
				 "WHERE lft BETWEEN ".$ilDB->quote($a_node["lft"])." AND ".$ilDB->quote($a_node["rgt"])." ".
				 "AND thr_fk = ".$ilDB->quote($a_node["tree"])."";
		$result = $this->ilias->db->query($query);
		
		$del_id = array();
		
		while ($treeData = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$del_id[] = $treeData["pos_fk"];
		}
		
		// delete subtree
		$query = "DELETE FROM frm_posts_tree ".
				 "WHERE lft BETWEEN ".$ilDB->quote($a_node["lft"])." AND ".$ilDB->quote($a_node["rgt"])." ".
				 "AND thr_fk = ".$ilDB->quote($a_node["tree"])."";
		$this->ilias->db->query($query);		

		// close gaps
		$query = "UPDATE frm_posts_tree SET ".
				 "lft = CASE ".
				 "WHEN lft > ".$ilDB->quote($a_node["lft"])." ".
				 "THEN lft - ".$ilDB->quote($diff)." ".
				 "ELSE lft ".
				 "END, ".
				 "rgt = CASE ".
				 "WHEN rgt > ".$ilDB->quote($a_node["lft"])." ".
				 "THEN rgt - ".$ilDB->quote($diff)." ".
				 "ELSE rgt ".
				 "END ".
				 "WHERE thr_fk = ".$ilDB->quote($a_node["tree"])."";
		$this->ilias->db->query($query);
		
		return $del_id;
	}

	/**
	* update page hits of given forum- or thread-ID
	* @access	public
	* @param	integer	
	*/
	function updateVisits($ID)
	{
		$checkTime = time() - (60*60);
		
		if ($_SESSION["frm_visit_".$this->dbTable."_".$ID] < $checkTime)
		{
			$_SESSION["frm_visit_".$this->dbTable."_".$ID] = time();		
		
			$q = "UPDATE ".$this->dbTable." SET ";
			$q .= "visits = visits + 1 ";
			$q .= "WHERE ( ".$this->whereCondition." )";			
			
			$this->ilias->db->query($q);
		}
		
		$this->setWhereCondition("1");
	}

	/**
	* prepares given string
	* @access	public
	* @param	string	
	* @param	integer
	* @return	string
	*/
	function prepareText($text, $edit=0, $quote_user = "", $type = '')
	{
		global $lng;
		
		if ($type == 'export')
		{
			$this->replQuote1 = "<blockquote class=\"quote\"><hr size=\"1\" color=\"#000000\">"; 
			$this->replQuote2 = "<hr size=\"1\" color=\"#000000\"/></blockquote>"; 
		}

		if ($edit == 1)
		{
			// add login name of quoted users
			$lname = ($quote_user != "")
				? '="'.$quote_user.'"'
				: "";

			$text = "[quote$lname]".$text."[/quote]";
		}
		else
		{
			// check for quotation
			$startZ = substr_count ($text, "[quote");	// also count [quote="..."]
			$endZ = substr_count ($text, "[/quote]");

			if ($startZ > 0 || $endZ > 0)
			{
				// add missing opening and closing tags
				if ($startZ > $endZ)
				{
					$diff = $startZ - $endZ;

					for ($i = 0; $i < $diff; $i++)
					{
						if ($type == 'export') $text .= $this->txtQuote2;
						else $text .= "[/quote]";
					}
				}
				elseif ($startZ < $endZ)
				{
					$diff = $endZ - $startZ;

					for ($i = 0; $i < $diff; $i++)
					{
						if ($type == 'export') $text .= $this->txtQuote1;
						else $text = "[quote]".$text;
					}
				}

				if ($edit == 0)
				{
					$ws= "[ \t\r\f\v\n]*";
					
					$text = eregi_replace("\[(quote$ws=$ws\"([^\"]*)\"$ws)\]",
						$this->replQuote1.'<div class="ilForumQuoteHead">'.$lng->txt("quote")." (\\2)".'</div>', $text);

					$text = str_replace("[quote]",
						$this->replQuote1.'<div class="ilForumQuoteHead">'.$lng->txt("quote").'</div>', $text);
					$text = str_replace("[/quote]", $this->replQuote2, $text);
				}
			}
		}
		
		if ($type != 'export')
		{		
			// this removes real slashes of the content (e.g. in latex code)
			//$text = stripslashes($text);		
			if ($edit == 0)
			{
				$text = ilUtil::insertLatexImages($text);
			}
			
	/*		if ($edit == 2)
			{
				$text = stripslashes($text);
			}*/
	
			// workaround for preventing template engine
			// from hiding text that is enclosed
			// in curly brackets (e.g. "{a}")
			$text = str_replace("{", "&#123;", $text);
			$text = str_replace("}", "&#125;", $text);
		}

		return $text;
	}


	/**
	* get one post-dataset 
	* @param    integer post id 
	* @return	array result dataset of the post
	* @access	public
	*/
	function getModeratorFromPost($pos_pk)
	{
		global $ilDB;
		
		$q = "SELECT frm_data.* FROM frm_data, frm_posts WHERE ";
		$q .= "pos_pk = ".$ilDB->quote($pos_pk)." AND ";
		$q .= "pos_top_fk = top_pk";

		$result = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);

		return $result;
	}

	function __deletePostFiles($a_ids)
	{
		if(!is_array($a_ids))
		{
			return false;
		}
		include_once "./Modules/Forum/classes/class.ilFileDataForum.php";
		
		$tmp_file_obj =& new ilFileDataForum($this->getForumId());
		foreach($a_ids as $pos_id)
		{
			$tmp_file_obj->setPosId($pos_id);
			$files = $tmp_file_obj->getFilesOfPost();
			foreach($files as $file)
			{
				$tmp_file_obj->unlinkFile($file["name"]);
			}
		}
		unset($tmp_file_obj);
		return true;
	}


	function __sendMessage($a_parent_pos, $post_data = array())
	{
		global $ilUser, $ilDB;
		
		$parent_data = $this->getOnePost($a_parent_pos);
				
		// only if the current user is not the owner of the parent post and the parent's notification flag is set...
		if($parent_data["notify"] && $parent_data["pos_usr_id"] != $ilUser->getId())
		{
			// SEND MESSAGE
			include_once "Services/Mail/classes/class.ilMail.php";
			include_once './Services/User/classes/class.ilObjUser.php';

			$tmp_user =& new ilObjUser($parent_data["pos_usr_id"]);

			// NONSENSE
			$this->setWhereCondition("thr_pk = ".$ilDB->quote($parent_data["pos_thr_fk"])."");
			$thread_data = $this->getOneThread();

			#var_dump("<pre>",$thread_data,"<pre>");
			#var_dump("<pre>",$parent_data,"<pre>");
			$tmp_mail_obj = new ilMail($_SESSION["AccountId"]);
			$message = $tmp_mail_obj->sendMail($tmp_user->getLogin(),"","",
											   $this->__formatSubject($thread_data),
											   $this->__formatMessage($thread_data, $post_data),
											   array(),array("system"));

			unset($tmp_user);
			unset($tmp_mail_obj);
		}
	}
	
	function __formatSubject($thread_data)
	{
		return $this->lng->txt("forums_notification_subject");		
	}
	
	function __formatMessage($thread_data, $post_data = array())
	{
		include_once "./classes/class.ilObjectFactory.php";

		
		$frm_obj =& ilObjectFactory::getInstanceByRefId($this->getForumRefId());
		$title = $frm_obj->getTitle();
		unset($frm_obj);
		
		$message = $this->lng->txt("forum").": ".$title." -> ".$thread_data["thr_subject"]."\n\n";
		$message .= $this->lng->txt("forum_post_replied");
		
		$message .= "\n------------------------------------------------------------\n";
		$message .= $post_data["pos_message"];
		$message .= "\n------------------------------------------------------------\n";
		$message .= sprintf($this->lng->txt("forums_notification_show_post"), "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["PHP_SELF"])."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"].'&client_id='.CLIENT_ID);
		
		return $message;
	}

	function getUserData($a_id, $a_import_name = 0)
	{
		global $lng, $ilDB;

		if($a_id && ilObject::_exists($a_id) && ilObjectFactory::getInstanceByObjId($a_id,false))
		{
			$query = "SELECT * FROM usr_data WHERE usr_id = ".$ilDB->quote($a_id)."";
			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$tmp_array["usr_id"] = $row->usr_id;
				$tmp_array["login"]  = $row->login;
				$tmp_array["firstname"]  = $row->firstname;
				$tmp_array["lastname"]  = $row->lastname;
				$tmp_array["public_profile"] = ilObjUser::_lookupPref($a_id, "public_profile");
				$tmp_array["create_date"]  = $row->create_date;
			}
			return $tmp_array ? $tmp_array : array();
		}
		else
		{
			$login = $a_import_name ? $a_import_name." (".$lng->txt("imported").")" : $lng->txt("unknown");

			return array("usr_id" => 0, "login" => $login, "firstname" => "", "lastname" => "");
		}
	}


	function getImportName()
	{
		return $this->import_name;
	}
	function setImportName($a_import_name)
	{
		$this->import_name = $a_import_name;
	}

	/**
	* Enable a user's notification about new posts in this forum
	* @param    integer	user_id	A user's ID
	* @return	bool	true
	* @access	private
	*/
	function enableForumNotification($user_id)
	{
		global $ilDB;
		
		if (!$this->isForumNotificationEnabled($user_id))
		{
			/* Remove all notifications of threads that belong to the forum */ 
			$q = "SELECT frm_notification.thread_id FROM frm_data, frm_notification, frm_threads WHERE " .
					"frm_notification.user_id = ".$ilDB->quote($user_id)." AND " .
					"frm_notification.thread_id = frm_threads.thr_pk AND " .
					"frm_threads.thr_top_fk = frm_data.top_pk AND " .
					"frm_data.top_frm_fk = ".$this->id." " .
					"GROUP BY frm_notification.thread_id";

			$res = $this->ilias->db->query($q);

			if (is_object($res) &&
				$res->numRows() > 0)
			{
				$thread_ids = "";
				while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
				{								
					$thread_ids .= $row["thread_id"].",";
				}
				$thread_ids = substr($thread_ids, 0, strlen($thread_ids)-1);
				$q = "DELETE FROM frm_notification WHERE " .
						"user_id = ".$ilDB->quote($user_id)." AND " .
						"thread_id IN (".$thread_ids.")";
				$this->ilias->db->query($q);
			}

			/* Insert forum notification */ 
			$q = "INSERT INTO frm_notification (user_id, frm_id) VALUES (";
			$q .= $ilDB->quote($user_id).", ";
			$q .= $ilDB->quote($this->id).")";
			$this->ilias->db->query($q);
		}

		return true;
	}

	/**
	* Disable a user's notification about new posts in this forum
	* @param    integer	user_id	A user's ID
	* @return	bool	true
	* @access	private
	*/
	function disableForumNotification($user_id)
	{
		global $ilDB;
		
		$q = "DELETE FROM frm_notification WHERE ";
		$q .= "user_id = ".$ilDB->quote($user_id)." AND ";
		$q .= "frm_id = ".$ilDB->quote($this->id)."";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Check whether a user's notification about new posts in this forum is enabled (result > 0) or not (result == 0)
	* @param    integer	user_id	A user's ID
	* @return	integer	Result
	* @access	private
	*/
	function isForumNotificationEnabled($user_id)
	{
		global $ilDB;
		
		$query = $ilDB->prepare("SELECT COUNT(*) AS cnt FROM frm_notification WHERE user_id = ? AND frm_id = ?",
		         	array("integer", "integer"));
		$result = $ilDB->execute($query, array($user_id, $this->id));		
		while($record = $ilDB->fetchAssoc($result))
		{
			return (bool)$record['cnt'];
		}
		
		return false;
	}

	/**
	* Enable a user's notification about new posts in a thread
	* @param    integer	user_id	A user's ID
	* @param    integer	thread_id	ID of the thread
	* @return	bool	true
	* @access	private
	*/
	function enableThreadNotification($user_id, $thread_id)
	{
		global $ilDB;
		
		if (!$this->isThreadNotificationEnabled($user_id, $thread_id))
		{
			$q = "INSERT INTO frm_notification (user_id, thread_id) VALUES (";
			$q .= $ilDB->quote($user_id).", ";
			$q .= $ilDB->quote($thread_id).")";
			$this->ilias->db->query($q);
		}

		return true;
	}

	/**
	* Check whether a user's notification about new posts in a thread is enabled (result > 0) or not (result == 0)
	* @param    integer	user_id	A user's ID
	* @param    integer	thread_id	ID of the thread
	* @return	integer	Result
	* @access	private
	*/
	function isThreadNotificationEnabled($user_id, $thread_id)
	{
		global $ilDB;
		
		$query = $ilDB->prepare("SELECT COUNT(*) AS cnt FROM frm_notification WHERE user_id = ? AND thread_id = ?",
		         	array("integer", "integer"));
		$result = $ilDB->execute($query, array($user_id, $thread_id));		
		while($record = $ilDB->fetchAssoc($result))
		{
			return (bool)$record['cnt'];
		}
		
		return false;
	}

	function sendThreadNotifications($post_data)
	{
		global $ilDB;
		
		include_once "Services/Mail/classes/class.ilMail.php";
		include_once './Services/User/classes/class.ilObjUser.php';
		
		// GET THREAD DATA		
		$query = $ilDB->prepare("SELECT thr_subject FROM frm_threads WHERE thr_pk = ?",
			     	array('integer'));
			
		$result = $ilDB->execute($query, array($post_data['pos_thr_fk']));			
		while($record = $ilDB->fetchAssoc($result))
		{
			$post_data['thr_subject'] = $record['thr_subject'];
			break;
		}

		// GET AUTHOR OF NEW POST
		if(ilForumProperties::getInstance(self::_lookupObjIdForForumId($post_data['pos_top_fk']))->isAnonymized())
		{
			$post_data['pos_usr_name'] = $post_data['pos_usr_alias'];
		}
		else
		{
			$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
		}
		if($post_data['pos_usr_name'] == '')
		{
			$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
		}

		// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
		$q = "SELECT user_id FROM frm_notification WHERE ";
		$q .= "thread_id = ".$ilDB->quote($post_data["pos_thr_fk"])." AND ";
		$q .= "user_id <> ".$ilDB->quote($_SESSION["AccountId"])."";
		$res = $this->ilias->db->query($q);
		
		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{								
			// SEND NOTIFICATIONS BY E-MAIL
			$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row["user_id"]),"","",
											   $this->formatNotificationSubject(),
											   $this->formatNotification($post_data),
											   array(),array("system"));
		}
	}
	
	function sendForumNotifications($post_data)
	{
		global $ilDB;
		
		include_once "Services/Mail/classes/class.ilMail.php";
		include_once './Services/User/classes/class.ilObjUser.php';
		
		// GET THREAD DATA
		$query = $ilDB->prepare("SELECT thr_subject FROM frm_threads WHERE thr_pk = ?",
			     	array('integer'));
			
		$result = $ilDB->execute($query, array($post_data['pos_thr_fk']));			
		while($record = $ilDB->fetchAssoc($result))
		{
			$post_data['thr_subject'] = $record['thr_subject'];
			break;
		}

		// GET AUTHOR OF NEW POST
		if(ilForumProperties::getInstance(self::_lookupObjIdForForumId($post_data['pos_top_fk']))->isAnonymized())
		{
			$post_data['pos_usr_name'] = $post_data['pos_usr_alias'];
		}
		else
		{
			$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
		}
		if($post_data['pos_usr_name'] == '')
		{
			$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
		}

		// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
		$q = "SELECT frm_notification.user_id FROM frm_notification, frm_data WHERE ";
		$q .= "frm_data.top_pk = ".$ilDB->quote($post_data["pos_top_fk"])." AND ";
		$q .= "frm_notification.frm_id = frm_data.top_frm_fk AND ";
		$q .= "frm_notification.user_id <> ".$ilDB->quote($_SESSION["AccountId"])." ";
		$q .= "GROUP BY frm_notification.user_id";
		$res = $this->ilias->db->query($q);
		
		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{								
			// SEND NOTIFICATIONS BY E-MAIL			
			$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row["user_id"]),"","",
											   $this->formatNotificationSubject(),
											   $this->formatNotification($post_data),
											   array(),array("system"));
		}
	}
	
	function formatPostActivationNotificationSubject()
	{
		return $this->lng->txt('forums_notification_subject');
	}
	
	function formatPostActivationNotification($post_data)
	{		
		$message = sprintf($this->lng->txt('forums_notification_intro'),
								$this->ilias->ini->readVariable('client', 'name'),
								ILIAS_HTTP_PATH)."\n\n";
		
		$message .= $this->lng->txt("forum").": ".$post_data["top_name"]."\n\n";
		$message .= $this->lng->txt("thread").": ".$post_data["thr_subject"]."\n\n";
		$message .= $this->lng->txt("new_post").":\n------------------------------------------------------------\n";
		$message .= $this->lng->txt("author").": ".$post_data["pos_usr_name"]."\n";
		$message .= $this->lng->txt("date").": ".$post_data["pos_date"]."\n";
		$message .= $this->lng->txt("subject").": ".$post_data["pos_subject"]."\n\n";
		if ($post_data["pos_cens"] == 1)
		{
			$message .= $post_data["pos_cens_com"]."\n";
		}
		else
		{
			$message .= $post_data["pos_message"]."\n";
		}
		$message .= "------------------------------------------------------------\n";
	
		$message .= sprintf($this->lng->txt('forums_notification_show_post'), ILIAS_HTTP_PATH."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"]."_".$post_data["pos_pk"].'&client_id='.CLIENT_ID);


		return $message;
	}
	
	function sendPostActivationNotification($post_data)
	{		
		global $ilDB, $ilUser;
		
		if (is_array($moderators = $this->getModerators()))
		{
			// GET THREAD DATA
			$query = $ilDB->prepare("SELECT thr_subject FROM frm_threads WHERE thr_pk = ?",
			     	 	array('integer'));
			
			$result = $ilDB->execute($query, array($post_data['pos_thr_fk']));			
			while($record = $ilDB->fetchAssoc($result))
			{
				$post_data['thr_subject'] = $record['thr_subject'];
				break;
			}
	
			// GET AUTHOR OF NEW POST
			$post_data["pos_usr_name"] = ilObjUser::_lookupLogin($post_data["pos_usr_id"]);
			
			$subject = $this->formatPostActivationNotificationSubject();
			$message = $this->formatPostActivationNotification($post_data);
			
			$mail_obj = new ilMail(ANONYMOUS_USER_ID);
			foreach ($moderators as $moderator)
			{
				$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($moderator), '', '',
												   $subject,
												   $message,
												   array(), array("system"));
			}
		}
	}

	function formatNotificationSubject()
	{
		return $this->lng->txt("forums_notification_subject");
	}

	function formatNotification($post_data, $cron = 0)
	{
		global $ilIliasIniFile;

		if ($cron == 1)
		{
			$message = sprintf($this->lng->txt("forums_notification_intro"),
								$this->ilias->ini->readVariable("client","name"),
								$ilIliasIniFile->readVariable("server","http_path"))."\n\n";
		}
		else
		{
			$message = sprintf($this->lng->txt("forums_notification_intro"),
								$this->ilias->ini->readVariable("client","name"),
								ILIAS_HTTP_PATH)."\n\n";
		}
		$message .= $this->lng->txt("forum").": ".$post_data["top_name"]."\n\n";
		$message .= $this->lng->txt("thread").": ".$post_data["thr_subject"]."\n\n";
		$message .= $this->lng->txt("new_post").":\n------------------------------------------------------------\n";
		$message .= $this->lng->txt("author").": ".$post_data["pos_usr_name"]."\n";
		$message .= $this->lng->txt("date").": ".$post_data["pos_date"]."\n";
		$message .= $this->lng->txt("subject").": ".$post_data["pos_subject"]."\n\n";
		if ($post_data["pos_cens"] == 1)
		{
			$message .= $post_data["pos_cens_com"]."\n";
		}
		else
		{
			$message .= $post_data["pos_message"]."\n";
		}
		$message .= "------------------------------------------------------------\n";
		if ($cron == 1)
		{
			$message .= sprintf($this->lng->txt("forums_notification_show_post"), $ilIliasIniFile->readVariable("server","http_path")."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"]."_".$post_data["pos_pk"].'&client_id='.CLIENT_ID);
		}
		else
		{
			$message .= sprintf($this->lng->txt("forums_notification_show_post"), ILIAS_HTTP_PATH."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"]."_".$post_data["pos_pk"].'&client_id='.CLIENT_ID);
		}

		return $message;
	}
	
	/**
	 * Get thread infos of object
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id of forum
	 * @param int sort mode SORT_TITLE or SORT_DATE
	 */
	public static function _getThreads($a_obj_id,$a_sort_mode = self::SORT_DATE)
	{
		global $ilDB;
		
		switch($a_sort_mode)
		{
			case self::SORT_DATE:
				$sort = 'thr_date';
				break;
			
			case self::SORT_TITLE:
			default:
				$sort = 'thr_subject';
				break;
		}
		
		$query = "SELECT * FROM frm_threads JOIN frm_data ON top_pk = thr_top_fk ".
			"WHERE top_frm_fk = ".$ilDB->quote($a_obj_id)." ".
			"ORDER BY ".$sort;
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$threads[$row->thr_pk] = $row->thr_subject;
		}
		return $threads ? $threads : array();
	}	
	
	public function limitMsgString($message)
	{		
		// limit the message-size
		$message = $this->prepareText($message, 2);

		if (strpos($message, $this->txtQuote2) > 0)
		{
			
			$viewPos = strrpos($message, $this->txtQuote2) + strlen($this->txtQuote2);
			$message = substr($message, $viewPos);				
		}
		
		if (strlen($message) > 40)
		{
			$message = substr($message, 0, 37).'...';
		}
		
		return $this->prepareText($message);
	}
		
	function _lookupObjIdForForumId($a_for_id)
	{
		global $ilDB;
		
		$forum = $ilDB->query("SELECT * FROM frm_data ".
				" WHERE top_pk = ".$ilDB->quote($a_for_id));
		if ($fdata = $forum->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $fdata["top_frm_fk"];
		}
		return false;
	}

} // END class.Forum
