<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Forum/interfaces/interface.ilForumNotificationMailData.php';
include_once './Modules/Forum/classes/class.ilForumProperties.php';

/**
 * Class ilObjForumNotificationDataProvider
 * @author Nadia Matuschek <nmatuschek@databay.de>
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
	public $pos_author_id = 0;
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

	private $db;
	private $access;
	private $user;
	
	/**
	 * @param ilForumPost $objPost
	 * @param int         $ref_id
	 */
	public function __construct(ilForumPost $objPost, $ref_id)
	{
		global $DIC;
		$this->db = $DIC->database();
		$this->access = $DIC->access();
		$this->user = $DIC->user();
		
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
	 * @param ilLanguage $user_lang
	 * @return bool|string
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
	 * @param ilLanguage $user_lang
	 * @return bool|string
	 */
	public function getPostUpdateUserName($user_lang)
	{
		// GET AUTHOR OF UPDATED POST
		if($this->objPost->getUpdateUserId() > 0)
		{
			$this->post_user_name = ilObjUser::_lookupLogin($this->objPost->getUpdateUserId());
		}
		
		if($this->objPost->getDisplayUserId() == 0 && $this->objPost->getPosAuthorId() == $this->objPost->getUpdateUserId())
		{
			if(strlen($this->objPost->getUserAlias()))
			{
				$this->post_user_name = $this->objPost->getUserAlias() . ' (' . $user_lang->txt('frm_pseudonym') . ')';
			}
			
			if($this->post_user_name == '')
			{
				$this->post_user_name = $user_lang->txt('forums_anonymous');
			}
		}
		
		return $this->post_user_name;
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
		$result = $this->db->queryf('
			SELECT thr_subject FROM frm_threads 
			WHERE thr_pk = %s',
			array('integer'), array($this->objPost->getThreadId()));

		$row = $this->db->fetchAssoc($result);
		$this->thread_title = $row['thr_subject'];
	}

	/**
	 *
	 */
	private function readForumData()
	{
		$result = $this->db->queryf('
			SELECT top_pk, top_name FROM frm_data
			WHERE top_frm_fk = %s',
			array('integer'), array($this->getObjId()));

		$row = $this->db->fetchAssoc($result);
		$this->forum_id    = $row['top_pk'];
		$this->forum_title = $row['top_name'];
	}

	/**
	 *
	 */
	private function readAttachments()
	{
		if(ilForumProperties::isSendAttachmentsByMailEnabled())
		{
			require_once 'Modules/Forum/classes/class.ilFileDataForum.php';
			$fileDataForum = new ilFileDataForum($this->getObjId(), $this->objPost->getId());
			$filesOfPost   = $fileDataForum->getFilesOfPost();
			
			require_once 'Services/Mail/classes/class.ilFileDataMail.php';
			$fileDataMail = new ilFileDataMail(ANONYMOUS_USER_ID);
			
			foreach($filesOfPost as $attachment)
			{
				$this->attachments[$attachment['path']] = $attachment['name'];
				$fileDataMail->copyAttachmentFile($attachment['path'], $attachment['name']);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getForumNotificationRecipients()
	{
		$res = $this->db->queryf('
			SELECT frm_notification.user_id FROM frm_notification, frm_data 
			WHERE frm_data.top_pk = %s
			AND frm_notification.frm_id = frm_data.top_frm_fk 
			AND frm_notification.user_id <> %s
			GROUP BY frm_notification.user_id',
			array('integer', 'integer'),
			array($this->getForumId(), $this->user->getId()));

		// get all references of obj_id
		$frm_references = ilObject::_getAllReferences($this->getObjId());
		$rcps = array();
		while($row = $this->db->fetchAssoc($res))
		{
			// do rbac check before sending notification
			foreach((array)$frm_references as $ref_id)
			{
				if($this->access->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
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
		// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
		$res = $this->db->queryf('
			SELECT user_id FROM frm_notification 
			WHERE thread_id = %s
			AND user_id <> %s',
			array('integer', 'integer'),
			array($this->getThreadId(), $this->user->getId()));

		// get all references of obj_id
		$frm_references = ilObject::_getAllReferences($this->getObjId());
		$rcps = array();
		while($row = $this->db->fetchAssoc($res))
		{
			// do rbac check before sending notification
			foreach((array)$frm_references as $ref_id)
			{
				if($this->access->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
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
	
	/**
	 * @param $pos_author_id
	 */
	public function setPosAuthorId($pos_author_id)
	{
		$this->pos_author_id = $pos_author_id;
	}
	
	/**
	 * @return int
	 */
	public function getPosAuthorId()
	{
		return $this->pos_author_id;
	}
}