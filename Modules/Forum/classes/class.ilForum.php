<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    const DEFAULT_PAGE_HITS = 30;

    /**
     * @var array
     */
    protected static $moderators_by_ref_id_map = array();

    public $lng;
    public $error;
    public $db;
    public $user;
    public $settings;
    
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
    private $className = "ilForum";
    
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
    private $pageHits = self::DEFAULT_PAGE_HITS;

    // object id
    private $id;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->error = $DIC['ilErr'];
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
    }

    // no usage?
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
        if (!isset($lngCache[$languageShorthandle])) {
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
        if (!isset($a_obj_id)) {
            $message = get_class($this) . "::setForumId(): No obj_id given!";
            $this->error->raiseError($message, $this->error->WARNING);
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
        if (!isset($a_ref_id)) {
            $message = get_class($this) . "::setForumRefId(): No ref_id given!";
            $this->error->raiseError($message, $this->error->WARNING);
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
        if ($orderField == "") {
            die($this->className . "::setOrderField(): No orderField given.");
        } else {
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
        if ($dbTable == "") {
            die($this->className . "::setDbTable(): No database table given.");
        } else {
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
     * @return bool
     */
    public function setMDB2WhereCondition($query_string, $data_type, $data_value)
    {
        $this->mdb2Query = $query_string;
        $this->mdb2DataValue = $data_value;
        $this->mdb2DataType = $data_type;
        
        return true;
    }
    
    /**
     * get content of additional condition
     * @return string
     */
    public function getMDB2Query()
    {
        if ($this->mdb2Query != '') {
            return $this->mdb2Query;
        }
    }
    
    /**
     * get content of additional condition
     * @return array
     */
    public function getMDB2DataValue()
    {
        if ($this->mdb2DataValue != '') {
            return $this->mdb2DataValue;
        }
    }

    /**
     * get content of additional condition
     * @return array
     */
    public function getMDB2DataType()
    {
        if ($this->mdb2DataType != '') {
            return $this->mdb2DataType;
        }
    }

    /**
     * @param int $pageHits
     * @return bool
     */
    public function setPageHits($pageHits)
    {
        if ($pageHits < 1 || !is_numeric($pageHits)) {
            $pageHits = 1;
        }

        $this->pageHits = (int) $pageHits;
        return true;
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
    
    /**
    * get one topic-dataset by WhereCondition
    * @return	array	$result dataset of the topic
    * @access	public
    */
    public function getOneTopic()
    {
        $data_type = array();
        $data_value = array();
        
        $query = 'SELECT * FROM frm_data WHERE ';
        
        if ($this->getMDB2Query() != '' && $this->getMDB2DataType() != '' && $this->getMDB2DataValue() != '') {
            $query .= '' . $this->getMDB2Query() . '';
            $data_type = $data_type + $this->getMDB2DataType();
            $data_value = $data_value + $this->getMDB2DataValue();

            $res = $this->db->queryf($query, $data_type, $data_value);
            $row = $this->db->fetchAssoc($res);
            
            if (is_null($row)) {
                return null;
            }
            
            $row["top_name"] = trim($row["top_name"]);
            $row["top_description"] = nl2br($row["top_description"]);

            return $row;
        } else {
            $query .= '1 = 1';
        
            $res = $this->db->query($query);
            $row = $this->db->fetchAssoc($res);
            
            if (!is_array($row) || !count($row)) {
                return null;
            }
            
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
        $data_type = array();
        $data_value = array();
        
        $query = 'SELECT * FROM frm_threads WHERE ';
        
        if ($this->getMDB2Query() != '' && $this->getMDB2DataType() != '' && $this->getMDB2DataValue() != '') {
            $query .= $this->getMDB2Query();
            $data_type = $data_type + $this->getMDB2DataType();
            $data_value = $data_value + $this->getMDB2DataValue();
            
            $sql_res = $this->db->queryf($query, $data_type, $data_value);
            $result = $this->db->fetchAssoc($sql_res);
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
        $res = $this->db->queryf(
            '
			SELECT frm_posts.*, usr_data.lastname FROM frm_posts, usr_data 
			WHERE pos_pk = %s
			AND pos_display_user_id = usr_id',
            array('integer'),
            array($post)
        );

        $row = $this->db->fetchAssoc($res);
        
        $row["pos_date"] = $this->convertDate($row["pos_date"]);
        $row["pos_message"] = nl2br($row["pos_message"]);
                    
        return $row;
    }

    public static function _lookupPostMessage($a_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryf(
            '
			SELECT * FROM frm_posts WHERE pos_pk = %s',
            array('integer'),
            array($a_id)
        );
        
        while ($row = $ilDB->fetchObject($res)) {
            return $row->pos_message;
        }
        return '';
    }

    /**
    * generate new dataset in frm_posts
     * @param int    $forum_id
     * @param int    $thread_id
     * @param int    $author_id
     * @param int    $display_user_id
     * @param string $message
     * @param int    $parent_pos
     * @param int    $notify
     * @param string $subject
     * @param string $alias
     * @param string $date datetime|timestamp
     * @param int    $status
     * @param int    $send_activation_mail
     * @return int   new post_id
     */
    public function generatePost($forum_id, $thread_id, $author_id, $display_user_id, $message, $parent_pos, $notify, $subject = '', $alias = '', $date = '', $status = 1, $send_activation_mail = 0)
    {
        $objNewPost = new ilForumPost();
        $objNewPost->setForumId($forum_id);
        $objNewPost->setThreadId($thread_id);
        $objNewPost->setSubject($subject);
        $objNewPost->setMessage($message);
        $objNewPost->setDisplayUserId($display_user_id);
        $objNewPost->setUserAlias($alias);
        $objNewPost->setPosAuthorId($author_id);
        
        $frm_settings = ilForumProperties::getInstance($this->getForumId());
        
        if ($frm_settings->getMarkModeratorPosts() == 1) {
            self::_isModerator($this->getForumRefId(), $author_id) ? $is_moderator = true : $is_moderator = false;
        } else {
            $is_moderator = false;
        }
        $objNewPost->setIsAuthorModerator($is_moderator);
        
        if ($date == "") {
            $objNewPost->setCreateDate(date("Y-m-d H:i:s"));
        } else {
            if (strpos($date, "-") > 0) {		// in mysql format
                $objNewPost->setCreateDate($date);
            } else {								// a timestamp
                $objNewPost->setCreateDate(date("Y-m-d H:i:s", $date));
            }
        }
        if ($status == 1) {
            $objNewPost->setPostActivationDate($objNewPost->getCreateDate());
        }

        $objNewPost->setImportName($this->getImportName());
        $objNewPost->setNotification($notify);
        $objNewPost->setStatus($status);
        $objNewPost->insert();
        
        // entry in tree-table
        if ($parent_pos == 0) {
            $this->addPostTree($objNewPost->getThreadId(), $objNewPost->getId(), $objNewPost->getCreateDate());
        } else {
            $this->insertPostNode($objNewPost->getId(), $parent_pos, $objNewPost->getThreadId(), $objNewPost->getCreateDate());
        }
        
        // string last post
        $lastPost = $objNewPost->getForumId() . "#" . $objNewPost->getThreadId() . "#" . $objNewPost->getId();
            
        // update thread
        $this->db->manipulateF(
            '
			UPDATE frm_threads 
			SET thr_num_posts = thr_num_posts + 1,
				thr_last_post = %s
			WHERE thr_pk = %s',
            array('text', 'integer'),
            array($lastPost, $objNewPost->getThreadId())
        );
        
        // update forum
        $this->db->manipulateF(
            '
			UPDATE frm_data 
			SET top_num_posts = top_num_posts + 1,
			 	top_last_post = %s
			WHERE top_pk = %s',
            array('text', 'integer'),
            array($lastPost, $objNewPost->getForumId())
        );
        
        // MARK READ
        $forum_obj = ilObjectFactory::getInstanceByRefId($this->getForumRefId());
        $forum_obj->markPostRead($objNewPost->getPosAuthorId(), $objNewPost->getThreadId(), $objNewPost->getId());

        // Add Notification to news
        if ($status && $parent_pos > 0) {
            $news_item = new ilNewsItem();
            $news_item->setContext($forum_obj->getId(), 'frm', $objNewPost->getId(), 'pos');
            $news_item->setPriority(NEWS_NOTICE);
            $news_item->setTitle($objNewPost->getSubject());
            $news_item->setContent(ilRTE::_replaceMediaObjectImageSrc($this->prepareText($objNewPost->getMessage(), 0), 1));
            if ($objNewPost->getMessage() != strip_tags($objNewPost->getMessage())) {
                $news_item->setContentHtml(true);
            }
            
            $news_item->setUserId($display_user_id);
            $news_item->setVisibility(NEWS_USERS);
            $news_item->create();
        }

        return $objNewPost->getId();
    }

    /**
     * @param ilForumTopic $thread
     * @param string       $message
     * @param int          $notify
     * @param int          $notify_posts
     * @param int          $status
     * @return int The id of the new posting, created implicitly when creating new threads
     */
    public function generateThread(ilForumTopic $thread, $message, $notify, $notify_posts, $status = 1)
    {
        if (!$thread->getCreateDate()) {
            $thread->setCreateDate(date('Y-m-d H:i:s'));
        }

        $thread->setImportName($this->getImportName());
        $thread->insert();

        if ($notify_posts == 1) {
            $thread->enableNotification($thread->getThrAuthorId());
        }

        $this->db->manipulateF(
            '
			UPDATE frm_data 
			SET top_num_threads = top_num_threads + 1
			WHERE top_pk = %s',
            array('integer'),
            array($thread->getForumId())
        );

        $rootNodeId = $this->generatePost(
            $thread->getForumId(),
            $thread->getId(),
            $thread->getThrAuthorId(),
            $thread->getDisplayUserId(),
            '',
            0,
            0,
            $thread->getSubject(),
            $thread->getUserAlias(),
            $thread->getCreateDate(),
            1,
            0
        );

        return $this->generatePost(
            $thread->getForumId(),
            $thread->getId(),
            $thread->getThrAuthorId(),
            $thread->getDisplayUserId(),
            $message,
            $rootNodeId,
            $notify,
            $thread->getSubject(),
            $thread->getUserAlias(),
            $thread->getCreateDate(),
            $status,
            0
        );
    }

    /**
     * Moves all chosen threads and their posts to a new forum
     *
     * @param    array    chosen thread pks
     * @param    integer    object id of src forum
     * @param    integer    object id of dest forum
     * @access    public
     * @return array
     */
    public function moveThreads($thread_ids = array(), $src_ref_id = 0, $dest_top_frm_fk = 0)
    {
        $src_top_frm_fk = ilObject::_lookupObjectId($src_ref_id);

        $errorMessages = array();

        if (is_numeric($src_top_frm_fk) && $src_top_frm_fk > 0 && is_numeric($dest_top_frm_fk) && $dest_top_frm_fk > 0) {
            $this->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($src_top_frm_fk));
            
            $oldFrmData = $this->getOneTopic();

            $this->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($dest_top_frm_fk));
                    
            $newFrmData = $this->getOneTopic();

            if ($oldFrmData['top_pk'] && $newFrmData['top_pk']) {
                $moved_posts = 0;
                $moved_threads = 0;
                $visits = 0;

                foreach ($thread_ids as $id) {
                    $objTmpThread = new ilForumTopic($id);

                    try {
                        $numPosts = $objTmpThread->movePosts(
                            $src_top_frm_fk,
                            $oldFrmData['top_pk'],
                            $dest_top_frm_fk,
                            $newFrmData['top_pk']
                        );

                        if (($last_post_string = $objTmpThread->getLastPostString()) != '') {
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
                    } catch (\ilFileUtilsException $exception) {
                        $errorMessages[] = sprintf($this->lng->txt('frm_move_invalid_file_type'), $objTmpThread->getSubject());
                        continue;
                    }
                }

                if ($moved_threads > 0 || $moved_posts > 0 || $visits > 0) {
                    // update frm_data source forum
                    $this->db->setLimit(1);
                    $res = $this->db->queryf(
                        '
					SELECT pos_thr_fk, pos_pk 
					FROM frm_posts						  
					WHERE pos_top_fk = %s
					ORDER BY pos_date DESC',
                        array('integer'),
                        array($oldFrmData['top_pk'])
                    );

                    $row = $this->db->fetchObject($res);
                    $last_post_src = $oldFrmData['top_pk'] . '#' . $row->pos_thr_fk . '#' . $row->pos_pk;

                    $this->db->manipulateF(
                        '
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
                            $oldFrmData['top_pk'])
                    );

                    // update frm_data destination forum
                    $this->db->setLimit(1);
                    $res = $this->db->queryf(
                        '
					SELECT pos_thr_fk, pos_pk 
				 	FROM frm_posts						  
					WHERE pos_top_fk = %s
					ORDER BY pos_date DESC',
                        array('integer'),
                        array($newFrmData['top_kp'])
                    );

                    $row = $this->db->fetchObject($res);
                    $last_post_dest = $newFrmData['top_pk'] . '#' . $row->pos_thr_fk . '#' . $row->pos_pk;

                    $this->db->manipulateF(
                        '
					UPDATE frm_data
					SET top_num_posts = top_num_posts + %s,
						top_num_threads = top_num_threads + %s,
						visits = visits + %s,
						top_last_post = %s
						WHERE top_pk = %s',
                        array('integer', 'integer', 'integer', 'text', 'integer'),
                        array($moved_posts, $moved_threads, $visits, $last_post_dest, $newFrmData['top_pk'])
                    );
                }
            }

            return $errorMessages;
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
        $cens_date = date("Y-m-d H:i:s");

        $this->db->manipulateF(
            '
			UPDATE frm_posts
			SET pos_cens_com = %s,
				pos_cens_date = %s,
				pos_cens = %s,
				update_user = %s
			WHERE pos_pk = %s',
            array('text', 'timestamp', 'integer', 'integer', 'integer'),
            array($message, $cens_date, $cens, $GLOBALS['DIC']['ilUser']->getId(), $pos_pk)
        );
        
        // Change news item accordingly
        $news_id = ilNewsItem::getFirstNewsIdForContext(
            $this->id,
            "frm",
            $pos_pk,
            "pos"
        );
        if ($news_id > 0) {
            if ($cens > 0) {		// censor
                $news_item = new ilNewsItem($news_id);
                //$news_item->setTitle($subject);
                $news_item->setContent(nl2br($this->prepareText($message, 0)));
                if ($message != strip_tags($message)) {
                    $news_item->setContentHtml(true);
                } else {
                    $news_item->setContentHtml(false);
                }
                
                $news_item->update();
            } else {				// revoke censorship
                // get original message
                $res = $this->db->queryf(
                    '
					SELECT * FROM frm_posts
					WHERE pos_pk = %s',
                    array('integer'),
                    array($pos_pk)
                );
                    
                $rec = $this->db->fetchAssoc($res);

                $news_item = new ilNewsItem($news_id);
                //$news_item->setTitle($subject);
                $news_item->setContent(nl2br($this->prepareText($rec["pos_message"], 0)));
                if ($rec["pos_message"] != strip_tags($rec["pos_message"])) {
                    $news_item->setContentHtml(true);
                } else {
                    $news_item->setContentHtml(false);
                }
                
                $news_item->update();
            }
        }

        $GLOBALS['ilAppEventHandler']->raise(
            'Modules/Forum',
            'censoredPost',
            array(
                'ref_id' => $this->getForumRefId(),
                'post' => new ilForumPost($pos_pk)
            )
        );

        return true;
    }

    /**
     * delete post and sub-posts
     * @param    integer $post : ID
     * @param bool $raiseEvents
     * @return    integer    0 or thread-ID
     * @access    public
     */
    public function deletePost($post, $raiseEvents = true)
    {
        $p_node = $this->getPostNode($post);

        if ($raiseEvents) {
            $GLOBALS['ilAppEventHandler']->raise(
                'Modules/Forum',
                'deletedPost',
                [
                    'ref_id' => $this->getForumRefId(),
                    'post' => new ilForumPost($post),
                    'thread_deleted' => ($p_node["parent"] == 0) ? true : false
                ]
            );
        }

        // delete tree and get id's of all posts to delete
        $del_id = $this->deletePostTree($p_node);

        // delete drafts_history
        $obj_history = new ilForumDraftsHistory();
        $obj_history->deleteHistoryByPostIds($del_id);
        // delete all drafts
        $obj_draft = new ilForumPostDraft();
        $obj_draft->deleteDraftsByPostIds($del_id);

        // Delete User read entries
        foreach ($del_id as $post_id) {
            ilObjForum::_deleteReadEntries($post_id);
        }

        // DELETE ATTACHMENTS ASSIGNED TO POST
        $this->__deletePostFiles($del_id);
        
        $dead_pos = count($del_id);
        $dead_thr = 0;

        // if deletePost is thread opener ...
        if ($p_node["parent"] == 0) {
            // delete thread access data
            ilObjForum::_deleteAccessEntries($p_node['tree']);

            // delete thread
            $dead_thr = $p_node["tree"];

            $this->db->manipulateF(
                '
				DELETE FROM frm_threads
				WHERE thr_pk = %s',
                array('integer'),
                array($p_node['tree'])
            );

            // update num_threads
            $this->db->manipulateF(
                '
				UPDATE frm_data 
				SET top_num_threads = top_num_threads - 1 
				WHERE top_frm_fk = %s',
                array('integer'),
                array($this->id)
            );
            
            // delete all related news
            $posset = $this->db->queryf(
                '
				SELECT * FROM frm_posts
				WHERE pos_thr_fk = %s',
                array('integer'),
                array($p_node['tree'])
            );
            
            while ($posrec = $this->db->fetchAssoc($posset)) {
                $news_id = ilNewsItem::getFirstNewsIdForContext(
                    $this->id,
                    "frm",
                    $posrec["pos_pk"],
                    "pos"
                );
                if ($news_id > 0) {
                    $news_item = new ilNewsItem($news_id);
                    $news_item->delete();
                }
                
                try {
                    $mobs = ilObjMediaObject::_getMobsOfObject('frm:html', $posrec['pos_pk']);
                    foreach ($mobs as $mob) {
                        if (ilObjMediaObject::_exists($mob)) {
                            ilObjMediaObject::_removeUsage($mob, 'frm:html', $posrec['pos_pk']);
                            $mob_obj = new ilObjMediaObject($mob);
                            $mob_obj->delete();
                        }
                    }
                } catch (Exception $e) {
                }
            }
            
            // delete all posts of this thread
            $this->db->manipulateF(
                '
				DELETE FROM frm_posts
				WHERE pos_thr_fk = %s',
                array('integer'),
                array($p_node['tree'])
            );
        } else {
            // delete this post and its sub-posts
            for ($i = 0; $i < $dead_pos; $i++) {
                $this->db->manipulateF(
                    '
					DELETE FROM frm_posts
					WHERE pos_pk = %s',
                    array('integer'),
                    array($del_id[$i])
                );
                
                // delete related news item
                $news_id = ilNewsItem::getFirstNewsIdForContext(
                    $this->id,
                    "frm",
                    $del_id[$i],
                    "pos"
                );
                if ($news_id > 0) {
                    $news_item = new ilNewsItem($news_id);
                    $news_item->delete();
                }
                
                try {
                    $mobs = ilObjMediaObject::_getMobsOfObject('frm:html', $del_id[$i]);
                    foreach ($mobs as $mob) {
                        if (ilObjMediaObject::_exists($mob)) {
                            ilObjMediaObject::_removeUsage($mob, 'frm:html', $del_id[$i]);
                            $mob_obj = new ilObjMediaObject($mob);
                            $mob_obj->delete();
                        }
                    }
                } catch (Exception $e) {
                }
            }
            
            // update num_posts in frm_threads
            $this->db->manipulateF(
                '
				UPDATE frm_threads
				SET thr_num_posts = thr_num_posts - %s
				WHERE thr_pk = %s',
                array('integer', 'integer'),
                array($dead_pos, $p_node['tree'])
            );
            
            // get latest post of thread and update last_post
            $res1 = $this->db->queryf(
                '
				SELECT * FROM frm_posts 
				WHERE pos_thr_fk = %s
				ORDER BY pos_date DESC',
                array('integer'),
                array($p_node['tree'])
            );
            
            if ($res1->numRows() == 0) {
                $lastPost_thr = "";
            } else {
                $z = 0;

                while ($selData = $this->db->fetchAssoc($res1)) {
                    if ($z > 0) {
                        break;
                    }

                    $lastPost_thr = $selData["pos_top_fk"] . "#" . $selData["pos_thr_fk"] . "#" . $selData["pos_pk"];
                    $z++;
                }
            }
            
            $this->db->manipulateF(
                '
				UPDATE frm_threads
				SET thr_last_post = %s
				WHERE thr_pk = %s',
                array('text', 'integer'),
                array($lastPost_thr, $p_node['tree'])
            );
        }
        
        // update num_posts in frm_data
        $this->db->manipulateF(
            '
			UPDATE frm_data
			SET top_num_posts = top_num_posts - %s
			WHERE top_frm_fk = %s',
            array('integer', 'integer'),
            array($dead_pos, $this->id)
        );
        
        // get latest post of forum and update last_post
        $res2 = $this->db->queryf(
            '
			SELECT * FROM frm_posts, frm_data 
			WHERE pos_top_fk = top_pk 
			AND top_frm_fk = %s
			ORDER BY pos_date DESC',
            array('integer'),
            array($this->id)
        );
        
        if ($res2->numRows() == 0) {
            $lastPost_top = "";
        } else {
            $z = 0;

            while ($selData = $this->db->fetchAssoc($res2)) {
                if ($z > 0) {
                    break;
                }

                $lastPost_top = $selData["pos_top_fk"] . "#" . $selData["pos_thr_fk"] . "#" . $selData["pos_pk"];
                $z++;
            }
        }
        
        $this->db->manipulateF(
            '
			UPDATE frm_data
			SET top_last_post = %s
			WHERE top_frm_fk = %s',
            array('text', 'integer'),
            array($lastPost_top, $this->id)
        );
        
        return $dead_thr;
    }

    /**
     * @param $a_topic_id
     * @param array $params
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllThreads($a_topic_id, array $params = array(), $limit = 0, $offset = 0)
    {
        $frm_overview_setting = (int) $this->settings->get('forum_overview');
        $frm_props = ilForumProperties::getInstance($this->getForumId());
        $is_post_activation_enabled = $frm_props->isPostActivationEnabled();
        
        $user_id = $this->user->getId();
        
        $excluded_ids_condition = '';
        if (isset($params['excluded_ids']) && is_array($params['excluded_ids']) && $params['excluded_ids']) {
            $excluded_ids_condition = ' AND ' . $this->db->in('thr_pk', $params['excluded_ids'], true, 'integer') . ' ';
        }
        
        if (!in_array(strtolower($params['order_column']), array('lp_date', 'rating', 'thr_subject', 'num_posts', 'num_visit'))) {
            $params['order_column'] = 'post_date';
        }
        if (!in_array(strtolower($params['order_direction']), array('asc', 'desc'))) {
            $params['order_direction'] = 'desc';
        }

        $cnt_active_pos_query = '';
        $cnt_join_type = 'LEFT';
        if ($is_post_activation_enabled && !$params['is_moderator']) {
            $cnt_active_pos_query = " AND (pos_status = {$this->db->quote(1, 'integer')} OR pos_author_id = {$this->db->quote($user_id, 'integer')}) ";
            $cnt_join_type = "INNER";
        }
        $query =
            "SELECT COUNT(DISTINCT(thr_pk)) cnt
			 FROM frm_threads
			 {$cnt_join_type} JOIN frm_posts
			 	ON pos_thr_fk = thr_pk {$cnt_active_pos_query}
			 WHERE thr_top_fk = %s {$excluded_ids_condition}
		";
        $res = $this->db->queryF($query, array('integer'), array($a_topic_id));
        $cntData = $this->db->fetchAssoc($res);
        $cnt = (int) $cntData['cnt'];

        $active_query = '';
        $active_inner_query = '';
        $having = '';
        if ($is_post_activation_enabled && !$params['is_moderator']) {
            $active_query = ' AND (pos_status = %s OR pos_author_id = %s) ';
            $active_inner_query = ' AND (ipos.pos_status = %s OR ipos.pos_author_id = %s) ';
            $having = ' HAVING num_posts > 0';
        }

        $threads = array();
        $data = array();
        $data_types = array();

        $optional_fields = '';
        if ($frm_props->isIsThreadRatingEnabled()) {
            $optional_fields = ',avg_rating';
        }
        if ($frm_props->getThreadSorting() == 1) {
            $optional_fields = ',thread_sorting';
        }

        $additional_sort = '';
        if ($frm_props->getThreadSorting()) {
            $additional_sort .= ' , thread_sorting ASC ';
        }

        if ($params['order_column'] == 'thr_subject') {
            $dynamic_columns = array(', thr_subject ' . $params['order_direction']);
        } elseif ($params['order_column'] == 'num_posts') {
            $dynamic_columns = array(', thr_num_posts ' . $params['order_direction']);
        } elseif ($params['order_column'] == 'num_visit') {
            $dynamic_columns = array(', visits ' . $params['order_direction']);
        } else {
            $dynamic_columns = array(', post_date ' . $params['order_direction']);
        }
        
        if ($frm_props->isIsThreadRatingEnabled()) {
            $dynamic_columns[] = ' ,avg_rating ' . $params['order_direction'];
        }
        if ('rating' == strtolower($params['order_column'])) {
            $dynamic_columns = array_reverse($dynamic_columns);
        }
        $additional_sort .= implode(' ', $dynamic_columns);

        if (!$this->user->isAnonymous()) {
            $query = "SELECT
					  (CASE WHEN COUNT(DISTINCT(notification_id)) > 0 THEN 1 ELSE 0 END) usr_notification_is_enabled,
					  MAX(pos_date) post_date,
					  SUM(tree1.parent_pos != 0) num_posts, 
					  SUM(tree1.parent_pos != 0) - SUM(tree1.parent_pos != 0 AND postread.post_id IS NOT NULL) num_unread_posts, ";

            // new posts query
            if ($frm_overview_setting == ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS) {
                $query .= "
					  (SELECT COUNT(DISTINCT(ipos.pos_pk))
						FROM frm_posts ipos
						INNER JOIN frm_posts_tree treenew
							ON treenew.pos_fk = ipos.pos_pk 
						LEFT JOIN frm_user_read iread ON iread.post_id = ipos.pos_pk AND iread.usr_id = %s
						LEFT JOIN frm_thread_access iacc ON (iacc.thread_id = ipos.pos_thr_fk AND iacc.usr_id = %s)
						WHERE ipos.pos_thr_fk = thr_pk
						AND treenew.parent_pos != 0
						AND (ipos.pos_update > iacc.access_old_ts
							OR
							(iacc.access_old IS NULL AND (ipos.pos_update > " . $this->db->quote(date('Y-m-d H:i:s', NEW_DEADLINE), 'timestamp') . "))
							)
						 
						AND ipos.pos_author_id != %s
						AND iread.usr_id IS NULL $active_inner_query
					  ) num_new_posts, ";
            }
            
            $query .= " thr_pk, thr_top_fk, thr_subject, thr_author_id, thr_display_user_id, thr_usr_alias, thr_num_posts, thr_last_post, thr_date, thr_update, visits, frm_threads.import_name, is_sticky, is_closed
					  {$optional_fields}
					  FROM frm_threads
					  
					  LEFT JOIN frm_notification
						ON frm_notification.thread_id = thr_pk
						AND frm_notification.user_id = %s
					  
					  LEFT JOIN frm_posts
						ON pos_thr_fk = thr_pk $active_query
					  LEFT JOIN frm_posts_tree tree1
					    ON tree1.pos_fk = frm_posts.pos_pk 
					  LEFT JOIN frm_user_read postread
						ON postread.post_id = pos_pk
						AND postread.usr_id = %s";

            $query .= " WHERE thr_top_fk = %s
						{$excluded_ids_condition}
						GROUP BY thr_pk, thr_top_fk, thr_subject, thr_author_id, thr_display_user_id, thr_usr_alias, thr_num_posts, thr_last_post, thr_date, thr_update, visits, frm_threads.import_name, is_sticky, is_closed
						{$optional_fields}
						{$having}
						ORDER BY is_sticky DESC {$additional_sort}, thr_date DESC";
            
            
            // data_types for new posts query and $active_inner_query
            if ($frm_overview_setting == ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS) {
                $data_types[] = 'integer';
                $data_types[] = 'integer';
                $data_types[] = 'integer';
                if ($is_post_activation_enabled && !$params['is_moderator']) {
                    array_push($data_types, 'integer', 'integer');
                }
            }
            $data_types[] = 'integer';
            if ($is_post_activation_enabled && !$params['is_moderator']) {
                array_push($data_types, 'integer', 'integer');
            }
            $data_types[] = 'integer';
            $data_types[] = 'integer';

            // data_values for new posts query and $active_inner_query
            if ($frm_overview_setting == ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS) {
                $data[] = $user_id;
                $data[] = $user_id;
                $data[] = $user_id;
                if ($is_post_activation_enabled && !$params['is_moderator']) {
                    array_push($data, '1', $user_id);
                }
            }
            $data[] = $user_id;
            if ($is_post_activation_enabled && !$params['is_moderator']) {
                array_push($data, '1', $user_id);
            }
            $data[] = $user_id;
            $data[] = $a_topic_id;
        } else {
            $query = "SELECT
					  0 usr_notification_is_enabled,
					  MAX(pos_date) post_date,
					  COUNT(DISTINCT(pos_pk)) num_posts,
					  COUNT(DISTINCT(pos_pk)) num_unread_posts,
					  COUNT(DISTINCT(pos_pk)) num_new_posts,
					  thr_pk, thr_top_fk, thr_subject, thr_author_id, thr_display_user_id, thr_usr_alias, thr_num_posts, thr_last_post, thr_date, thr_update, visits, frm_threads.import_name, is_sticky, is_closed
					  {$optional_fields}
					  FROM frm_threads
					  
					  LEFT JOIN frm_posts
						ON pos_thr_fk = thr_pk $active_query
					  LEFT JOIN frm_posts_tree tree1
					    ON tree1.pos_fk = frm_posts.pos_pk 	
					";

            $query .= " WHERE thr_top_fk = %s
						{$excluded_ids_condition}
						GROUP BY thr_pk, thr_top_fk, thr_subject, thr_author_id, thr_display_user_id, thr_usr_alias, thr_num_posts, thr_last_post, thr_date, thr_update, visits, frm_threads.import_name, is_sticky, is_closed
						{$optional_fields}
						{$having}
						ORDER BY is_sticky DESC {$additional_sort}, thr_date DESC";

            if ($is_post_activation_enabled && !$params['is_moderator']) {
                array_push($data_types, 'integer', 'integer');
            }
            $data_types[] = 'integer';
            if ($is_post_activation_enabled && !$params['is_moderator']) {
                array_push($data, '1', $user_id);
            }
            $data[] = $a_topic_id;
        }

        if ($limit || $offset) {
            $this->db->setLimit($limit, $offset);
        }
        $res = $this->db->queryF($query, $data_types, $data);

        $threadIds = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $thread = new ilForumTopic($row['thr_pk'], $params['is_moderator'], true);
            $thread->assignData($row);
            $threads[$row['thr_pk']] = $thread;
            $threadIds[] = $row['thr_pk'];
        }

        $inner_last_active_post_condition = "";
        if (true || !$params['is_moderator']) {
            $inner_last_active_post_condition = sprintf(
                " AND (iposts.pos_status = %s OR (iposts.pos_status = %s AND iposts.pos_author_id = %s)) ",
                $this->db->quote(1, 'integer'),
                $this->db->quote(0, 'integer'),
                $this->db->quote($this->user->getId(), 'integer')
            );
        }

        $post_res = $this->db->query(
            '
			SELECT frm_posts.*
			FROM frm_posts
			INNER JOIN (
				SELECT pos_thr_fk, MAX(iposts.pos_date) i_pos_date
				FROM frm_posts iposts
				WHERE ' . $this->db->in('iposts.pos_thr_fk', $threadIds, false, 'integer') . ' 
				' . $inner_last_active_post_condition . '
				GROUP BY pos_thr_fk
			) opost ON frm_posts.pos_thr_fk = opost.pos_thr_fk AND frm_posts.pos_date = opost.i_pos_date'
        );

        while ($post_row = $this->db->fetchAssoc($post_res)) {
            $tmp_obj = new ilForumPost($post_row['pos_pk'], $params['is_moderator'], true);

            $tmp_obj->setPosAuthorId($post_row['pos_author_id']);
            $tmp_obj->setDisplayUserId($post_row['pos_display_user_id']);
            $tmp_obj->setUserAlias($post_row['pos_usr_alias']);
            $tmp_obj->setImportName($post_row['import_name']);
            $tmp_obj->setId($post_row['pos_pk']);
            $tmp_obj->setCreateDate($post_row['pos_date']);

            $threads[$post_row['pos_thr_fk']]->setLastPostForThreadOverview($tmp_obj);
        }

        return array(
            'items' => $threads,
            'cnt' => $cnt
        );
    }
    
    /**
     * @param bool $is_moderator
     * @return array
     */
    public function getUserStatistic($is_moderator = false)
    {
        $statistic = array();
        
        $data_types = array();
        $data = array();
        
        $query = "SELECT COUNT(f.pos_display_user_id) ranking, u.login, p.value, u.lastname, u.firstname
	 				FROM frm_posts f
						INNER JOIN frm_posts_tree t
							ON f.pos_pk = t.pos_fk
						INNER JOIN frm_threads th
							ON t.thr_fk = th.thr_pk
						INNER JOIN usr_data u
							ON u.usr_id = f.pos_display_user_id
						INNER JOIN frm_data d
							ON d.top_pk = f.pos_top_fk
						LEFT JOIN usr_pref p
							ON p.usr_id = u.usr_id AND p.keyword = %s
					WHERE 1 = 1 AND t.parent_pos != 0";
    
        array_push($data_types, 'text');
        array_push($data, 'public_profile');

        if (!$is_moderator) {
            $query .= ' AND (pos_status = %s
						OR (pos_status = %s
						AND pos_author_id = %s ))';
            
            array_push($data_types, 'integer', 'integer', 'integer');
            array_push($data, '1', '0', $this->user->getId());
        }
        
        $query .= ' AND d.top_frm_fk = %s
					GROUP BY pos_display_user_id, u.login, p.value,u.lastname, u.firstname';

        array_push($data_types, 'integer');
        array_push($data, $this->getForumId());
        
        $res = $this->db->queryf($query, $data_types, $data);
        
        $counter = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            $statistic[$counter][] = $row['ranking'];
            $statistic[$counter][] = $row['login'];

            $lastname = '';
            $firstname = '';
            if (!$this->user->isAnonymous() && in_array($row['value'], array('y', 'g')) ||
                $this->user->isAnonymous() && 'g' == $row['value']) {
                $lastname = $row['lastname'];
                $firstname = $row['firstname'];
            }

            $statistic[$counter][] = $lastname;
            $statistic[$counter][] = $firstname;
            
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
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_posts_tree 
			WHERE thr_fk = %s
			AND parent_pos = %s',
            array('integer', 'integer'),
            array($a_thread_id, '0')
        );

        $row = $this->db->fetchObject($res);
        
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
        return self::_getModerators($this->getForumRefId());
    }

    /**
    * get all users assigned to local role il_frm_moderator_<frm_ref_id> (static)
    *
    * @param	int		$a_ref_id	reference id
    * @return	array	user_ids
    * @access	public
    */
    public static function _getModerators($a_ref_id)
    {
        global $DIC;
        $rbacreview = $DIC->rbac()->review();

        $role_arr = $rbacreview->getRolesOfRoleFolder($a_ref_id);
        foreach ($role_arr as $role_id) {
            if (ilObject::_lookupTitle($role_id) == 'il_frm_moderator_' . $a_ref_id) {
                return $rbacreview->assignedUsers($role_id);
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
        if (!self::$moderators_by_ref_id_map[$a_ref_id]) {
            self::$moderators_by_ref_id_map[$a_ref_id] = self::_getModerators($a_ref_id);
        }
        return in_array($a_usr_id, self::$moderators_by_ref_id_map[$a_ref_id]);
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
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_data
			INNER JOIN frm_posts ON pos_top_fk = top_pk
			INNER JOIN frm_posts_tree tree1
				ON tree1.pos_fk = frm_posts.pos_pk
				AND tree1.parent_pos != 0  
			WHERE top_frm_fk = %s
			AND pos_author_id = %s',
            array('integer', 'integer'),
            array($this->getForumId(), $a_user_id)
        );
        
        return $res->numRows();
    }
    
    public function countActiveUserArticles($a_user_id)
    {
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_data
			INNER JOIN frm_posts ON pos_top_fk = top_pk
			INNER JOIN frm_posts_tree tree1
				ON tree1.pos_fk = frm_posts.pos_pk
				AND tree1.parent_pos != 0
			WHERE top_frm_fk = %s
			AND (pos_status = %s
				OR (pos_status = %s 
					AND pos_author_id = %s
					)
				)	   
			AND pos_author_id = %s',
            array('integer', 'integer', 'integer', 'integer', 'integer'),
            array($this->getForumId(),'1', '0', $this->user->getId(), $a_user_id)
        );
        
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
        $a_date = $a_date ? $a_date : date("Y-m-d H:i:s");
        
        if ($a_node_id <= 0) {
            $a_node_id = $a_tree_id;
        }
        
        $nextId = $this->db->nextId('frm_posts_tree');
        
        $this->db->manipulateF(
            '
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
            array($nextId, $a_tree_id, $a_node_id, '0', '1', '2', '1', $a_date)
        );
        
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
        $a_date = $a_date ? $a_date : date("Y-m-d H:i:s");
        
        // get left value
        $sql_res = $this->db->queryf(
            '
			SELECT * FROM frm_posts_tree
			WHERE pos_fk = %s
			AND thr_fk = %s',
            array('integer', 'integer'),
            array($a_parent_id, $tree_id)
        );
        
        $res = $this->db->fetchObject($sql_res);
        
        $left = $res->lft;

        $lft = $left + 1;
        $rgt = $left + 2;

        // spread tree
        $this->db->manipulateF(
            '
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
            array($left, $left, $tree_id)
        );
        
        $depth = $this->getPostDepth($a_parent_id, $tree_id) + 1;
    
        // insert node
        $nextId = $this->db->nextId('frm_posts_tree');
        $this->db->manipulateF(
            '
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
        if ($tree_id) {
            $sql_res = $this->db->queryf(
                '
				SELECT depth FROM frm_posts_tree
				WHERE pos_fk = %s
				AND thr_fk = %s',
                array('integer', 'integer'),
                array($a_node_id, $tree_id)
            );
            
            $res = $this->db->fetchObject($sql_res);
            
            return $res->depth;
        } else {
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
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_posts, frm_posts_tree 
			WHERE pos_pk = pos_fk 
			AND parent_pos = %s
			AND thr_fk = %s',
            array('integer', 'integer'),
            array('0', $tree_id)
        );
        
        $row = $this->db->fetchObject($res);
        
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
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_posts, frm_posts_tree 
			WHERE pos_pk = pos_fk 
			AND pos_pk = %s',
            array('integer'),
            array($post_id)
        );
        
        $row = $this->db->fetchObject($res);

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
        if (ilObject::_exists($a_row->pos_display_user_id)) {
            $tmp_user = new ilObjUser($a_row->pos_display_user_id);
            $fullname = $tmp_user->getFullname();
            $loginname = $tmp_user->getLogin();
        }
    
        $fullname = $fullname ? $fullname : ($a_row->import_name ? $a_row->import_name : $this->lng->txt("unknown"));

        $data = array(
                    "pos_pk" => $a_row->pos_pk,
                    "child" => $a_row->pos_pk,
                    "author" => $a_row->pos_display_user_id,
                    "alias" => $a_row->pos_usr_alias,
                    "title" => $fullname,
                    "loginname" => $loginname,
                    "type" => "post",
                    "message" => $a_row->pos_message,
                    "subject" => $a_row->pos_subject,
                    "pos_cens_com" => $a_row->pos_cens_com,
                    "pos_cens" => $a_row->pos_cens,
                //	"date"			=> $a_row->date,
                    "date" => $a_row->fpt_date,
                    "create_date" => $a_row->pos_date,
                    "update" => $a_row->pos_update,
                    "update_user" => $a_row->update_user,
                    "tree" => $a_row->thr_fk,
                    "parent" => $a_row->parent_pos,
                    "lft" => $a_row->lft,
                    "rgt" => $a_row->rgt,
                    "depth" => $a_row->depth,
                    "id" => $a_row->fpt_pk,
                    "notify" => $a_row->notify,
                    "import_name" => $a_row->import_name,
                    "pos_status" => $a_row->pos_status
                    );
        
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
        // GET LEFT AND RIGHT VALUES
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_posts_tree
			WHERE thr_fk = %s 
			AND pos_fk = %s
			AND parent_pos = %s',
            array('integer', 'integer', 'integer'),
            array($a_node['tree'], $a_node['pos_pk'], $a_node['parent'])
        );
        
        while ($row = $this->db->fetchObject($res)) {
            $a_node["lft"] = $row->lft;
            $a_node["rgt"] = $row->rgt;
        }

        $diff = $a_node["rgt"] - $a_node["lft"] + 1;
        
        // get data of posts
        $result = $this->db->queryf(
            '
			SELECT * FROM frm_posts_tree 
			WHERE lft BETWEEN %s AND %s
			AND thr_fk = %s',
            array('integer', 'integer', 'integer'),
            array($a_node['lft'], $a_node['rgt'], $a_node['tree'])
        );
        
        $del_id = array();
        
        while ($treeData = $this->db->fetchAssoc($result)) {
            $del_id[] = $treeData["pos_fk"];
        }
        
        // delete subtree
        $this->db->manipulateF(
            '
			DELETE FROM frm_posts_tree
			WHERE lft BETWEEN %s AND %s
			AND thr_fk = %s',
            array('integer', 'integer', 'integer'),
            array($a_node['lft'], $a_node['rgt'], $a_node['tree'])
        );
        
        // close gaps
        $this->db->manipulateF(
            '
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
            array($a_node['lft'], $diff, $a_node['lft'], $diff, $a_node['tree'])
        );
        
        return $del_id;
    }

    /**
    * update page hits of given forum- or thread-ID
    * @access	public
    * @param	integer
    */
    public function updateVisits($ID)
    {
        $checkTime = time() - (60 * 60);
            
        if ($_SESSION["frm_visit_" . $this->dbTable . "_" . $ID] < $checkTime) {
            $_SESSION["frm_visit_" . $this->dbTable . "_" . $ID] = time();
            $query = 'UPDATE ' . $this->dbTable . ' SET visits = visits + 1 WHERE ';
            
            $data_type = array();
            $data_value = array();
        
            if ($this->getMDB2Query() != '' && $this->getMDB2DataType() != '' && $this->getMDB2DataValue() != '') {
                $query .= $this->getMDB2Query();
                $data_type = $data_type + $this->getMDB2DataType();
                $data_value = $data_value + $this->getMDB2DataValue();

                $res = $this->db->queryf($query, $data_type, $data_value);
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
    public function prepareText($text, $edit = 0, $quote_user = '', $type = '')
    {
        if ($type == 'export') {
            $this->replQuote1 = "<blockquote class=\"quote\"><hr size=\"1\" color=\"#000000\">";
            $this->replQuote2 = "<hr size=\"1\" color=\"#000000\"/></blockquote>";
        }

        if ($edit == 1) {
            // add login name of quoted users
            $lname = ($quote_user != "")
                ? '="' . $quote_user . '"'
                : "";

            $text = "[quote$lname]" . $text . "[/quote]";
        } else {
            // check for quotation
            $startZ = substr_count($text, "[quote");	// also count [quote="..."]
            $endZ = substr_count($text, "[/quote]");

            if ($startZ > 0 || $endZ > 0) {
                // add missing opening and closing tags
                if ($startZ > $endZ) {
                    $diff = $startZ - $endZ;

                    for ($i = 0; $i < $diff; $i++) {
                        if ($type == 'export') {
                            $text .= $this->txtQuote2;
                        } else {
                            $text .= "[/quote]";
                        }
                    }
                } elseif ($startZ < $endZ) {
                    $diff = $endZ - $startZ;

                    for ($i = 0; $i < $diff; $i++) {
                        if ($type == 'export') {
                            $text = $this->txtQuote1 . $text;
                        } else {
                            $text = "[quote]" . $text;
                        }
                    }
                }

                if ($edit == 0) {
                    $text = preg_replace(
                        '@\[(quote\s*?=\s*?"([^"]*?)"\s*?)\]@i',
                        $this->replQuote1 . '<div class="ilForumQuoteHead">' . $this->lng->txt('quote') . ' ($2)</div>',
                        $text
                    );

                    $text = str_replace(
                        "[quote]",
                        $this->replQuote1 . '<div class="ilForumQuoteHead">' . $this->lng->txt("quote") . '</div>',
                        $text
                    );
                    
                    $text = str_replace("[/quote]", $this->replQuote2, $text);
                }
            }
        }
        
        if ($type != 'export') {
            if ($edit == 0) {
                $text = ilMathJax::getInstance()->insertLatexImages($text, "\<span class\=\"latex\">", "\<\/span>");
                $text = ilMathJax::getInstance()->insertLatexImages($text, "\[tex\]", "\[\/tex\]");
            }
            
            // workaround for preventing template engine
            // from hiding text that is enclosed
            // in curly brackets (e.g. "{a}")
            $text = str_replace("{", "&#123;", $text);
            $text = str_replace("}", "&#125;", $text);
        }

        return $text;
    }

    public function __deletePostFiles($a_ids)
    {
        if (!is_array($a_ids)) {
            return false;
        }

        $tmp_file_obj = new ilFileDataForum($this->getForumId());
        foreach ($a_ids as $pos_id) {
            $tmp_file_obj->setPosId($pos_id);
            $files = $tmp_file_obj->getFilesOfPost();
            foreach ($files as $file) {
                $tmp_file_obj->unlinkFile($file["name"]);
            }
        }
        unset($tmp_file_obj);
        return true;
    }

    public function getImportName()
    {
        return $this->import_name;
    }
    public function setImportName($a_import_name)
    {
        $this->import_name = $a_import_name;
    }

    /**
    * Enable a user's notification about new posts in this forum
    * @param    integer	user_id	A user's ID
    * @return	bool	true
    * @access	private
    */
    public function enableForumNotification($user_id)
    {
        if (!$this->isForumNotificationEnabled($user_id)) {
            /* Remove all notifications of threads that belong to the forum */
                
            $res = $this->db->queryf(
                '
				SELECT frm_notification.thread_id FROM frm_data, frm_notification, frm_threads 
				WHERE frm_notification.user_id = %s
				AND frm_notification.thread_id = frm_threads.thr_pk 
				AND frm_threads.thr_top_fk = frm_data.top_pk 
				AND frm_data.top_frm_fk = %s
				GROUP BY frm_notification.thread_id',
                array('integer', 'integer'),
                array($user_id, $this->id)
            );
            
            if (is_object($res) && $res->numRows() > 0) {
                $thread_data = array();
                $thread_data_types = array();

                $query = ' DELETE FROM frm_notification 
							WHERE user_id = %s 
							AND thread_id IN (';
                
                array_push($thread_data, $user_id);
                array_push($thread_data_types, 'integer');
                
                $counter = 1;

                while ($row = $this->db->fetchAssoc($res)) {
                    if ($counter < $res->numRows()) {
                        $query .= '%s, ';
                        array_push($thread_data, $row['thread_id']);
                        array_push($thread_data_types, 'integer');
                    }
            
                    if ($counter == $res->numRows()) {
                        $query .= '%s)';
                        array_push($thread_data, $row['thread_id']);
                        array_push($thread_data_types, 'integer');
                    }
                    $counter++;
                }

                $this->db->manipulateF($query, $thread_data_types, $thread_data);
            }

            /* Insert forum notification */

            $nextId = $this->db->nextId('frm_notification');
            
            $this->db->manipulateF(
                '
				INSERT INTO frm_notification
				( 	notification_id,
					user_id, 
					frm_id
				)
				VALUES(%s, %s, %s)',
                array('integer','integer', 'integer'),
                array($nextId, $user_id, $this->id)
            );
        }
        return true;
    }

    /**
    * Disable a user's notification about new posts in this forum
    * @param    integer	user_id	A user's ID
    * @return	bool	true
    * @access	private
    */
    public function disableForumNotification($user_id)
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification 
			WHERE user_id = %s
			AND frm_id = %s',
            array('integer', 'integer'),
            array($user_id, $this->id)
        );
        
        return true;
    }

    /**
    * Check whether a user's notification about new posts in this forum is enabled (result > 0) or not (result == 0)
    * @param    integer	user_id	A user's ID
    * @return	integer	Result
    */
    public function isForumNotificationEnabled($user_id)
    {
        $result = $this->db->queryf(
            'SELECT COUNT(*) cnt FROM frm_notification WHERE user_id = %s AND frm_id = %s',
            array('integer', 'integer'),
            array($user_id, $this->id)
        );
         
        while ($record = $this->db->fetchAssoc($result)) {
            return (bool) $record['cnt'];
        }
        
        return false;
    }
 
    /**
     * no usage?  ..delete ..
     *
     *
    * Enable a user's notification about new posts in a thread
    * @param    integer	user_id	A user's ID
    * @param    integer	thread_id	ID of the thread
    * @return	bool	true
    * @access	private
    */
    public function enableThreadNotification($user_id, $thread_id)
    {
        if (!$this->isThreadNotificationEnabled($user_id, $thread_id)) {
            $nextId = $this->db->nextId('frm_notification');
            $this->db->manipulateF(
                '
				INSERT INTO frm_notification
				(	notification_id,
					user_id,
					thread_id
				)
				VALUES (%s, %s, %s)',
                array('integer', 'integer', 'integer'),
                array($nextId, $user_id, $thread_id)
            );
        }

        return true;
    }

    /**
    * Check whether a user's notification about new posts in a thread is enabled (result > 0) or not (result == 0)
    * @param    integer	user_id	A user's ID
    * @param    integer	thread_id	ID of the thread
    * @return	integer	Result
    */
    public function isThreadNotificationEnabled($user_id, $thread_id)
    {
        $result = $this->db->queryf(
            '
			SELECT COUNT(*) cnt FROM frm_notification 
			WHERE user_id = %s 
			AND thread_id = %s',
            array('integer', 'integer'),
            array($user_id, $thread_id)
        );

                
        while ($record = $this->db->fetchAssoc($result)) {
            return (bool) $record['cnt'];
        }
        
        return false;
    }
    
    /**
     * Get thread infos of object
     * @param int $a_obj_id
     * @param int $a_sort_mode SORT_TITLE or SORT_DATE
     * @return array
     */
    public static function _getThreads($a_obj_id, $a_sort_mode = self::SORT_DATE)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        switch ($a_sort_mode) {
            case self::SORT_DATE:
                $sort = 'thr_date';
                break;
            
            case self::SORT_TITLE:
            default:
                $sort = 'thr_subject';
                break;
        }
        
        $res = $ilDB->queryf(
            '
			SELECT * FROM frm_threads 
			JOIN frm_data ON top_pk = thr_top_fk 
			WHERE top_frm_fk = %s
			ORDER BY %s',
            array('integer', 'text'),
            array($a_obj_id, $sort)
        );
        
        while ($row = $ilDB->fetchObject($res)) {
            $threads[$row->thr_pk] = $row->thr_subject;
        }
        return $threads ? $threads : array();
    }
        
    public static function _lookupObjIdForForumId($a_for_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryf(
            '
			SELECT top_frm_fk FROM frm_data
			WHERE top_pk = %s',
            array('integer'),
            array($a_for_id)
        );
        
        if ($fdata = $ilDB->fetchAssoc($res)) {
            return $fdata["top_frm_fk"];
        }
        return false;
    }
    
    public static function updateLastPostByObjId($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        // get latest post of forum and update last_post
        $ilDB->setLimit(1);
        $res2 = $ilDB->queryf(
            '
			SELECT pos_top_fk, pos_thr_fk, pos_pk FROM frm_posts, frm_data 
			WHERE pos_top_fk = top_pk 
			AND top_frm_fk = %s
			ORDER BY pos_date DESC',
            array('integer'),
            array($a_obj_id)
        );

        if ($res2->numRows() == 0) {
            $lastPost_top = "";
        } else {
            $z = 0;

            while ($selData = $ilDB->fetchAssoc($res2)) {
                if ($z > 0) {
                    break;
                }

                $lastPost_top = $selData["pos_top_fk"] . "#" . $selData["pos_thr_fk"] . "#" . $selData["pos_pk"];
                $z++;
            }
        }

        $ilDB->update(
            'frm_data',
            array('top_last_post' => array('text', $lastPost_top)),
            array('top_frm_fk' => array('integer',  $a_obj_id))
        );
    }

    /**
     * @param int $source_id
     * @param int $target_id
     * @throws ilException
     */
    public function mergeThreads($source_id, $target_id)
    {
        // selected source and target objects
        $sourceThread = new \ilForumTopic((int) $source_id);
        $targetThread = new \ilForumTopic((int) $target_id);

        if ($sourceThread->getForumId() != $targetThread->getForumId()) {
            throw new \ilException('not_allowed_to_merge_into_another_forum');
        }

        // use the "older" thread as target
        if ($sourceThread->getCreateDate() > $targetThread->getCreateDate()) {
            $sourceThreadForMerge = $sourceThread;
            $targetThreadForMerge = $targetThread;
        } else {
            $sourceThreadForMerge = $targetThread;
            $targetThreadForMerge = $sourceThread;
        }

        $threadSubject = $targetThread->getSubject();

        $targetWasClosedBeforeMerge = (bool) $targetThreadForMerge->isClosed();
        $sourceThreadForMerge->close();

        if (false === $targetWasClosedBeforeMerge) {
            $targetThreadForMerge->close();
        }

        $allSourcePostings = $sourceThreadForMerge->getAllPosts();
        $sourceThreadRootNode = $sourceThreadForMerge->getFirstPostNode();
        $targetThreadRootNode = $targetThreadForMerge->getFirstPostNode();

        $targetRootNodeRgt = $targetThreadRootNode->getRgt();
        // update target root node rgt: Ignore the root node itself from the source (= -2)
        \ilForumPostsTree::updateTargetRootRgt(
            $targetThreadRootNode->getId(),
            ($targetThreadRootNode->getRgt() + $sourceThreadRootNode->getRgt() - 2)
        );

        $targetRootNodeId = $targetThreadRootNode->getId();

        // get source post tree and update posts tree
        foreach ($allSourcePostings as $post) {
            $post_obj = new ilForumPost($post->pos_pk);

            if ((int) $post_obj->getId() === (int) $sourceThreadRootNode->getId()) {
                // Ignore the source root node (MUST be deleted later)
                continue;
            }

            $tree = new \ilForumPostsTree();
            $tree->setPosFk($post->pos_pk);

            if ((int) $post_obj->getParentId() === (int) $sourceThreadRootNode->getId()) {
                $tree->setParentPos($targetRootNodeId);
            } else {
                $tree->setParentPos($post_obj->getParentId());
            }

            $tree->setLft(($post_obj->getLft() + $targetRootNodeRgt) - 2);
            $tree->setRgt(($post_obj->getRgt() + $targetRootNodeRgt) - 2);

            $tree->setDepth($post_obj->getDepth());
            $tree->setTargetThreadId($targetThreadForMerge->getId());
            $tree->setSourceThreadId($sourceThreadForMerge->getId());

            $tree->merge();
        }

        // update frm_posts pos_thr_fk = target_thr_id
        \ilForumPost::mergePosts($sourceThreadForMerge->getId(), $targetThreadForMerge->getId());

        // check notifications
        \ilForumNotification::mergeThreadNotificiations($sourceThreadForMerge->getId(), $targetThreadForMerge->getId());

        // delete frm_thread_access entries
        \ilObjForum::_deleteAccessEntries($sourceThreadForMerge->getId());

        // update frm_user_read
        \ilObjForum::mergeForumUserRead($sourceThreadForMerge->getId(), $targetThreadForMerge->getId());

        // update visits, thr_num_posts, last_post, subject
        $lastPostString = $targetThreadForMerge->getLastPostString();
        $exp = explode('#', $lastPostString);
        if (array_key_exists(2, $exp)) {
            $exp[2] = $targetThreadForMerge->getLastPost()->getId();
            $lastPostString = implode('#', $exp);
        }

        $frm_topic_obj = new \ilForumTopic(0, false, true);
        $frm_topic_obj->setNumPosts((int) $sourceThreadForMerge->getNumPosts() + (int) $targetThreadForMerge->getNumPosts());
        $frm_topic_obj->setVisits((int) $sourceThreadForMerge->getVisits() + (int) $targetThreadForMerge->getVisits());
        $frm_topic_obj->setLastPostString($lastPostString);
        $frm_topic_obj->setSubject($threadSubject);
        $frm_topic_obj->setId($targetThreadForMerge->getId());
        $frm_topic_obj->updateMergedThread();

        // update frm_data:  top_last_post , top_num_threads
        \ilForum::updateLastPostByObjId($this->getForumId());

        if (!$targetWasClosedBeforeMerge) {
            $targetThreadForMerge->reopen();
        }

        // raise event for updating existing drafts
        $GLOBALS['ilAppEventHandler']->raise(
            'Modules/Forum',
            'mergedThreads',
            [
                'source_thread_id' => $sourceThreadForMerge->getId(),
                'target_thread_id' => $targetThreadForMerge->getId()
            ]
        );

        $this->deletePost($sourceThreadRootNode->getId(), false);
    }
}
