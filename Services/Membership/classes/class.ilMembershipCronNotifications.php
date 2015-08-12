<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Course/group notifications
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilMembershipCronNotifications extends ilCronJob
{
	public function getId()
	{
		return "mem_notification";
	}
	
	public function getTitle()
	{
		global $lng;
		
		return $lng->txt("enable_course_group_notifications");
	}
	
	public function getDescription()
	{
		global $lng;
		
		return $lng->txt("enable_course_group_notifications_desc");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}

	public function run()
	{				
		global $lng, $ilDB;
		
		$status = ilCronJobResult::STATUS_NO_ACTION;
		$status_details = null;
	
		$setting = new ilSetting("cron");		
		$last_run = $setting->get(get_class($this));
				
		// no last run?
		if(!$last_run)
		{
			$last_run = date("Y-m-d H:i:s", strtotime("yesterday"));
			
			$status_details = "No previous run found - starting from yesterday.";
		}
		// migration: used to be date-only value 
		else if(strlen($last_run) == 10)
		{
			$last_run .= " 00:00:00";
			
			$status_details = "Switched from daily runs to open schedule.";			
		}
		
		include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
		$objects = ilMembershipNotifications::getActiveUsersforAllObjects();
		if(sizeof($objects))
		{				
			// gather news for each user over all objects
			
			$user_news_aggr = array();
						
			include_once "Services/News/classes/class.ilNewsItem.php";
			foreach($objects as $ref_id => $user_ids)
			{
				// gather news per object
				$news_item = new ilNewsItem();
				if($news_item->checkNewsExistsForGroupCourse($ref_id, $last_run))
				{
					foreach($user_ids as $user_id)
					{
						// gather news for user
						$user_news = $news_item->getNewsForRefId($ref_id,
							false, false, $last_run, false, false, false, false,
							$user_id);
						if($user_news)
						{
							$user_news_aggr[$user_id][$ref_id] = $user_news;								
						}
					}
				}				
			}
			unset($objects);


			// send mails (1 max for each user)
			
			$old_lng = $lng;
			$old_dt = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);

			if(sizeof($user_news_aggr))
			{
				foreach($user_news_aggr as $user_id => $user_news)
				{
					$this->sendMail($user_id, $user_news, $last_run);
				}
			
				// mails were sent - set cron job status accordingly
				$status = ilCronJobResult::STATUS_OK;							
			}

			ilDatePresentation::setUseRelativeDates($old_dt);
			$lng = $old_lng;
		}

		// save last run
		$setting->set(get_class($this), date("Y-m-d H:i:s")); 

		$result = new ilCronJobResult();
		$result->setStatus($status);	
		
		if($status_details)
		{
			$result->setMessage($status_details);
		}
		
		return $result;
	}
	
	protected function parseNewsItem(array $a_item, $a_is_sub = false)
	{
		global $lng;
		
		if(!$a_is_sub)
		{
			$title = ilNewsItem::determineNewsTitle(
				$a_item["context_obj_type"],
				$a_item["title"], 
				$a_item["content_is_lang_var"], 
				$a_item["agg_ref_id"], 
				$a_item["aggregation"]
			);					
		}
		else
		{
			$title = ilNewsItem::determineNewsTitle(
				$a_item["context_obj_type"],
				$a_item["title"], 
				$a_item["content_is_lang_var"]
			);										
		}
		
		$content = ilNewsItem::determineNewsContent(
			$a_item["context_obj_type"], 
			$a_item["content"], 
			$a_item["content_text_is_lang_var"]
		);			
		$item_obj_title = trim(ilObject::_lookupTitle($a_item["context_obj_id"]));	
		$item_obj_type = $a_item["context_obj_type"];
		
		$title = trim($title);
		$content = trim($content);
		
		$res = "";
		switch($item_obj_type)
		{
			case "frm":
				if(!$a_is_sub)
				{
					$res =  $lng->txt("obj_".$item_obj_type).
						' "'.$item_obj_title.'": '.$title;	
				}
				else
				{
					$res .= '"'.$title.'": "'.$content.'"';
				}
				break;
				
			case "file":
				if(!is_array($a_item["aggregation"]) ||
					sizeof($a_item["aggregation"]) == 1)
				{
					$res =  $lng->txt("obj_".$item_obj_type).
						' "'.$item_obj_title.'" - '.$title;	
				}
				else
				{
					$res = $title;
				}
				break;
				
			default:					
				$res =  $lng->txt("obj_".$item_obj_type).
					' "'.$item_obj_title.'"';	
				if($title)
				{
					$res .= ': "'.$title.'"';
				}
				if($content)
				{
					$res .= ' - '.$content;
				}
				break;
		}		
		if($res)
		{
			$res = $a_is_sub 
				? "- ".$res
				: "* ".$res;
		}
		
		// sub-items
		$sub = null;
		if($a_item["aggregation"])
		{				
			$do_sub = true;			
			if($item_obj_type == "file" &&
				sizeof($a_item["aggregation"]) == 1)
			{
				$do_sub = false;
			}										
			if($do_sub)
			{
				$sub = array();						
				foreach($a_item["aggregation"] as $subitem)
				{								
					$res .= "\n ".$this->parseNewsItem($subitem, true);
				}	
			}
		}
	
		return trim($res);
	}

	/**
	 * Send news mail for 1 user and n objects
	 *
	 * @param int $a_user_id
	 * @param array $a_objects
	 * @param string $a_last_run
	 */
	protected function sendMail($a_user_id, array $a_objects, $a_last_run)
	{
		global $lng, $ilUser, $ilClientIniFile, $tree;
		
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();		
		$ntf->setLangModules(array("crs", "news"));
		// no single object anymore
		// $ntf->setRefId($a_ref_id);	
		// $ntf->setGotoLangId('url');
		// $ntf->setSubjectLangId('crs_subject_course_group_notification');
		
		// user specific language
		$lng = $ntf->getUserLanguage($a_user_id);
		
		include_once './Services/Locator/classes/class.ilLocatorGUI.php';			
		require_once "HTML/Template/ITX.php";
		require_once "./Services/UICore/classes/class.ilTemplateHTMLITX.php";
		require_once "./Services/UICore/classes/class.ilTemplate.php";
		require_once "./Services/Link/classes/class.ilLink.php";
				
		$tmp = array();
		foreach($a_objects as $parent_ref_id => $news)
		{						
			$parent = array();
			
			// path		
			$path = array();
			foreach($tree->getPathId($parent_ref_id) as $node)
			{
				$path[] = $node;
			}			
			$path = implode("-", $path);		
			
			$parent_obj_id = ilObject::_lookupObjId($parent_ref_id);			
			$parent_type = ilObject::_lookupType($parent_obj_id);
			
			$parent["title"] = $lng->txt("obj_".$parent_type).' "'.ilObject::_lookupTitle($parent_obj_id).'"';
			$parent["url"] = "  ".$lng->txt("crs_course_group_notification_link")." ".ilLink::_getStaticLink($parent_ref_id);
			
			// news summary		
			$parsed = array();
			foreach($news as $item)
			{
				$parsed_item = $this->parseNewsItem($item);
				$parsed[md5($parsed_item)] = $parsed_item; 				
			}	
			$parent["news"] = implode("\n", $parsed);
			
			$tmp[$path] = $parent;										
		}
		
		ksort($tmp);
		$counter = 0;
		$obj_index = array();
		foreach($tmp as $path => $item)
		{			
			$counter++;
			
			$txt .= $counter." ".$item["title"]."\n".
				$item["url"]."\n\n".
				$item["news"]."\n\n";
			
			$obj_index[] = $counter." ".$item["title"];
		}				
		
		$intro = $lng->txt("crs_intro_course_group_notification_for")."\n".
			sprintf(
				$lng->txt("crs_intro_course_group_notification_period"), 
				ilDatePresentation::formatDate(new ilDateTime($a_last_run, IL_CAL_DATETIME)),
				ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX))
			);		
		
		$ntf->setIntroductionDirect($intro);
		$ntf->addAdditionalInfo("crs_intro_course_group_notification_index", 
			trim(implode("\n", $obj_index)),
			true);
		$ntf->addAdditionalInfo("", 
			trim($txt), 
			true);
		
		// :TODO: does it make sense to add client to subject?
		$client = $ilClientIniFile->readVariable('client', 'name');
		$subject = sprintf($lng->txt("crs_subject_course_group_notification"), $client);
			
		// #10044
		$mail = new ilMail($ilUser->getId());
		$mail->enableSOAP(false); // #10410
		$mail->sendMail(ilObjUser::_lookupLogin($a_user_id), 
			null, 
			null,
			$subject, 
			$ntf->composeAndGetMessage($a_user_id, null, "read", true), 
			null, 
			array("system"));
	}
	
	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{				
		global $lng;
		
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_COURSE:				
			case ilAdministrationSettingsFormHandler::FORM_GROUP:								
				$a_fields["enable_course_group_notifications"] = $a_is_active ? 
					$lng->txt("enabled") :
					$lng->txt("disabled");
				break;
		}
	}
	
	public function activationWasToggled($a_currently_active)
	{
		global $ilSetting;
				
		// propagate cron-job setting to object setting
		$ilSetting->set("crsgrp_ntf", (bool)$a_currently_active);		
	}
}

?>