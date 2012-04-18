<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	public $ilias;
	public $lng;
	
	/**
	* database table name
	* @var string
	* @see setDbTable(), getDbTable()
	* @access private
	*/
	private $dbTable;
	
	/**
	* class name
	* @var string class name
	* @access private
	*/
	private $className="ilForum";
	
	/**
	* database table field for sorting the results
	* @var string
	* @see setOrderField()
	* @access private
	*/
	private $orderField;
	
	private $mdb2Query;
	private $mdb2DataValue;
	private $mdb2DataType;
	
	private $txtQuote1 = "[quote]";
	private $txtQuote2 = "[/quote]";
	private $replQuote1 = '<blockquote class="ilForumQuote">';
	private $replQuote2 = '</blockquote>';
	
	// max. datasets per page
	private $pageHits = 30;

	// object id
	private $id;
	
	/**
	* Constructor
	* @access	public
	*/
	public function __construct()
	{
		global $ilias,$lng;

		$this->ilias = $ilias;
		$this->lng = $lng;
	}

	public function setLanguage($lng)
	{
		$this->lng = $lng;
	}
	
	/**
	 * 
	 * Get the ilLanguage instance for the passed user id
	 * 
	 * @param	integer	$usr_id	a user id
	 * @return	ilLanguage
	 * @access	public
	 * @static
	 * 
	 */
	public static function _getLanguageInstanceByUsrId($usr_id)
	{
		static $lngCache = array();
		
		$languageShorthandle = ilObjUser::_lookupLanguage($usr_id);
		
		// lookup in cache array
		if(!isset($lngCache[$languageShorthandle]))
		{
			$lngCache[$languageShorthandle] = new ilLanguage($languageShorthandle);
			$lngCache[$languageShorthandle]->loadLanguageModule('forum');
		}
		
		return $lngCache[$languageShorthandle];
	}

	/**
	* set object id which refers to ILIAS obj_id
	* @param	integer	object id
	* @access	public
	*/
	public function setForumId($a_obj_id)
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
	public function setForumRefId($a_ref_id)
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
	public function getForumId()
	{
		return $this->id;
	}
	
	/**
	* get forum ref_id
	* @access	public
	* @return	integer	reference id of forum
	*/
	public function getForumRefId()
	{
		return $this->ref_id;
	}
	
	/**
	* set database field for sorting results
	* @param	string	$orderField database field for sorting
	* @see				$orderField
	* @access	private
	*/
	private function setOrderField($orderField)
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
	public function getOrderField()
	{
		return $this->orderField;
	}
	
	/**
	* set database table
	* @param	string	$dbTable database table
	* @see				$dbTable
	* @access	public
	*/
	public function setDbTable($dbTable)
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
	public function getDbTable()
	{
		return $this->dbTable;
	}
	
	
	/**
	 * set content for additional condition
	 *
	 * @param string $query_string
	 * @param array $data_type
	 * @param array $data_value
	 * 
	 */	
	
	public function setMDB2WhereCondition($query_string, $data_type, $data_value)
	{
		
		$this->mdb2Query = $query_string;
		$this->mdb2DataValue = $data_value;
		$this->mdb2DataType = $data_type;
	
		
		return true;
	}	
	

	/**
	// get content of additional condition
	 *
	 * @return string 
	 */	
	public function getMDB2Query()
	{
		if($this->mdb2Query != '')
		{
			return $this->mdb2Query;
		}
		
	}
	
	/**
	 /* get content of additional condition
	 *
	 * @return array 
	 */		
	public function getMDB2DataValue()
	{
		if($this->mdb2DataValue != '')
		{
			return $this->mdb2DataValue;
		}
	}

	/**
	 * get content of additional condition
	 *
	 * @return array 
	 */	
	public function getMDB2DataType()
	{
		if($this->mdb2DataType != '')
		{
			return $this->mdb2DataType;
		}
	}
	
	/**
	* set number of max. visible datasets
	* @param	integer	$pageHits 
	* @see				$pageHits
	* @access	public
	*/
	public function setPageHits($pageHits)
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
	public function getPageHits()
	{
		return $this->pageHits;
	}
	
	// *******************************************************************************

	/**
	* get one topic-dataset by WhereCondition
	* @return	array	$result dataset of the topic
	* @access	public
	*/
	public function getOneTopic()
	{
		global $ilDB;
		
		$data_type = array();
		$data_value = array();
		
		$query = 'SELECT * FROM frm_data WHERE ';
		
		if($this->getMDB2Query() != '' && $this->getMDB2DataType() != '' && $this->getMDB2DataValue() != '')
		{
			$query .= ''.$this->getMDB2Query().'';
			$data_type = $data_type + $this->getMDB2DataType();
			$data_value = $data_value + $this->getMDB2DataValue();

			$res = $ilDB->queryf($query, $data_type, $data_value);			
			$row = $ilDB->fetchAssoc($res);
			
			if(is_null($row)) return NULL;
			
			$row["top_name"] = trim($row["top_name"]);
			$row["top_description"] = nl2br($row["top_description"]);

			return $row;
			
		}
		else
		{			
			$query .= '1 = 1';
		
			$res = $ilDB->query($query);			
			$row = $ilDB->fetchAssoc($res);
			
			if(!is_array($row) || !count($row)) return null;
			
			$row['top_name'] = trim($row['top_name']);
			$row['top_description'] = nl2br($row['top_description']);
	
			return $row;			
		}
	}

	/**
	* get one thread-dataset by WhereCondition
	* @return	array	$result dataset of the thread
	* @access	public
	*/
	public function getOneThread()
	{	
		global $ilDB;
			
		$data_type = array();
		$data_value = array();
		
		$query = 'SELECT * FROM frm_threads WHERE ';
		
		if($this->getMDB2Query() != '' && $this->getMDB2DataType() != '' && $this->getMDB2DataValue() != '')
		{
			$query .= $this->getMDB2Query();
			$data_type = $data_type + $this->getMDB2DataType();
			$data_value = $data_value + $this->getMDB2DataValue();
			
			$sql_res = $ilDB->queryf($query, $data_type, $data_value);
			$result = $sql_res->fetchRow(DB_FETCHMODE_ASSOC);
			$result["thr_subject"] = trim($result["thr_subject"]);
		}

		return $result;
	}
	
	/**
	* get one post-dataset 
	* @param    integer post id 
	* @return	array result dataset of the post
	* @access	public
	*/
	public function getOnePost($post)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data 
			WHERE pos_pk = %s
			AND pos_usr_id = usr_id',
			array('integer'), array($post));

		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		
		$row["pos_date"] = $this->convertDate($row["pos_date"]);		
		$row["pos_message"] = nl2br($row["pos_message"]);
					
		return $row;
	}

	public function _lookupPostMessage($a_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM frm_posts WHERE pos_pk = %s',
			array('integer'), array($a_id));
		
		while($row = $ilDB->fetchObject($res))
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
	public function generatePost($forum_id, $thread_id, $user, $message, $parent_pos, $notify, $subject = '', $alias = '', $date = '', $status = 1, $send_activation_mail = 0)
	{
		global $ilUser, $ilDB;
	
		$objNewPost = new ilForumPost();
		$objNewPost->setForumId($forum_id);
		$objNewPost->setThreadId($thread_id);
		$objNewPost->setSubject($subject);
		$objNewPost->setMessage($message);
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
		$result = $ilDB->manipulateF('
			UPDATE frm_threads 
			SET thr_num_posts = thr_num_posts + 1,
				thr_last_post = %s
			WHERE thr_pk = %s',
			array('text', 'integer'),
			array($lastPost, $objNewPost->getThreadId()));
		
		// update forum
		$result = $ilDB->manipulateF('
			UPDATE frm_data 
			SET top_num_posts = top_num_posts + 1,
			 	top_last_post = %s
			WHERE top_pk = %s',
			array('text', 'integer'),
			array($lastPost, $objNewPost->getForumId()));
		
		// MARK READ
		$forum_obj = ilObjectFactory::getInstanceByRefId($this->getForumRefId());
		$forum_obj->markPostRead($objNewPost->getUserId(), $objNewPost->getThreadId(), $objNewPost->getId());
		
		$pos_data = $objNewPost->getDataAsArray();
		$pos_data["ref_id"] = $this->getForumRefId();

		// FINALLY SEND MESSAGE

		if ($this->ilias->getSetting("forum_notification") == 1 && (int)$status )
		{
			$GLOBALS["frm_notifications_sent"] = array();
			$this->__sendMessage($parent_pos, $pos_data);

			$pos_data["top_name"] = $forum_obj->getTitle();			
			$this->sendForumNotifications($pos_data);
			$this->sendThreadNotifications($pos_data);
			unset($GLOBALS["frm_notifications_sent"]);
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
			require_once 'Services/RTE/classes/class.ilRTE.php';
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$news_item = new ilNewsItem();
			$news_item->setContext($forum_obj->getId(), 'frm', $objNewPost->getId(), 'pos');
			$news_item->setPriority(NEWS_NOTICE);
			$news_item->setTitle($objNewPost->getSubject());
			$news_item->setContent(ilRTE::_replaceMediaObjectImageSrc($this->prepareText($objNewPost->getMessage(), 0), 1));
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
	public function generateThread($forum_id, $user, $subject, $message, $notify, $notify_posts, $alias = '', $date = '')
	{	
		global $ilDB;

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
		$statement = $ilDB->manipulateF('
			UPDATE frm_data 
			SET top_num_threads = top_num_threads + 1
			WHERE top_pk = %s',
			array('integer'), array($forum_id));
		
		return $this->generatePost($forum_id, $objNewThread->getId(), $user, $message, 0, $notify, $subject, $alias, $objNewThread->getCreateDate());
	}
	
	/**
	* Moves all chosen threads and their posts to a new forum
	* 
	* @param    array	chosen thread pks
	* @param    integer	object id of src forum
	* @param    integer	object id of dest forum
	* @access	public
	*/
	public function moveThreads($thread_ids = array(), $src_ref_id = 0, $dest_top_frm_fk = 0)
	{	
		global $ilDB;
		
		$src_top_frm_fk = ilObject::_lookupObjectId($src_ref_id);		
		
		if (is_numeric($src_top_frm_fk) && $src_top_frm_fk > 0 && is_numeric($dest_top_frm_fk) && $dest_top_frm_fk > 0)
		{	

			$this->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($src_top_frm_fk));
			
			$oldFrmData = $this->getOneTopic();			

			$this->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($dest_top_frm_fk));	
					
			$newFrmData = $this->getOneTopic();
			
			if ($oldFrmData['top_pk'] && $newFrmData['top_pk'])
			{
				$moved_posts = 0;
				$moved_threads = 0;
				$visits = 0;
				foreach ($thread_ids as $id)
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
				$ilDB->setLimit(1);
				$res = $ilDB->queryf('
					SELECT pos_thr_fk, pos_pk 
					FROM frm_posts						  
					WHERE pos_top_fk = %s
					ORDER BY pos_date DESC',
					array('integer'), array($oldFrmData['top_pk']));
				
				$row = $res->fetchRow(DB_FETCHMODE_OBJECT);				
				$last_post_src = $oldFrmData['top_pk'] . '#' . $row->pos_thr_fk . '#' . $row->pos_pk;
				
				$statement = $ilDB->manipulateF('
					UPDATE frm_data
					SET top_num_posts = top_num_posts - %s,
						top_num_threads = top_num_threads - %s,
						visits = visits - %s,
						top_last_post = %s
					WHERE top_pk = %s',
					array('integer', 'integer', 'integer', 'text', 'integer'), 
					array(	$moved_posts, 
							$moved_threads, 
							$visits, 
							$last_post_src, 
							$oldFrmData['top_pk']));
				
				// update frm_data destination forum
				
				$ilDB->setLimit(1);
				$res = $ilDB->queryf('
					SELECT pos_thr_fk, pos_pk 
				 	FROM frm_posts						  
					WHERE pos_top_fk = %s
					ORDER BY pos_date DESC',
					array('integer'), array($newFrmData['top_kp']));
				
				$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
				$last_post_dest = $newFrmData['top_pk'] . '#' . $row->pos_thr_fk . '#' . $row->pos_pk;

				$statement = $ilDB->manipulateF('
					UPDATE frm_data
					SET top_num_posts = top_num_posts + %s,
						top_num_threads = top_num_threads + %s,
						visits = visits + %s,
						top_last_post = %s
						WHERE top_pk = %s',
					array('integer', 'integer', 'integer', 'text', 'integer'),
					array($moved_posts, $moved_threads, $visits, $last_post_dest, $newFrmData['top_pk']));
				
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
	public function postCensorship($message, $pos_pk, $cens = 0)
	{		
		global $ilDB;

		$statement = $ilDB->manipulateF('
			UPDATE frm_posts
			SET pos_cens_com = %s,
				pos_update = %s,
				pos_cens = %s,
				update_user = %s
			WHERE pos_pk = %s',
			array('text', 'timestamp', 'integer', 'integer', 'integer'),
			array($message, date("Y-m-d H:i:s"), $cens, $_SESSION['AccountId'], $pos_pk));
		
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
				$res = $ilDB->queryf('
					SELECT * FROM frm_posts
					WHERE pos_pk = %s',
					array('integer'), array($pos_pk));
					
				$rec = $res->fetchRow(DB_FETCHMODE_ASSOC);

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
	public function deletePost($post)
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

			$statement = $ilDB->manipulateF('
				DELETE FROM frm_threads
				WHERE thr_pk = %s',
				array('integer'), array($p_node['tree']));

			// update num_threads
			$statement = $ilDB->manipulateF('
				UPDATE frm_data 
				SET top_num_threads = top_num_threads - 1 
				WHERE top_frm_fk = %s',
				array('integer'), array($this->id));
			
			// delete all related news
			$posset = $ilDB->queryf('
				SELECT * FROM frm_posts
				WHERE pos_thr_fk = %s',
				array('integer'), array($p_node['tree']));
			
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
				
				try
				{
					include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
					$mobs = ilObjMediaObject::_getMobsOfObject('frm:html', $posrec['pos_pk']);
					foreach($mobs as $mob)
					{						
						if(ilObjMediaObject::_exists($mob))
						{
							ilObjMediaObject::_removeUsage($mob, 'frm:html', $posrec['pos_pk']);
							$mob_obj = new ilObjMediaObject($mob);
							$mob_obj->delete();
						}
					}
				}
				catch(Exception $e)
				{
				}
			}
			
			
			// delete all posts of this thread
			$statement = $ilDB->manipulateF('
				DELETE FROM frm_posts
				WHERE pos_thr_fk = %s',
				array('integer'), array($p_node['tree']));
			
		}
		else
		{

			// delete this post and its sub-posts
			for ($i = 0; $i < $dead_pos; $i++)
			{
				$statement = $ilDB->manipulateF('
					DELETE FROM frm_posts
					WHERE pos_pk = %s',
					array('integer'), array($del_id[$i]));
				
				// delete related news item
				include_once("./Services/News/classes/class.ilNewsItem.php");
				$news_id = ilNewsItem::getFirstNewsIdForContext($this->id,
					"frm", $del_id[$i], "pos");
				if ($news_id > 0)
				{
					$news_item = new ilNewsItem($news_id);
					$news_item->delete();
				}
				
				try
				{
					include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
					$mobs = ilObjMediaObject::_getMobsOfObject('frm:html', $del_id[$i]);
					foreach($mobs as $mob)
					{						
						if(ilObjMediaObject::_exists($mob))
						{
							ilObjMediaObject::_removeUsage($mob, 'frm:html', $del_id[$i]);
							$mob_obj = new ilObjMediaObject($mob);
							$mob_obj->delete();
						}
					}
				}
				catch(Exception $e)
				{
				}
			}
			
			// update num_posts in frm_threads
			$statement = $ilDB->manipulateF('
				UPDATE frm_threads
				SET thr_num_posts = thr_num_posts - %s
				WHERE thr_pk = %s',
				array('integer', 'integer'),
				array($dead_pos, $p_node['tree']));
			
			
			// get latest post of thread and update last_post
			$res1 = $ilDB->queryf('
				SELECT * FROM frm_posts 
				WHERE pos_thr_fk = %s
				ORDER BY pos_date DESC',
				array('integer'), array($p_node['tree']));
			
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
			
			$statement = $ilDB->manipulateF('
				UPDATE frm_threads
				SET thr_last_post = %s
				WHERE thr_pk = %s',
				array('text', 'integer'), array($lastPost_thr, $p_node['tree']));
		}
		
		// update num_posts in frm_data
		$statement = $ilDB->manipulateF('
			UPDATE frm_data
			SET top_num_posts = top_num_posts - %s
			WHERE top_frm_fk = %s',
			array('integer', 'integer'), array($dead_pos, $this->id));
		
		
		// get latest post of forum and update last_post
		$res2 = $ilDB->queryf('
			SELECT * FROM frm_posts, frm_data 
			WHERE pos_top_fk = top_pk 
			AND top_frm_fk = %s
			ORDER BY pos_date DESC',
			array('integer'), array($this->id));
		
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
		
		$statement = $ilDB->manipulateF('
			UPDATE frm_data
			SET top_last_post = %s
			WHERE top_frm_fk = %s',
			array('text', 'integer'), array($lastPost_top, $this->id));
		
		return $dead_thr;		
	}

	/**
	 * @param $a_topic_id
	 * @param bool $is_moderator
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getAllThreads($a_topic_id, $is_moderator = false, $limit = 0, $offset = 0)
	{
		global $ilDB, $ilUser;

		// Count all threads for the passed forum
		$query = 'SELECT COUNT(thr_pk) cnt
				  FROM frm_threads
				  WHERE thr_top_fk = %s';
		$res = $ilDB->queryF($query, array('integer'), array($a_topic_id));
		$data = $ilDB->fetchAssoc($res);
		$cnt = (int) $data['cnt'];

		$threads = array();

		$data = array();
		$data_types = array();

		$active_query = '';
		if(!$is_moderator)
		{
			$active_query = ' AND (pos_status = %s OR pos_usr_id = %s) ';
		}

		$query = "SELECT
				  (CASE WHEN COUNT(DISTINCT(notification_id)) > 0 THEN 1 ELSE 0 END) usr_notification_is_enabled,
				  MAX(pos_date) post_date,
				  COUNT(DISTINCT(pos_pk)) num_posts,
				  
				  COUNT(DISTINCT(pos_pk)) - COUNT(DISTINCT(postread.post_id)) num_unread_posts,
				  
				  (
				  	SELECT COUNT(pos_pk) cnt
					FROM frm_posts
					LEFT JOIN frm_user_read ON post_id = pos_pk AND usr_id = %s
					WHERE pos_thr_fk = frm_threads.thr_pk
					AND (pos_date > ".$ilDB->fromUnixtime('access_last')." OR pos_update > ".$ilDB->fromUnixtime('access_last').") 
					AND pos_usr_id != %s
					$active_query
					AND usr_id IS NULL
				  
				  ) num_new_posts,
				  
				  thr_pk, thr_top_fk, thr_subject, thr_usr_id, thr_usr_alias, thr_num_posts, thr_last_post, thr_date, thr_update, visits, frm_threads.import_name, is_sticky, is_closed
				  
				  FROM frm_threads
				  
				  LEFT JOIN frm_notification
				  	ON frm_notification.thread_id = thr_pk
				  	AND frm_notification.user_id = %s
				  
				  LEFT JOIN frm_thread_access
				  	ON frm_thread_access.thread_id = thr_pk
				  	AND frm_thread_access.usr_id = %s
				  
				  LEFT JOIN frm_posts
				  	ON pos_thr_fk = thr_pk $active_query
				  
				  LEFT JOIN frm_user_read postread
				  	ON postread.post_id = pos_pk
				  	AND postread.usr_id = %s
		";

		$data_types[] = 'integer';
		if(!$is_moderator)
		{
			array_push($data_types, 'integer', 'integer');
		}
		$data_types[] = 'integer';
		$data_types[] = 'integer';
		$data_types[] = 'integer';

		$data[] = $ilUser->getId();
		if(!$is_moderator)
		{
			array_push($data, '1', $ilUser->getId());
		}
		$data[] = $ilUser->getId();
		$data[] = $ilUser->getId();
		$data[] = $ilUser->getId();

		if(!$is_moderator)
		{
			array_push($data_types, 'integer', 'integer');
			array_push($data, '1', $ilUser->getId());
		}

		$data_types[] = 'integer';
		$data[] = $ilUser->getId();

		$query .= ' WHERE thr_top_fk = %s
				    GROUP BY thr_pk, thr_top_fk, thr_subject, thr_usr_id, thr_usr_alias, thr_num_posts, thr_last_post, thr_date, thr_update, visits, frm_threads.import_name, is_sticky, is_closed
				    ORDER BY is_sticky DESC, post_date DESC, thr_date DESC';

		array_push($data_types, 'integer');
		array_push($data, $a_topic_id);

		if($limit || $offset)
		{
			$ilDB->setLimit($limit, $offset);
		}
		$res = $ilDB->queryF($query, $data_types, $data);
		while($row = $ilDB->fetchAssoc($res))
		{
			$thread = new ilForumTopic($row['thr_pk'], $is_moderator, true);
			$thread->assignData($row);
			$threads[] = $thread;
		}

		return array(
			'items' => $threads,
			'cnt' => $cnt
		);
	}
	
	public function getUserStatistic($is_moderator = false)
	{
		global $ilDB, $ilUser;
		
		$statistic = array();
		
		$data_types = array();
		$data = array();
		
		$query = "SELECT COUNT(f.pos_usr_id) ranking, u.login, p.value, u.lastname, u.firstname
	 				FROM frm_posts f
						INNER JOIN frm_posts_tree t
							ON f.pos_pk = t.pos_fk
						INNER JOIN frm_threads th
							ON t.thr_fk = th.thr_pk
						INNER JOIN usr_data u
							ON u.usr_id = f.pos_usr_id
						INNER JOIN frm_data d
							ON d.top_pk = f.pos_top_fk
						LEFT JOIN usr_pref p
							ON p.usr_id = u.usr_id AND p.keyword = %s
					WHERE 1 = 1";
	
		array_push($data_types, 'text');
		array_push($data, 'public_profile');

		if (!$is_moderator) 
		{
			$query .= ' AND (pos_status = %s
						OR (pos_status = %s
						AND pos_usr_id = %s ))';
			
			array_push($data_types,'integer', 'integer', 'integer');
			array_push($data, '1', '0', $ilUser->getId());
		}
		
		$query .= ' AND d.top_frm_fk = %s
					GROUP BY pos_usr_id, u.login, p.value,u.lastname, u.firstname';

		array_push($data_types,'integer');
		array_push($data, $this->getForumId());
			
		$res = $ilDB->queryf($query, $data_types, $data);
		
		$counter = 0;
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
		    $statistic[$counter][] = $row['ranking'];
		    $statistic[$counter][] = $row['login'];
		    $statistic[$counter][] = ($row['value'] != 'y' ? '' : $row['lastname']);
		    $statistic[$counter][] = ($row['value'] != 'y' ? '' : $row['firstname']);
		    
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

		$res = $ilDB->queryf('
			SELECT * FROM frm_posts_tree 
			WHERE thr_fk = %s
			AND parent_pos = %s',
			array('integer', 'integer'), array($a_thread_id, '0'));

		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->pos_fk ? $row->pos_fk : 0;
	}

	/**
	* get all users assigned to local role il_frm_moderator_<frm_ref_id>
	*
	* @return	array	user_ids
	* @access	public
   	*/
	public function getModerators()
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
	* @static
	* @param	int		$a_ref_id	reference id
	* @param	int		$a_usr_id	user id
	* @return	bool
	* @access	public
	*/
	public static function _isModerator($a_ref_id, $a_usr_id)
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
	public function countUserArticles($a_user_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM frm_data
			INNER JOIN frm_posts ON pos_top_fk = top_pk 
			WHERE top_frm_fk = %s
			AND pos_usr_id = %s',
			array('integer', 'integer'),
			array($this->getForumId(), $a_user_id));
		
		return $res->numRows();
	}	
	
	public function countActiveUserArticles($a_user_id)
	{
		global $ilDB, $ilUser;

		$res = $ilDB->queryf('
			SELECT * FROM frm_data
			INNER JOIN frm_posts ON pos_top_fk = top_pk
			WHERE top_frm_fk = %s
			AND (pos_status = %s
				OR (pos_status = %s 
					AND pos_usr_id = %s
					)
				)	   
			AND pos_usr_id = %s',
			array('integer', 'integer', 'integer', 'integer', 'integer'),
			array($this->getForumId(),'1', '0', $ilUser->getId(), $a_user_id));
		
		return $res->numRows();
	}

	/**
	 * converts the date format
	 * @param	string	$date
	 * @return	string	formatted datetime
	 * @access	public
	 */
	public function convertDate($date)
	{
		return ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_DATETIME));
	}
	
	/**
	* create a new post-tree
	* @param	integer		a_tree_id: id where tree belongs to
	* @param	integer		a_node_id: root node of tree (optional; default is tree_id itself)
	* @return	boolean		true on success
	* @access	public
	*/
	public function addPostTree($a_tree_id, $a_node_id = -1, $a_date = '')
	{
		global $ilDB;
		
		$a_date = $a_date ? $a_date : date("Y-m-d H:i:s");
		
		if ($a_node_id <= 0)
		{
			$a_node_id = $a_tree_id;
		}
		
		$nextId = $ilDB->nextId('frm_posts_tree');
		
		$statement = $ilDB->manipulateF('
			INSERT INTO frm_posts_tree
			( 	fpt_pk,
				thr_fk,
				pos_fk,
				parent_pos,
				lft,
				rgt,
				depth,
				fpt_date
			)
			VALUES(%s, %s, %s, %s,  %s,  %s, %s, %s )',
			array('integer','integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'timestamp'),
			array($nextId, $a_tree_id, $a_node_id, '0', '1', '2', '1', $a_date));		
		
		return true;
	}
	
	/**
	* insert node under parent node
	* @access	public
	* @param	integer		node_id
	* @param	integer		tree_id
	* @param	integer		parent_id (optional)
	*/
	public function insertPostNode($a_node_id, $a_parent_id, $tree_id, $a_date = '')
	{		
		global $ilDB;

		$a_date = $a_date ? $a_date : date("Y-m-d H:i:s");
		
		// get left value
		$sql_res = $ilDB->queryf('
			SELECT * FROM frm_posts_tree
			WHERE pos_fk = %s
			AND thr_fk = %s',
			array('integer', 'integer'),
			array($a_parent_id, $tree_id));
		
		$res = $sql_res->fetchRow(DB_FETCHMODE_OBJECT);
		
		$left = $res->lft;

		$lft = $left + 1;
		$rgt = $left + 2;

		// spread tree
		$statement = $ilDB->manipulateF('
			UPDATE frm_posts_tree 
			SET  lft = CASE 
				 WHEN lft > %s
				 THEN lft + 2 
				 ELSE lft 
				 END, 
				 rgt = CASE 
				 WHEN rgt > %s
				 THEN rgt + 2 
				 ELSE rgt 
				 END 
				 WHERE thr_fk = %s',
			array('integer', 'integer', 'integer'),
			array($left, $left, $tree_id));
		
		$depth = $this->getPostDepth($a_parent_id, $tree_id) + 1;
	
		// insert node
		$nextId = $ilDB->nextId('frm_posts_tree');
		$statement = $ilDB->manipulateF('
			INSERT INTO frm_posts_tree
			(	fpt_pk,
				thr_fk,
				pos_fk,
				parent_pos,
				lft,
				rgt,
				depth,
				fpt_date
			)
			VALUES(%s,%s,%s, %s, %s, %s,%s, %s)',
			array('integer','integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'timestamp'),
			array(	$nextId,
					$tree_id, 
					$a_node_id, 
					$a_parent_id,
					$lft,
					$rgt,
					$depth,
					$a_date)
		);
		
	}

	/**
	* Return depth of an object
	* @access	private
	* @param	integer		node_id of parent's node_id
	* @param	integer		node_id of parent's node parent_id
	* @return	integer		depth of node
	*/
	public function getPostDepth($a_node_id, $tree_id)
	{
		global $ilDB;

		if ($tree_id)
		{
			$sql_res = $ilDB->queryf('
				SELECT depth FROM frm_posts_tree
				WHERE pos_fk = %s
				AND thr_fk = %s',
				array('integer', 'integer'),
				array($a_node_id, $tree_id));
			
			$res = $sql_res->fetchRow(DB_FETCHMODE_OBJECT);
			
			return $res->depth;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	* get data of the first node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		tree id	
	* @return	object		db result object
	*/
	public function getFirstPostNode($tree_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM frm_posts, frm_posts_tree 
			WHERE pos_pk = pos_fk 
			AND parent_pos = %s
			AND thr_fk = %s',
			array('integer', 'integer'),
			array('0', $tree_id));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $this->fetchPostNodeData($row);
	}

	/**
	* get data of given node from frm_posts_tree and frm_posts
	* @access	public
	* @param	integer		post_id	
	* @return	object		db result object
	*/
	public function getPostNode($post_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM frm_posts, frm_posts_tree 
			WHERE pos_pk = pos_fk 
			AND pos_pk = %s',
			array('integer'),
			array($post_id));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return $this->fetchPostNodeData($row);
	}

	/**
	* get data of parent node from frm_posts_tree and frm_posts
	* @access	private
 	* @param	object	db	db result object containing node_data
	* @return	array		2-dim (int/str) node_data
	*/
	public function fetchPostNodeData($a_row)
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
				//	"date"			=> $a_row->date,
					"date"			=> $a_row->fpt_date,
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
	* delete node and the whole subtree under this node
	* @access	public
	* @param	array	node_data of a node
	* @return	array	ID's of deleted posts
	*/
	public function deletePostTree($a_node)
	{
		global $ilDB;
		
		// GET LEFT AND RIGHT VALUES
		$res = $ilDB->queryf('
			SELECT * FROM frm_posts_tree
			WHERE thr_fk = %s 
			AND pos_fk = %s
			AND parent_pos = %s',
			array('integer', 'integer', 'integer'), 
			array($a_node['tree'], $a_node['pos_pk'], $a_node['parent']));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$a_node["lft"] = $row->lft;
			$a_node["rgt"] = $row->rgt;
		}

		$diff = $a_node["rgt"] - $a_node["lft"] + 1;		
		
		// get data of posts
		$result = $ilDB->queryf('
			SELECT * FROM frm_posts_tree 
			WHERE lft BETWEEN %s AND %s
			AND thr_fk = %s',
			array('integer', 'integer', 'integer'),
			array($a_node['lft'], $a_node['rgt'], $a_node['tree']));
		
		$del_id = array();
		
		while ($treeData = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$del_id[] = $treeData["pos_fk"];
		}
		
		// delete subtree
		$statement = $ilDB->manipulateF('
			DELETE FROM frm_posts_tree
			WHERE lft BETWEEN %s AND %s
			AND thr_fk = %s',
			array('integer', 'integer', 'integer'),
			array($a_node['lft'], $a_node['rgt'], $a_node['tree']));

		
		// close gaps
		$statement = $ilDB->manipulateF('
			UPDATE frm_posts_tree 
			SET lft = CASE 
						WHEN lft > %s
						THEN lft - %s
						ELSE lft 
						END, 
				rgt = CASE 
						WHEN rgt > %s
						THEN rgt - %s
						ELSE rgt 
						END 
			WHERE thr_fk = %s',
			array('integer', 'integer', 'integer', 'integer', 'integer'),
			array($a_node['lft'], $diff, $a_node['lft'], $diff, $a_node['tree']));
		
		return $del_id;

	}

	/**
	* update page hits of given forum- or thread-ID
	* @access	public
	* @param	integer	
	*/
	public function updateVisits($ID)
	{

		global $ilDB;
		
		$checkTime = time() - (60*60);
			
		if ($_SESSION["frm_visit_".$this->dbTable."_".$ID] < $checkTime)
		{
		
			$_SESSION["frm_visit_".$this->dbTable."_".$ID] = time();		
			$query = 'UPDATE '.$this->dbTable.' SET visits = visits + 1 WHERE ';
			
			$data_type = array();
			$data_value = array();
		
			if($this->getMDB2Query() != '' && $this->getMDB2DataType() != '' && $this->getMDB2DataValue() != '')
			{
				$query .= $this->getMDB2Query();
				$data_type = $data_type + $this->getMDB2DataType();
				$data_value = $data_value + $this->getMDB2DataValue();

				$res = $ilDB->queryf($query, $data_type, $data_value);
			}
		}
	}

	/**
	* prepares given string
	* @access	public
	* @param	string	
	* @param	integer
	* @return	string
	*/
	public function prepareText($text, $edit=0, $quote_user = '', $type = '')
	{
		global $lng; 
		
		if($type == 'export')
		{
			$this->replQuote1 = "<blockquote class=\"quote\"><hr size=\"1\" color=\"#000000\">"; 
			$this->replQuote2 = "<hr size=\"1\" color=\"#000000\"/></blockquote>"; 
		}

		if($edit == 1)
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
						if ($type == 'export') $text = $this->txtQuote1.$text;
						else $text = "[quote]".$text;
					}
				}

				if($edit == 0)
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
		
		if($type != 'export')
		{
			if($edit == 0)
			{
				$text = ilUtil::insertLatexImages($text, "\<span class\=\"latex\">", "\<\/span>");
				$text = ilUtil::insertLatexImages($text, "\[tex\]", "\[\/tex\]");
			}
			
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
	public function getModeratorFromPost($pos_pk)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT frm_data.* FROM frm_data, frm_posts 
			WHERE pos_pk = %s
			AND pos_top_fk = top_pk',
			array('integer'), array($pos_pk));
		
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		return $row;
		
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
			$this->setMDB2WhereCondition('thr_pk = %s ', array('integer'), array($parent_data["pos_thr_fk"]));

			$thread_data = $this->getOneThread();

			$tmp_mail_obj = new ilMail(ANONYMOUS_USER_ID);
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
		include_once "./Services/Object/classes/class.ilObjectFactory.php";
		
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
				
			$res = $ilDB->queryf('
				SELECT frm_notification.thread_id FROM frm_data, frm_notification, frm_threads 
				WHERE frm_notification.user_id = %s
				AND frm_notification.thread_id = frm_threads.thr_pk 
				AND frm_threads.thr_top_fk = frm_data.top_pk 
				AND frm_data.top_frm_fk = %s
				GROUP BY frm_notification.thread_id',
				array('integer', 'integer'),
				array($user_id, $this->id));
			
			if (is_object($res) && $res->numRows() > 0)
			{
				$thread_data = array();
				$thread_data_types = array();				

				$query = ' DELETE FROM frm_notification 
							WHERE user_id = %s 
							AND thread_id IN (';
				
				array_push($thread_data, $user_id);
				array_push($thread_data_types, 'integer');
				
				$counter = 1;

				while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
				{	
					if($counter < $res->numRows())
					{	
						$query .= '%s, ';
						array_push($thread_data, $row['thread_id']);
						array_push($thread_data_types, 'integer');
					}
			
					if($counter == $res->numRows())
					{
						$query .= '%s)';
						array_push($thread_data, $row['thread_id']);
						array_push($thread_data_types, 'integer');
						
					}
					$counter++;
				}

				$statement = $ilDB->manipulateF($query, $thread_data_types, $thread_data);
			}

			/* Insert forum notification */ 

			$nextId = $ilDB->nextId('frm_notification');
			
			$statement = $ilDB->manipulateF('
				INSERT INTO frm_notification
				( 	notification_id,
					user_id, 
					frm_id
				)
				VALUES(%s, %s, %s)',
				array('integer','integer', 'integer'),
				array($nextId, $user_id, $this->id));
		
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
		
		$statement = $ilDB->manipulateF('
			DELETE FROM frm_notification 
			WHERE user_id = %s
			AND frm_id = %s',
			array('integer', 'integer'),
			array($user_id, $this->id));
		
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

		$result = $ilDB->queryf('SELECT COUNT(*) cnt FROM frm_notification WHERE user_id = %s AND frm_id = %s',
		    array('integer', 'integer'), array($user_id, $this->id));
		 
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
			$nextId = $ilDB->nextId('frm_notification');
			$statement = $ilDB->manipulateF('
				INSERT INTO frm_notification
				(	notification_id,
					user_id,
					thread_id
				)
				VALUES (%s, %s, %s)',
				array('integer', 'integer', 'integer'), array($nextId, $user_id, $thread_id));
			
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

		$result = $ilDB->queryf('
			SELECT COUNT(*) cnt FROM frm_notification 
			WHERE user_id = %s 
			AND thread_id = %s',
			array('integer', 'integer'),
			array($user_id, $thread_id));		         	

				
		while($record = $ilDB->fetchAssoc($result))
		{
			return (bool)$record['cnt'];
		}
		
		return false;
	}

	function sendThreadNotifications($post_data)
	{
		global $ilDB, $ilAccess, $lng;
		
		include_once "Services/Mail/classes/class.ilMail.php";
		include_once './Services/User/classes/class.ilObjUser.php';

		// GET THREAD DATA		
		$result = $ilDB->queryf('
			SELECT thr_subject FROM frm_threads 
			WHERE thr_pk = %s',
			array('integer'), array($post_data['pos_thr_fk']));
			
		while($record = $ilDB->fetchAssoc($result))
		{
			$post_data['thr_subject'] = $record['thr_subject'];
			break;
		}
		
		// determine obj_id of the forum
		$obj_id = self::_lookupObjIdForForumId($post_data['pos_top_fk']);

		// GET AUTHOR OF NEW POST
		if($post_data['pos_usr_id'])
		{
			$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
		}
		else if(strlen($post_data['pos_usr_alias']))
		{
			$post_data['pos_usr_name'] = $post_data['pos_usr_alias'].' ('.$lng->txt('frm_pseudonym').')';
		}
		
		if($post_data['pos_usr_name'] == '')
		{
			$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
		}

		// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
		$res = $ilDB->queryf('
			SELECT user_id FROM frm_notification 
			WHERE thread_id = %s
			AND user_id <> %s',
			array('integer', 'integer'),
			array($post_data['pos_thr_fk'], $_SESSION['AccountId']));
		
		// get all references of obj_id
		$frm_references = ilObject::_getAllReferences($obj_id);
		
		// save language of the current user
		global $lng;
		$userLanguage = $lng;

		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if(is_array($GLOBALS["frm_notifications_sent"]) &
			   in_array($row['user_id'], $GLOBALS["frm_notifications_sent"]))
			{
				continue;
			}

			// do rbac check before sending notification
			$send_mail = false;			
			foreach((array)$frm_references as $ref_id)
			{
				if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
				{
					$send_mail = true;
					break;
				}
			}
			
			if($send_mail)
			{
				// set forum language instance for earch user
				$this->setLanguage(self::_getLanguageInstanceByUsrId($row['user_id']));
				
				// SEND NOTIFICATIONS BY E-MAIL
				$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row["user_id"]),"","",
												   $this->formatNotificationSubject($post_data),
												   strip_tags($this->formatNotification($post_data)),
												  array(),array("system"));
			}
		}
		
		// reset language
		$this->setLanguage($userLanguage);
	}
	
	function sendForumNotifications($post_data)
	{
		global $ilDB, $ilAccess, $lng, $ilUser;
		
		include_once "Services/Mail/classes/class.ilMail.php";
		include_once './Services/User/classes/class.ilObjUser.php';
		
		// GET THREAD DATA
		$result = $ilDB->queryf('
			SELECT thr_subject FROM frm_threads 
			WHERE thr_pk = %s',
			array('integer'), 
			array($post_data['pos_thr_fk']));
			
		while($record = $ilDB->fetchAssoc($result))
		{
			$post_data['thr_subject'] = $record['thr_subject'];
			break;
		}
				
		// determine obj_id of the forum
		$obj_id = self::_lookupObjIdForForumId($post_data['pos_top_fk']);

		// GET AUTHOR OF NEW POST
		if($post_data['pos_usr_id'])
		{
			$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
		}
		else if(strlen($post_data['pos_usr_alias']))
		{
			$post_data['pos_usr_name'] = $post_data['pos_usr_alias'].' ('.$lng->txt('frm_pseudonym').')';
		}
		
		if($post_data['pos_usr_name'] == '')
		{
			$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
		}

		// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
		$res = $ilDB->queryf('
			SELECT frm_notification.user_id FROM frm_notification, frm_data 
			WHERE frm_data.top_pk = %s
			AND frm_notification.frm_id = frm_data.top_frm_fk 
			AND frm_notification.user_id <> %s
			GROUP BY frm_notification.user_id',
			array('integer', 'integer'),
			array($post_data['pos_top_fk'], $_SESSION['AccountId']));
		
		// get all references of obj_id
		$frm_references = ilObject::_getAllReferences($obj_id);
		
		// save language of the current user
		global $lng;
		$userLanguage = $lng;
		
		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{			
			if(is_array($GLOBALS["frm_notifications_sent"]) &
			   in_array($row['user_id'], $GLOBALS["frm_notifications_sent"]))
			{
				continue;
			}

			// do rbac check before sending notification
			$send_mail = false;			
			foreach((array)$frm_references as $ref_id)
			{
				if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
				{
					$send_mail = true;
					break;
				}
			}
			
			if($send_mail)
			{
				// set forum language instance for earch user
				$this->setLanguage(self::_getLanguageInstanceByUsrId($row['user_id']));
				
				// SEND NOTIFICATIONS BY E-MAIL			
				$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row["user_id"]),"","",
												   $this->formatNotificationSubject($post_data),
												   strip_tags($this->formatNotification($post_data)),
												   array(),array("system"));
			
			}
		}
		
		// reset language
		$this->setLanguage($userLanguage);
	}
	
	function formatPostActivationNotificationSubject()
	{
		return $this->lng->txt('forums_notification_subject');
	}
	
	function formatPostActivationNotification($post_data)
	{		
		$message = sprintf($this->lng->txt('forums_notification_intro'),
								$this->ilias->ini->readVariable('client', 'name'),
								ILIAS_HTTP_PATH.'/?client_id='.CLIENT_ID)."\n\n";

		$message .= $this->lng->txt('forums_post_activation_mail')."\n\n";
		
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
		global $ilDB, $ilUser, $lng;
		
		if (is_array($moderators = $this->getModerators()))
		{
			// GET THREAD DATA
			$result = $ilDB->queryf('
				SELECT thr_subject FROM frm_threads 
				WHERE thr_pk = %s',
			    array('integer'),
			    array($post_data['pos_thr_fk']));
			    
			while($record = $ilDB->fetchAssoc($result))
			{
				$post_data['thr_subject'] = $record['thr_subject'];
				break;
			}
	
			// GET AUTHOR OF NEW POST
			if($post_data['pos_usr_id'])
			{
				$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
			}
			else if(strlen($post_data['pos_usr_alias']))
			{
				$post_data['pos_usr_name'] = $post_data['pos_usr_alias'].' ('.$lng->txt('frm_pseudonym').')';
			}
			
			if($post_data['pos_usr_name'] == '')
			{
				$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
			}
			
			// save language of the current user
			global $lng;
			$userLanguage = $lng;
			
			$mail_obj = new ilMail(ANONYMOUS_USER_ID);
			foreach ($moderators as $moderator)		
			{
				// set forum language instance for earch user
				$this->setLanguage(self::_getLanguageInstanceByUsrId($moderator));
				
				$subject = $this->formatPostActivationNotificationSubject();
				$message = $this->formatPostActivationNotification($post_data);
				
				$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($moderator), '', '',
												   $subject,
												   strip_tags($message),
												   array(), array("system"));
			}
			
			// reset language
			$this->setLanguage($userLanguage);
		}
	}

	function formatNotificationSubject($post_data)
	{
		return $this->lng->txt("forums_notification_subject").' '.$post_data['top_name'];
	}

	function formatNotification($post_data, $cron = 0)
	{
		global $ilIliasIniFile;

		if ($cron == 1)
		{
			$message = sprintf($this->lng->txt("forums_notification_intro"),
								$this->ilias->ini->readVariable("client","name"),
								$ilIliasIniFile->readVariable("server","http_path").'/?client_id='.CLIENT_ID)."\n\n";
		}
		else
		{
			$message = sprintf($this->lng->txt("forums_notification_intro"),
								$this->ilias->ini->readVariable("client","name"),
								ILIAS_HTTP_PATH.'/?client_id='.CLIENT_ID)."\n\n";
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
		
		$res = $ilDB->queryf('
			SELECT * FROM frm_threads 
			JOIN frm_data ON top_pk = thr_top_fk 
			WHERE top_frm_fk = %s
			ORDER BY %s',
			array('integer', 'text'), array($a_obj_id, $sort));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$threads[$row->thr_pk] = $row->thr_subject;
		}
		return $threads ? $threads : array();
	}
		
	function _lookupObjIdForForumId($a_for_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT top_frm_fk FROM frm_data
			WHERE top_pk = %s',
			array('integer'), array($a_for_id));
		
		if ($fdata = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $fdata["top_frm_fk"];
		}
		return false;
	}

} // END class.Forum
