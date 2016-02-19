<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Forum/interfaces/interface.ilForumNotificationMailData.php';

/**
 * Class ilForumCronNotificationDataProvider
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilForumCronNotificationDataProvider implements ilForumNotificationMailData
{
	/**
	 * @var null
	 */
	public $notification_type = NULL;

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
	 * @var int
	 */
	protected $thread_id = 0;

	/**
	 * @var string $thread_title
	 */
	protected $thread_title = '';

	/**
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * @var string
	 */
	protected $post_title = '';
	/**
	 * @var string
	 */
	protected $post_message = '';
	/**
	 * @var null
	 */
	protected $post_date = NULL;
	/**
	 * @var null
	 */
	protected $post_update = NULL;

	/**
	 * @var bool
	 */
	protected $post_censored = false;
	/**
	 * @var null
	 */
	protected $post_censored_date = NULL;
	/**
	 * @var string
	 */
	protected $post_censored_comment = '';

	/**
	 * @var string
	 */
	protected $pos_usr_alias = '';
	/**
	 * @var int
	 */
	protected $pos_display_user_id = 0;

	/**
	 * @var array $attachments
	 */
	protected $attachments = array();

	/**
	 * @var array $cron_recipients user_ids
	 */
	protected $cron_recipients = array();

	/**
	 * @var ilForumPost|null
	 */
	public $objPost = NULL;

	/**
	 * @var int
	 */
	public $post_upate_user_id = 0;

	/**
	 * @param $row
	 */
	public function __construct($row)
	{
		$this->obj_id = $row['obj_id'];
		$this->ref_id = $row['ref_id'];

		$this->thread_id    = $row['thread_id'];
		$this->thread_title = $row['thr_subject'];

		$this->forum_id    = $row['pos_top_fk'];
		$this->forum_title = $row['top_name'];

		$this->post_id     = $row['pos_pk'];
		$this->post_title  = $row['pos_subject'];
		$this->post_message  = $row['pos_message'];
		$this->post_date   = $row['pos_date'];
		$this->post_update = $row['pos_update'];
		$this->post_upate_user_id = $row['update_user'];

		$this->post_censored         = $row['pos_cens'];
		$this->post_censored_date    = $row['pos_cens_date'];
		$this->post_censored_comment = $row['pos_cens_com'];

		$this->pos_usr_alias       = $row['pos_usr_alias'];
		$this->pos_display_user_id = $row['pos_display_user_id'];

		$this->read();
	}

	/**
	 *
	 */
	protected function read()
	{
		$this->readAttachments();
	}

	/**
	 *
	 */
	private function readAttachments()
	{
		// get attachments
		include_once "./Modules/Forum/classes/class.ilFileDataForum.php";
		$fileDataForum = new ilFileDataForum($this->getObjId(), $this->getPostId());
		$filesOfPost   = $fileDataForum->getFilesOfPost();

		foreach($filesOfPost as $attachment)
		{
			$this->attachments[] = $attachment['name'];
		}
	}

	/**
	 * @param int $user_id
	 */
	public function addRecipient($user_id)
	{
		$this->cron_recipients[] = (int)$user_id;
	}

	/**
	 * @return array
	 */
	public function getCronRecipients()
	{
		return $this->cron_recipients;
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
	 * @return int frm_data.top_pk
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
	 * @return int
	 */
	public function getThreadId()
	{
		return $this->thread_id;
	}

	/**
	 * @return string frm_threads.thr_subject
	 */
	public function getThreadTitle()
	{
		return $this->thread_title;
	}

	/**
	 * @return int
	 */
	public function getPostId()
	{
		return $this->post_id;
	}

	/**
	 * @return string frm_posts.pos_subject
	 */
	public function getPostTitle()
	{
		return $this->post_title;
	}

	/**
	 * @return string frm_posts.pos_message
	 */
	public function getPostMessage()
	{
		return $this->post_message;
	}

	/**
	 * @return string
	 */
	public function getPostUserName($user_lang)
	{
		// GET AUTHOR OF NEW POST
		if($this->getPosDisplayUserId())
		{
			$this->post_user_name = ilObjUser::_lookupLogin($this->getPosDisplayUserId());
		}
		else if(strlen($this->getPosUserAlias()))
		{
			$this->post_user_name = $this->getPosUserAlias() . ' (' . $user_lang->txt('frm_pseudonym') . ')';
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
		return $this->post_date;
	}

	/**
	 * @return string frm_posts.pos_update
	 */
	public function getPostUpdate()
	{
		return $this->post_update;
	}

	/**
	 * @return string login
	 */
	public function getPostUpdateUserName()
	{
		return ilObjUser::_lookupLogin($this->getPostUpateUserId());
	}

	/**
	 * @return string frm_posts.pos_cens
	 */
	public function getPostCensored()
	{
		return $this->post_censored;
	}

	/**
	 * @return string frm_posts.pos_cens_date
	 */
	public function getPostCensoredDate()
	{
		return $this->post_censored_date;
	}

	/**
	 * @return string
	 */
	public function getCensorshipComment()
	{
		return $this->post_censored_comment;
	}

	/**
	 * @return array file names
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * @param null $notification_type
	 */
	public function setNotificationType($notification_type)
	{
		$this->notification_type = $notification_type;
	}

	/**
	 * @return int
	 */
	public function getPosDisplayUserId()
	{
		return $this->pos_display_user_id;
	}


	/**
	 * @return string frm_posts.pos_usr_alias
	 */
	public function getPosUserAlias()
	{
		return $this->pos_usr_alias;
	}

	/**
	 * @return int
	 */
	public function getPostUpateUserId()
	{
		return $this->post_upate_user_id;
	}

	/**
	 * @param int $post_upate_user_id
	 */
	public function setPostUpateUserId($post_upate_user_id)
	{
		$this->post_upate_user_id = $post_upate_user_id;
	}
}