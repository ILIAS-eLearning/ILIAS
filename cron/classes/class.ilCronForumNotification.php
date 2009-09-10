<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* 
* @author Jens Conze <jc@databay.de>
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @package ilias
*/
class ilCronForumNotification
{
	public function sendMails($res)
	{		
		global $ilias, $rbacsystem, $ilAccess, $ilDB;
		
		static $cache = array();

		include_once 'Modules/Forum/classes/class.ilObjForum.php';
		include_once 'Services/Mail/classes/class.ilMail.php';
		include_once 'Services/User/classes/class.ilObjUser.php';
		include_once 'Services/Language/classes/class.ilLanguage.php';
		
		$forumObj = new ilObjForum((int)$_GET['ref_id']);
		$frm = $forumObj->Forum;

		$numRows = 0;
		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		while($row = $ilDB->fetchAssoc($res))
		{
			// don not send a notification to the post author
			if($row['pos_usr_id'] != $row['user_id'])
			{					
				// GET AUTHOR OF NEW POST
				$row['pos_usr_name'] = ilObjUser::_lookupLogin($row['pos_usr_id']);
				
				// get all references of obj_id
				if(!isset($cache[$row['obj_id']]))		
					$cache[$row['obj_id']] = ilObject::_getAllReferences($row['obj_id']);				
				
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
				
				if($send_mail)
				{
					// SEND NOTIFICATIONS BY E-MAIL
					$user_language = ilObjUser::_lookupLanguage($row['user_id']);
					if(!is_object($lng[$user_language]))
					{
						$lng[$user_language] = new ilLanguage($user_language);
						$lng[$user_language]->loadLanguageModule('forum');
					}					
					
					$frm->setLanguage($lng[$user_language]);			
					$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row['user_id']),'','',
													   $frm->formatNotificationSubject($row),
													   $frm->formatNotification($row, 1),
													   array(),array('normal'));
					$numRows++;					
				}
			}
		}
		
		return $numRows;
	}

	public function sendNotifications()
	{
		global $ilDB, $ilLog, $ilSetting;

		if(!($lastDate = $ilSetting->get('cron_forum_notification_last_date')))
		{
			$lastDate = null;
		}

		$numRows = 0;		
		$datecondition_frm = '';
		$types = array();
		$values = array();		
		 	
		if($lastDate != null && 
		   checkDate(date('m', strtotime($lastDate)), date('d', strtotime($lastDate)), date('Y', strtotime($lastDate))))
		{
			$datecondition_frm = ' frm_posts.pos_date >= %s AND ';
			$types[] = 'timestamp';
			$values[] = $lastDate;
		}
		
		/*** FORUMS ***/
		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	'.$datecondition_frm.' frm_posts.pos_thr_fk = frm_threads.thr_pk 
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
			WHERE 	'.$datecondition_frm.' frm_posts.pos_thr_fk = frm_threads.thr_pk 
			AND		frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk 
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$numRows += $this->sendMails($res);

		//$ilSetting->set('cron_forum_notification_last_date', date('Y-m-d H:i:s'));

		$ilLog->write(__METHOD__.': Send '.$numRows.' messages.');		

		return true;
	}
}
?>
