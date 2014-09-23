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
				
		// #10284 - we already did send today, do nothing
		if($last_run == date("Y-m-d"))
		{			
			// #14005
			$status_details = "Did already run today.";
		}
		else
		{
			// gather objects and participants with notification setting
			$objects = array();
			$set = $ilDB->query("SELECT usr_id,keyword FROM usr_pref".
				" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_%").
				" AND value = ".$ilDB->quote("1", "text"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$ref_id = substr($row["keyword"], 11);
				$type = ilObject::_lookupType($ref_id, true);
				if($type)
				{
					$objects[$type][$ref_id][] = $row["usr_id"];
				}
			}

			$counter = 0;
			if(sizeof($objects))
			{
				$old_lng = $lng;

				include_once "Services/News/classes/class.ilNewsItem.php";
				foreach($objects as $type => $ref_ids)
				{
					// type is not needed for now
					foreach($ref_ids as $ref_id => $user_ids)
					{
						// gather news per object
						$news_item = new ilNewsItem();
						if($news_item->checkNewsExistsForGroupCourse($ref_id))
						{
							foreach($user_ids as $user_id)
							{
								// gather news for user
								$user_news = $news_item->getNewsForRefId($ref_id,
									false, false, 1, false, false, false, false,
									$user_id);
								if($user_news)
								{
									$this->sendMail($user_id, $ref_id, $user_news);
									$counter++;
								}
							}
						}
					}
				}

				$lng = $old_lng;
			}

			// save last run
			$setting->set(get_class($this), date("Y-m-d")); 

			if($counter)
			{
				$status = ilCronJobResult::STATUS_OK;
			}			
		}
		
		$result = new ilCronJobResult();
		$result->setStatus($status);	
		
		if($status_details)
		{
			$result->setMessage($status_details);
		}
		
		return $result;
	}

	/**
	 * Send news mail for 1 object and 1 user
	 *
	 * @param int $a_user_id
	 * @param int $a_ref_id
	 * @param array $news
	 */
	protected function sendMail($a_user_id, $a_ref_id, array $news)
	{
		global $lng, $ilUser;

		$obj_id = ilObject::_lookupObjId($a_ref_id);
		$obj_type = ilObject::_lookupType($obj_id);
						
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();		
		$ntf->setLangModules(array("crs", "news"));
		$ntf->setRefId($a_ref_id);	
		$ntf->setGotoLangId('url');
		$ntf->setSubjectLangId('crs_subject_course_group_notification');
		
		// user specific language
		$lng = $ntf->getUserLanguage($a_user_id);
			
		$obj_title = $lng->txt($obj_type)." \"".ilObject::_lookupTitle($obj_id)."\"";		
		$ntf->setIntroductionDirect(sprintf($lng->txt("crs_intro_course_group_notification_for"), $obj_title));
		
		$subject = sprintf($lng->txt("crs_subject_course_group_notification"), $obj_title);
		
		// news summary
		$counter = 1;
		$txt = "";
		foreach($news as $item)
		{
			$title = ilNewsItem::determineNewsTitle($item["context_obj_type"],
				$item["title"], $item["content_is_lang_var"], $item["agg_ref_id"], 
				$item["aggregation"]);
			$content = ilNewsItem::determineNewsContent($item["context_obj_type"], 
				$item["content"], $item["content_text_is_lang_var"]);
			
			$obj_id = ilObject::_lookupObjId($item["ref_id"]);
			$obj_title = ilObject::_lookupTitle($obj_id);
			
			// path
			include_once './Services/Locator/classes/class.ilLocatorGUI.php';			
			$cont_loc = new ilLocatorGUI();
			$cont_loc->addContextItems($item["ref_id"], true);
			$cont_loc->setTextOnly(true);
			
			// #9954/#10044
			// see ilInitialisation::requireCommonIncludes()
			@include_once "HTML/Template/ITX.php";		// new implementation
			if (class_exists("HTML_Template_ITX"))
			{
				include_once "./Services/UICore/classes/class.ilTemplateHTMLITX.php";
			}
			else
			{
				include_once "HTML/ITX.php";		// old implementation
				include_once "./Services/UICore/classes/class.ilTemplateITX.php";
			}
			require_once "./Services/UICore/classes/class.ilTemplate.php";			
			$loc = "[".$cont_loc->getHTML()."]";
						
			if($counter > 1)
			{
				$txt .= $ntf->getBlockBorder();
			}
			$txt .= '#'.$counter." - ".$loc." ".$obj_title."\n\n";
			$txt .= $title;
			if($content)
			{
				$txt .= "\n".$content;
			}			
			$txt .= "\n\n";

			++$counter;
		}
		$ntf->addAdditionalInfo("news", $txt, true);
		
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