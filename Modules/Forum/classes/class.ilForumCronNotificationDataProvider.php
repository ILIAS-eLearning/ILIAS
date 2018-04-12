<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Forum/interfaces/interface.ilForumNotificationMailData.php';
include_once './Modules/Forum/classes/class.ilForumProperties.php';
require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';

/**
 * Class ilForumCronNotificationDataProvider
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
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
	 * @var string|null $post_user_name
	 */
	protected $post_user_name = null;

	/**
	 * @var string|null $update_user_name
	 */
	protected $update_user_name = null;

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
	 * @var bool
	 */
	protected $is_anonymized = false;
	
	/**
	 * @var int|string
	 */
	protected $import_name = '';
	
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
	public $post_update_user_id = 0;
	
	/**
	 * @var int
	 */
	public $pos_author_id = 0;

	/**
	 * @var \ilForumAuthorInformation[]
	 */
	protected static $authorInformationCache = array();

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
		$this->post_update_user_id = $row['update_user'];

		$this->post_censored         = $row['pos_cens'];
		$this->post_censored_date    = $row['pos_cens_date'];
		$this->post_censored_comment = $row['pos_cens_com'];

		$this->pos_usr_alias       = $row['pos_usr_alias'];
		$this->pos_display_user_id = $row['pos_display_user_id'];
		$this->pos_author_id = $row['pos_author_id'];

		$this->import_name = strlen($row['import_name']) ? $row['import_name'] : '';

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
		if(ilForumProperties::isSendAttachmentsByMailEnabled())
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
	public function getPostUpdateUserId()
	{
		return $this->post_update_user_id;
	}

	/**
	 * @param int $post_update_user_id
	 */
	public function setPostUpdateUserId($post_update_user_id)
	{
		$this->post_update_user_id = $post_update_user_id;
	}
	
	public function setPosAuthorId($pos_author_id)
	{
		$this->pos_author_id = $pos_author_id;
	}
	public function getPosAuthorId()
	{
		return $this->pos_author_id;
	}
	
	/**
	 * @return bool
	 */
	public function isAnonymized()
	{
		return $this->is_anonymized;
	}
	
	/**
	 * @return string
	 */
	public function getImportName()
	{
		return $this->import_name;
	}

	/**
	 * @param ilLanguage $lng
	 * @param            $authorUsrId
	 * @param            $displayUserId
	 * @param            $usrAlias
	 * @param            $importName
	 * @return \ilForumAuthorInformation
	 */
	private function getAuthorInformation(\ilLanguage $lng, $authorUsrId, $displayUserId, $usrAlias, $importName)
	{
		$cacheKey = md5(implode('|', array(
			$lng->getLangKey(),
			(int)$authorUsrId,
			(int)$displayUserId,
			(string)$usrAlias,
			(string)$importName
		)));

		if (!array_key_exists($cacheKey, self::$authorInformationCache)) {
			$authorInformation = new ilForumAuthorInformation(
				$authorUsrId,
				$displayUserId,
				$usrAlias,
				$importName,
				array(),
				$lng
			);

			self::$authorInformationCache[$cacheKey] = $authorInformation;
		}

		return self::$authorInformationCache[$cacheKey];
	}

	/**
	 * @inheritdoc
	 */
	public function getPostUserName(\ilLanguage $user_lang)
	{
		if (null === $this->post_user_name) {
			$this->post_user_name = $this->getPublicUserInformation(self::getAuthorInformation(
				$user_lang,
				$this->getPosAuthorId(),
				$this->getPosDisplayUserId(),
				$this->getPosUserAlias(),
				$this->getImportName()
			));
		}

		return (string)$this->post_user_name;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getPostUpdateUserName(\ilLanguage $user_lang)
	{
		if ($this->update_user_name === null) {
			$this->update_user_name = $this->getPublicUserInformation(self::getAuthorInformation(
				$user_lang,
				$this->getPosAuthorId(),
				$this->getPostUpdateUserId(),
				$this->getPosUserAlias(),
				$this->getImportName()
			));
		}

		return (string)$this->update_user_name;
	}
	
	/**
	 * @param ilForumAuthorInformation $authorinfo
	 * @return string
	 */
	public function getPublicUserInformation(ilForumAuthorInformation $authorinfo)
	{
		if($authorinfo->hasSuffix())
		{
			$public_name = $authorinfo->getAuthorName();
		}
		else
		{
			$public_name = $authorinfo->getAuthorShortName();
			
			if($authorinfo->getAuthorName() && !$this->isAnonymized())
			{
				$public_name = $authorinfo->getAuthorName();
			}
		}
		
		return $public_name;
	}
}