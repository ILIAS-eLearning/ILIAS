<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";
include_once "./Modules/Forum/classes/class.ilForumMailNotification.php";

/**
 * Forum notifications
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumCronNotification extends ilCronJob
{
	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var array  ilForumCronNotificationDataProvider
	 */
	public static $providerObject = array();

	/**
	 * @var array frm_posts_deleted.deleted_id
	 */
	protected static $deleted_ids_cache = array();

	/**
	 * @var array
	 */
	protected static $ref_ids_by_obj_id = array();

	/**
	 * @var array
	 */
	protected static $accessible_ref_ids_by_user = array();
	
	/**
	 * @var int
	 */
	protected $num_sent_messages = 0;
	
	/**
	 *
	 */
	public function __construct()
	{
		$this->settings = new ilSetting('frma');
	}

	public function getId()
	{
		return "frm_notification";
	}
	
	public function getTitle()
	{
		global $DIC;

		return $DIC->language()->txt("cron_forum_notification");
	}
	
	public function getDescription()
	{
		global $DIC;

		return $DIC->language()->txt("cron_forum_notification_crob_desc");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_HOURS;
	}
	
	public function getDefaultScheduleValue()
	{
		return 1;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasCustomSettings() 
	{
		return true;
	}

	/**
	 * @return ilCronJobResult
	 */
	public function run()
	{
		global $DIC; 
		$ilDB = $DIC->database(); 
		$ilLog = $DIC->logger()->root(); 
		$ilSetting = $DIC->settings(); 
		$lng = $DIC->language();

		$status = ilCronJobResult::STATUS_NO_ACTION;

		$lng->loadLanguageModule('forum');

		if(!($last_run_datetime = $ilSetting->get('cron_forum_notification_last_date')))
		{
			$last_run_datetime = null;
		}

		$numRows = 0;
		$this->num_sent_messages = 0;
		$cj_start_date = date('Y-m-d H:i:s');

		if($last_run_datetime != null &&
			checkDate(date('m', strtotime($last_run_datetime)), date('d', strtotime($last_run_datetime)), date('Y', strtotime($last_run_datetime))))
		{
			$threshold = max(strtotime($last_run_datetime), strtotime('-' . (int)$this->settings->get('max_notification_age', 30) . ' days', time()));
		}
		else
		{
			$threshold = strtotime('-' . (int)$this->settings->get('max_notification_age', 30) . ' days', time());
		}
		$threshold_date =  date('Y-m-d H:i:s', $threshold);
		$new_posts_condition = '
			frm_posts.pos_status = %s AND (
				(frm_posts.pos_date >= %s AND frm_posts.pos_date = frm_posts.pos_activation_date) OR 
				(frm_posts.pos_activation_date >= %s AND frm_posts.pos_date < frm_posts.pos_activation_date)
			) ';
		$types          = array('integer', 'timestamp', 'timestamp');
		$values         = array(1, $threshold_date, $threshold_date);
		
		/*** new posts ***/
		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_threads.thr_pk thread_id,
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	frm_posts.pos_thr_fk = frm_threads.thr_pk AND '.$new_posts_condition.' 
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id != frm_notification.user_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows = $ilDB->numRows($res);
		if($numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_NEW);
		}

		/*** updated posts ***/
		$updated_condition = '
			frm_posts.pos_cens = %s AND frm_posts.pos_status = %s AND 
			(frm_posts.pos_update > frm_posts.pos_date AND frm_posts.pos_update >= %s) ';
		$types             = array('integer', 'integer', 'timestamp');
		$values            = array(0, 1, $threshold_date);

		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id,
					frm_threads.thr_pk thread_id,
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	frm_posts.pos_thr_fk = frm_threads.thr_pk AND '.$updated_condition.' 
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id != frm_notification.user_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows = $ilDB->numRows($res);
		if($numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_UPDATED);
		}

		/*** censored posts ***/ 
		$censored_condition = '
			frm_posts.pos_cens = %s AND frm_posts.pos_status = %s AND  
            (frm_posts.pos_cens_date >= %s AND frm_posts.pos_cens_date > frm_posts.pos_activation_date ) ';
		$types              = array('integer', 'integer', 'timestamp');
		$values             = array(1, 1, $threshold_date);

		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id,
					frm_threads.thr_pk thread_id,
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	frm_posts.pos_thr_fk = frm_threads.thr_pk AND '.$censored_condition.'
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	(frm_posts.pos_display_user_id != frm_notification.user_id)
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows = $ilDB->numRows($res);
		if($numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_CENSORED);
		}
		
		/*** uncensored posts ***/
		$uncensored_condition = '
			frm_posts.pos_cens = %s AND frm_posts.pos_status = %s AND  
            (frm_posts.pos_cens_date >= %s AND frm_posts.pos_cens_date > frm_posts.pos_activation_date ) ';
		$types              = array('integer', 'integer', 'timestamp');
		$values             = array(0, 1, $threshold_date);

		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id,
					frm_threads.thr_pk thread_id,
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	frm_posts.pos_thr_fk = frm_threads.thr_pk AND '.$uncensored_condition.' 
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id != frm_notification.user_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows = $ilDB->numRows($res);
		if($numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_UNCENSORED);
		}

		/*** deleted threads ***/
		$res = $ilDB->queryF('
			SELECT 	frm_posts_deleted.thread_title thr_subject, 
					frm_posts_deleted.forum_title  top_name, 
					frm_posts_deleted.obj_id obj_id, 
					frm_notification.user_id user_id, 
					frm_posts_deleted.pos_display_user_id,
					frm_posts_deleted.pos_usr_alias,
					frm_posts_deleted.deleted_id,
					frm_posts_deleted.post_date pos_date,
					frm_posts_deleted.post_title pos_subject,
					frm_posts_deleted.post_message pos_message
					
			FROM 	frm_notification, frm_posts_deleted
			
			WHERE 	( frm_posts_deleted.obj_id = frm_notification.frm_id
					OR frm_posts_deleted.thread_id = frm_notification.thread_id) 
			AND 	frm_posts_deleted.pos_display_user_id != frm_notification.user_id
			AND 	frm_posts_deleted.is_thread_deleted = %s
			ORDER BY frm_posts_deleted.post_date ASC',
			array('integer'), array(1));
		$numRows = $ilDB->numRows($res);
		if($numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_THREAD_DELETED);
			if(count(self::$deleted_ids_cache) > 0)
			{
				$ilDB->manipulate('DELETE FROM frm_posts_deleted WHERE '. $ilDB->in('deleted_id', self::$deleted_ids_cache, false, 'integer'));
				$ilLog->write(__METHOD__ . ':DELETED ENTRIES: frm_posts_deleted');
			}
		}

		/*** deleted posts ***/
		$res = $ilDB->queryF('
			SELECT 	frm_posts_deleted.thread_title thr_subject, 
					frm_posts_deleted.forum_title  top_name, 
					frm_posts_deleted.obj_id obj_id, 
					frm_notification.user_id user_id, 
					frm_posts_deleted.pos_display_user_id,
					frm_posts_deleted.pos_usr_alias,
					frm_posts_deleted.deleted_id,
					frm_posts_deleted.post_date pos_date,
					frm_posts_deleted.post_title pos_subject,
					frm_posts_deleted.post_message pos_message
					
			FROM 	frm_notification, frm_posts_deleted
			
			WHERE 	( frm_posts_deleted.obj_id = frm_notification.frm_id
					OR frm_posts_deleted.thread_id = frm_notification.thread_id) 
			AND 	frm_posts_deleted.pos_display_user_id != frm_notification.user_id
			AND 	frm_posts_deleted.is_thread_deleted = %s
			ORDER BY frm_posts_deleted.post_date ASC',
			array('integer'), array(0));
		
		$numRows = $ilDB->numRows($res);
		if($numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_DELETED);
			if(count(self::$deleted_ids_cache) > 0)
			{
				$ilDB->manipulate('DELETE FROM frm_posts_deleted WHERE '. $ilDB->in('deleted_id', self::$deleted_ids_cache, false, 'integer')); 
				$ilLog->write(__METHOD__ . ':DELETED ENTRIES: frm_posts_deleted');
			}
		}

		$ilSetting->set('cron_forum_notification_last_date', $cj_start_date);

		$mess = 'Sent '.$this->num_sent_messages.' messages.';
		$ilLog->write(__METHOD__.': '.$mess);

		$result = new ilCronJobResult();
		if($this->num_sent_messages)
		{
			$status = ilCronJobResult::STATUS_OK;
			$result->setMessage($mess);
		};
		$result->setStatus($status);
		return $result;
	}

	/**
	 * @param int $a_obj_id
	 * @return array
	 */
	protected function getRefIdsByObjId($a_obj_id)
	{
		if(!array_key_exists($a_obj_id, self::$ref_ids_by_obj_id))
		{
			self::$ref_ids_by_obj_id[$a_obj_id] = ilObject::_getAllReferences($a_obj_id);
		}

		return (array)self::$ref_ids_by_obj_id[$a_obj_id];
	}

	/**
	 * @param int $a_user_id
	 * @param int $a_obj_id
	 * @return int
	 */
	protected function getFirstAccessibleRefIdBUserAndObjId($a_user_id, $a_obj_id)
	{
		global $DIC; 
		$ilAccess = $DIC->access();

		if(!array_key_exists($a_user_id, self::$accessible_ref_ids_by_user))
		{
			self::$accessible_ref_ids_by_user[$a_user_id] = array();
		}

		if(!array_key_exists($a_obj_id, self::$accessible_ref_ids_by_user[$a_user_id]))
		{
			$accessible_ref_id = 0;
			foreach($this->getRefIdsByObjId($a_obj_id) as $ref_id)
			{
				if($ilAccess->checkAccessOfUser($a_user_id, 'read', '', $ref_id))
				{
					$accessible_ref_id = $ref_id;
					break;
				}
			}
			self::$accessible_ref_ids_by_user[$a_user_id][$a_obj_id] = $accessible_ref_id;
		}

		return (int)self::$accessible_ref_ids_by_user[$a_user_id][$a_obj_id];
	}

	/**
	 * @param $res
	 * @param $notification_type
	 */
	public function sendCronForumNotification($res, $notification_type)
	{
		global $DIC; 
		$ilDB = $DIC->database();
		$ilLog = $DIC->logger()->root();
		
		include_once './Modules/Forum/classes/class.ilForumCronNotificationDataProvider.php';
		include_once './Modules/Forum/classes/class.ilForumMailNotification.php';

		while($row = $ilDB->fetchAssoc($res))
		{
			if($notification_type == ilForumMailNotification::TYPE_POST_DELETED
				|| $notification_type == ilForumMailNotification::TYPE_THREAD_DELETED)
			{
				// important! save the deleted_id to cache before proceeding getFirstAccessibleRefIdBUserAndObjId !
				self::$deleted_ids_cache[$row['deleted_id']] = $row['deleted_id'];
			}
			
			$ref_id = $this->getFirstAccessibleRefIdBUserAndObjId($row['user_id'], $row['obj_id']);
			if($ref_id < 1)
			{
				$ilLog->write(__METHOD__.': User-Id: '.$row['user_id'].' has no read permission for object id: '.$row['obj_id']);
				continue;
			}

			$row['ref_id'] = $ref_id;

			if($this->existsProviderObject($row['pos_pk']))
			{
				self::$providerObject[$row['pos_pk']]->addRecipient($row['user_id']);
			}	
			else
			{
				$this->addProviderObject($row);
			}
		}

		foreach(self::$providerObject as  $provider)
		{
			$mailNotification = new ilForumMailNotification($provider);
			$mailNotification->setIsCronjob(true);
			$mailNotification->setType($notification_type);
			$mailNotification->setRecipients(array_unique($provider->getCronRecipients()));

			$mailNotification->send();
	
			$this->num_sent_messages += count($provider->getCronRecipients());
			$ilLog->write(__METHOD__.':SUCCESSFULLY SEND: NotificationType: '.$notification_type.' -> Recipients: '. implode(', ',$provider->getCronRecipients()));
		}
		
		$this->resetProviderCache();
	}

	/**
	 * @param $post_id
	 * @return bool
	 */
	public function existsProviderObject($post_id)
	{
		if(isset(self::$providerObject[$post_id]))
		{
			return true;
		}	
		return false;
	}

	/**
	 * @param $row
	 */
	private function addProviderObject($row)
	{
		$tmp_provider = new ilForumCronNotificationDataProvider($row);

		self::$providerObject[$row['pos_pk']] = $tmp_provider;
		self::$providerObject[$row['pos_pk']]->addRecipient($row['user_id']);
	}

	/**
	 * 
	 */
	private function resetProviderCache()
	{
		self::$providerObject = array();
	}
	
	/**
	 * @param int   $a_form_id
	 * @param array $a_fields
	 * @param bool  $a_is_active
	 */
	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{
		global $DIC;
		$lng = $DIC->language();

		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_FORUM:
				$a_fields['cron_forum_notification'] = $a_is_active ?
					$lng->txt('enabled') :
					$lng->txt('disabled');
				break;
		}
	}

	/**
	 * @param bool $a_currently_active
	 */
	public function activationWasToggled($a_currently_active)
	{		
		global $DIC;
		
		// propagate cron-job setting to object setting
		if((bool)$a_currently_active)
		{
			$DIC->settings()->set('forum_notification', 2);
		}
		else
		{
			$DIC->settings()->set('forum_notification', 1);
		}
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		global $DIC; 
		$lng = $DIC->language();

		$lng->loadLanguageModule('forum');

		$max_notification_age = new ilNumberInputGUI($lng->txt('frm_max_notification_age'), 'max_notification_age');
		$max_notification_age->setSize(5);
		$max_notification_age->setSuffix($lng->txt('frm_max_notification_age_unit'));
		$max_notification_age->setRequired(true);
		$max_notification_age->allowDecimals(false);
		$max_notification_age->setMinValue(1);
		$max_notification_age->setInfo($lng->txt('frm_max_notification_age_info'));
		$max_notification_age->setValue($this->settings->get('max_notification_age', 30));

		$a_form->addItem($max_notification_age);
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 * @return bool
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{
		$this->settings->set('max_notification_age', $a_form->getInput('max_notification_age'));
		return true;
	}
}