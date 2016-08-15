<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Forum/interfaces/interface.ilForumNotificationMailData.php';

/**
 * Class ilObjForumNotificationDataProvider
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilObjForumNotificationDataProvider implements ilForumNotificationMailData
{
	/**
	 * @var int $ref_id
	 */
	protected $ref_id = 0;

	/**
	 * @var int $obj_id
	 */
	protected $obj_id = 0;

	/**
	 * @var string $post_user_name
	 */
	protected $post_user_name = '';

	/**
	 * @var int
	 */
	protected $forum_id = 0;

	/**
	 * @var string $forum_title
	 */
	protected $forum_title = '';

	/**
	 * @var string $thread_title
	 */
	protected $thread_title = '';

	/**
	 * @var array $attachments
	 */
	protected $attachments = array();

	/**
	 * @var ilForumPost
	 */
	public $objPost;

	/**
	 * @param ilForumPost $objPost
	 * @param int         $ref_id
	 */
	public function __construct(ilForumPost $objPost, $ref_id)
	{
		$this->objPost = $objPost;
		$this->ref_id  = $ref_id;
		$this->obj_id  = ilObject::_lookupObjId($ref_id);
		$this->read();
	}

	/**
	 * @return int
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}

	/**
	 * @return int
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/**
	 * @return int
	 */
	public function getThreadId()
	{
		return $this->objPost->getThreadId();
	}

	/**
	 * @return int
	 */
	public function getPostId()
	{
		return $this->objPost->getId();
	}

	/**
	 * @return int
	 */
	public function getForumId()
	{
		return $this->forum_id;
	}

	/**
	 * @return string frm_data.top_name
	 */
	public function getForumTitle()
	{
		return $this->forum_title;
	}

	/**
	 * @return string frm_threads.thr_subject
	 */
	public function getThreadTitle()
	{
		return $this->thread_title;
	}

	/**
	 * @return string frm_posts.pos_subject
	 */
	public function getPostTitle()
	{
		return $this->objPost->getSubject();
	}

	/**
	 * @return string frm_posts.pos_message
	 */
	public function getPostMessage()
	{
		return $this->objPost->getMessage();
	}

	/**
	 * @return string frm_posts.pos_display_user_id
	 */
	public function getPosDisplayUserId()
	{
		return $this->objPost->getDisplayUserId();
	}

	/**
	 * @return string
	 */
	public function getPostUserName($user_lang)
	{
		// GET AUTHOR OF NEW POST
		if($this->objPost->getDisplayUserId())
		{
			$this->post_user_name = ilObjUser::_lookupLogin($this->objPost->getDisplayUserId());
		}
		else if(strlen($this->objPost->getUserAlias()))
		{
			$this->post_user_name = $this->objPost->getUserAlias() . ' (' . $user_lang->txt('frm_pseudonym') . ')';
		}

		if($this->post_user_name == '')
		{
			$this->post_user_name = $user_lang->txt('forums_anonymous');
		}

		return $this->post_user_name;
	}

	/**
	 * @return string frm_posts.pos_date
	 */
	public function getPostDate()
	{
		return $this->objPost->getCreateDate();
	}

	/**
	 * @return string frm_posts.pos_update
	 */
	public function getPostUpdate()
	{
		return $this->objPost->getChangeDate();
	}

	/**
	 * @return string login
	 */
	public function getPostUpdateUserName()
	{
		return ilObjUser::_lookupLogin($this->objPost->getUpdateUserId());
	}

	/**
	 * @return bool frm_posts.pos_cens
	 */
	public function getPostCensored()
	{
		return $this->objPost->isCensored();
	}

	/**
	 * @return string frm_posts.pos_cens_date
	 */
	public function getPostCensoredDate()
	{
		return $this->objPost->getCensoredDate();
	}

	public function getCensorshipComment()
	{
		return $this->objPost->getCensorshipComment();
	}

	/**
	 * @return array file names
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * @return string frm_posts.pos_usr_alias
	 */
	public function getPosUserAlias()
	{
		return $this->objPost->getUserAlias();
	}

	/**
	 *
	 */
	protected function read()
	{
		$this->readForumData();
		$this->readThreadTitle();
		$this->readAttachments();
	}

	/**
	 *
	 */
	private function readThreadTitle()
	{
		global $ilDB;

		$result = $ilDB->queryf('
			SELECT thr_subject FROM frm_threads 
			WHERE thr_pk = %s',
			array('integer'), array($this->objPost->getThreadId()));

		$row = $ilDB->fetchAssoc($result);
		$this->thread_title = $row['thr_subject'];
	}

	/**
	 *
	 */
	private function readForumData()
	{
		global $ilDB;

		$result = $ilDB->queryf('
			SELECT top_pk, top_name FROM frm_data
			WHERE top_frm_fk = %s',
			array('integer'), array($this->getObjId()));

		$row = $ilDB->fetchAssoc($result);
		$this->forum_id    = $row['top_pk'];
		$this->forum_title = $row['top_name'];
	}

	/**
	 *
	 */
	private function readAttachments()
	{
		require_once 'Modules/Forum/classes/class.ilFileDataForum.php';
		$fileDataForum = new ilFileDataForum($this->getObjId(), $this->objPost->getId());
		$filesOfPost   = $fileDataForum->getFilesOfPost();

		foreach($filesOfPost as $attachment)
		{
			$this->attachments[] = $attachment['name'];
		}
	}

	/**
	 * @return array
	 */
	public function getForumNotificationRecipients()
	{
		global $ilDB, $ilAccess, $ilUser;

		$res = $ilDB->queryf('
			SELECT frm_notification.user_id FROM frm_notification, frm_data 
			WHERE frm_data.top_pk = %s
			AND frm_notification.frm_id = frm_data.top_frm_fk 
			AND frm_notification.user_id <> %s
			GROUP BY frm_notification.user_id',
			array('integer', 'integer'),
			array($this->getForumId(), $ilUser->getId()));

		// get all references of obj_id
		$frm_references = ilObject::_getAllReferences($this->getObjId());
		$rcps = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			// do rbac check before sending notification
			foreach((array)$frm_references as $ref_id)
			{
				if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
				{
					$rcps[] = $row['user_id'];
				}
			}
		}


		return array_unique($rcps);
	}

	/**
	 * @return array
	 */
	public function getThreadNotificationRecipients()
	{
		global $ilDB, $ilAccess, $ilUser;

		// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
		$res = $ilDB->queryf('
			SELECT user_id FROM frm_notification 
			WHERE thread_id = %s
			AND user_id <> %s',
			array('integer', 'integer'),
			array($this->getThreadId(), $GLOBALS['DIC']['ilUser']->getId()));

		// get all references of obj_id
		$frm_references = ilObject::_getAllReferences($this->getObjId());
		$rcps = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			// do rbac check before sending notification
			foreach((array)$frm_references as $ref_id)
			{
				if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
				{
					$rcps[] = $row['user_id'];
				}
			}
		}
		return $rcps;
	}

	/**
	 * @return array
	 */
	public function getPostAnsweredRecipients()
	{
		include_once './Modules/Forum/classes/class.ilForumPost.php';
		$parent_objPost = new ilForumPost($this->objPost->getParentId());

		$rcps = array();
		$rcps[] = $parent_objPost->getPosAuthorId();

		return $rcps;
	}

	/**
	 * @return array
	 */
	public function getPostActivationRecipients()
	{
		include_once './Modules/Forum/classes/class.ilForum.php';
		// get moderators to notify about needed activation
		$rcps =  ilForum::_getModerators($this->getRefId());
		return  (array)$rcps;
	}
}