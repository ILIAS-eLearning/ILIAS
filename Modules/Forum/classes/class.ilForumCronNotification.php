<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Forum notifications
 *
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumCronNotification extends ilCronJob
{
	/**
	 * @var ilSetting
	 */
	protected $settings;

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
		global $lng;
			
		return $lng->txt("cron_forum_notification");
	}
	
	public function getDescription()
	{
		global $lng;
			
		return $lng->txt("cron_forum_notification_crob_desc");
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

	public function run()
	{
		global $ilDB, $ilLog, $ilSetting, $lng;

		$status = ilCronJobResult::STATUS_NO_ACTION;

		$lng->loadLanguageModule('forum');

		if(!($last_run_datetime = $ilSetting->get('cron_forum_notification_last_date')))
		{
			$last_run_datetime = null;
		}

		$numRows = 0;
		$types   = array();
		$values  = array();

		if($last_run_datetime != null &&
		   checkDate(date('m', strtotime($last_run_datetime)), date('d', strtotime($last_run_datetime)), date('Y', strtotime($last_run_datetime))))
		{
			$threshold = max(strtotime($last_run_datetime), strtotime('-' . (int)$this->settings->get('max_notification_age', 30) . ' days', time()));
		}
		else
		{
			$threshold = strtotime('-' . (int)$this->settings->get('max_notification_age', 30) . ' days', time());
		}

		$date_condition = ' frm_posts.pos_date >= %s AND ';
		$types[]        = 'timestamp';
		$values[]       = date('Y-m-d H:i:s', $threshold);

		$cj_start_date = date('Y-m-d H:i:s');

		/*** FORUMS ***/
		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	'.$date_condition.' frm_posts.pos_thr_fk = frm_threads.thr_pk
			AND 	frm_threads.thr_top_fk = frm_data.top_pk 
			AND 	frm_data.top_frm_fk = frm_notification.frm_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows += $this->sendMails($res);

		/*** THREADS ***/
		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE 	'.$date_condition.' frm_posts.pos_thr_fk = frm_threads.thr_pk
			AND		frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk 
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows += $this->sendMails($res);

		$ilSetting->set('cron_forum_notification_last_date', $cj_start_date);

		$mess = 'Send '.$numRows.' messages.';
		$ilLog->write(__METHOD__.': '.$mess);		

		$result = new ilCronJobResult();
		if($numRows)
		{
			$status = ilCronJobResult::STATUS_OK;
			$result->setMessage($mess);
		};				
		$result->setStatus($status);		
		return $result;
	}
	
	protected function sendMails($res)
	{		
		global $ilAccess, $ilDB, $lng;

		static $cache = array();
		static $attachments_cache = array();

		include_once 'Modules/Forum/classes/class.ilObjForum.php';
		include_once 'Services/Mail/classes/class.ilMail.php';
		include_once 'Services/User/classes/class.ilObjUser.php';
		include_once 'Services/Language/classes/class.ilLanguage.php';
		
		$forumObj = new ilObjForum();
		$frm = $forumObj->Forum;

		$numRows = 0;
		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		$mail_obj->enableSOAP(false);
		while($row = $ilDB->fetchAssoc($res))
		{
			// don not send a notification to the post author
			if($row['pos_display_user_id'] != $row['user_id'])
			{
				// GET AUTHOR OF NEW POST	
				if($row['pos_display_user_id'])
				{
					$row['pos_usr_name'] = ilObjUser::_lookupLogin($row['pos_display_user_id']);
				}
				else if(strlen($row['pos_usr_alias']))
				{
					$row['pos_usr_name'] = $row['pos_usr_alias'].' ('.$lng->txt('frm_pseudonym').')';
				}
				
				if($row['pos_usr_name'] == '')
				{
					$row['pos_usr_name'] = $lng->txt('forums_anonymous');
				}
				
				// get all references of obj_id
				if(!isset($cache[$row['obj_id']]))		
					$cache[$row['obj_id']] = ilObject::_getAllReferences($row['obj_id']);				
				
				// check for attachments
				$has_attachments = false;
				if(!isset($attachments_cache[$row['obj_id']][$row['pos_pk']]))
				{
					$fileDataForum = new ilFileDataForum($row['obj_id'], $row['pos_pk']);
					$filesOfPost   = $fileDataForum->getFilesOfPost();
					foreach($filesOfPost as $attachment)
					{
						$attachments_cache[$row['obj_id']][$row['pos_pk']][] = $attachment['name'];
						$has_attachments = true;
					}
				}
				else 
				{
					$has_attachments = true;
				}
		
				// do rbac check before sending notification
				$send_mail = false;
				foreach((array)$cache[$row['obj_id']] as $ref_id)
				{
					if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
					{
						$row['ref_id'] = $ref_id;
						$send_mail = true;
						break;
					}
				}
				$attached_files = array();
				if($has_attachments == true)
				{
					$attached_files = $attachments_cache[$row['obj_id']][$row['pos_pk']];
				}
	
				if($send_mail)
				{
					$frm->setLanguage(ilForum::_getLanguageInstanceByUsrId($row['user_id']));
					$mail_obj->sendMail(
						ilObjUser::_lookupLogin($row['user_id']), '', '',
						$frm->formatNotificationSubject($row),
						$frm->formatNotification($row, 1, $attached_files, $row['user_id']),
						array(), array(
							'normal'
						));
					$numRows++;
				}
			}
		}
		
		return $numRows;
	}

	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_FORUM:
				$a_fields['cron_forum_notification'] = $a_is_active ?
					$lng->txt('enabled') :
					$lng->txt('disabled');
				break;
		}
	}

	public function activationWasToggled($a_currently_active)
	{		
		global $ilSetting;
		
		// propagate cron-job setting to object setting
		if((bool)$a_currently_active)
		{
			$ilSetting->set('forum_notification', 2);
		}
		else
		{
			$ilSetting->set('forum_notification', 1);
		}
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

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
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{
		$this->settings->set('max_notification_age', $a_form->getInput('max_notification_age'));
		return true;
	}
}