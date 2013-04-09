<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Mail/classes/class.ilMailNotification.php';
include_once 'Services/Mail/classes/class.ilMail.php';

/**
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id:$
* @package ilias
*/
class ilCronCourseGroupNotification extends ilMailNotification
{	
	public function sendNotifications()
	{
		global $ilDB, $lng;
		
		$setting = new ilSetting("cron");		
		$last_run = $setting->get(get_class($this));
				
		// #10284 - we already did send today, do nothing
		if($last_run == date("Y-m-d"))
		{			
			return;
		}
		
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
							}
						}
					}
				}
			}
			
			$lng = $old_lng;
		}
		
		// save last run
		$setting->set(get_class($this), date("Y-m-d")); 

		return true;
	}

	/**
	 * Send news mail for 1 object and 1 user
	 *
	 * @param int $a_user_id
	 * @param int $a_ref_id
	 * @param array $news
	 */
	public function sendMail($a_user_id, $a_ref_id, array $news)
	{
		global $lng, $ilUser;

		$obj_id = ilObject::_lookupObjId($a_ref_id);
		$obj_type = ilObject::_lookupType($obj_id);

		$this->initLanguage($a_user_id);
		$this->getLanguage()->loadLanguageModule("crs");
		$this->getLanguage()->loadLanguageModule("news");
		
		// needed for ilNewsItem
		$lng = $this->getLanguage();
		
		$this->initMail();

		$obj_title = $this->getLanguageText($obj_type)." \"".ilObject::_lookupTitle($obj_id)."\"";
				
		$this->setRecipients($a_user_id);
		$this->setSubject(sprintf($this->getLanguageText("crs_subject_course_group_notification"), $obj_title));

		$this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
		$this->appendBody("\n\n");

		$this->appendBody(sprintf($this->getLanguageText("crs_intro_course_group_notification_for"), $obj_title));
		$this->appendBody("\n\n");

		// ilDatePresentation::setUseRelativeDates(false);

		// news summary
		$counter = 1;
		foreach($news as $item)
		{
			$title = ilNewsItem::determineNewsTitle($item["context_obj_type"],
				$item["title"], $item["content_is_lang_var"], $item["agg_ref_id"], 
				$item["aggregation"]);
			$content = ilNewsItem::determineNewsContent($item["context_obj_type"], 
				$item["content"], $item["content_text_is_lang_var"]);
			
			/* process sub-item info
			if($item["aggregation"])
			{
				$sub = array();
				foreach($item["aggregation"] as $subitem)
				{
					$sub_id = ilObject::_lookupObjId($subitem["ref_id"]);
					$sub_title = ilObject::_lookupTitle($sub_id);
					
					// to include posting title
					if($subitem["context_obj_type"] == "frm")
					{
						$sub_title = ilNewsItem::determineNewsTitle($subitem["context_obj_type"],
							$subitem["title"], $subitem["content_is_lang_var"]);
					}					
								
					$sub[] = $sub_title;
					
					$sub_content = ilNewsItem::determineNewsContent($subitem["context_obj_type"], 
						$subitem["content"], $subitem["content_text_is_lang_var"]);								
					if($sub_content)
					{
						$sub[] = strip_tags($sub_content);
					}
				}
				$content .= "\n".implode("\n\n", $sub);
			} 
			*/
			
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
			
			$this->appendBody("----------------------------------------------------------------------------------------------");
			$this->appendBody("\n\n");
			$this->appendBody('#'.$counter." - ".$loc." ".$obj_title."\n\n");
			$this->appendBody($title);
			if($content)
			{
				$this->appendBody("\n");			
				$this->appendBody($content);
			}			
			$this->appendBody("\n\n");

			++$counter;
		}
		$this->appendBody("----------------------------------------------------------------------------------------------");
		$this->appendBody("\n\n");

		// link to object
		$this->appendBody($this->getLanguageText("crs_course_group_notification_link"));
		$this->appendBody("\n");
		$object_link = ilUtil::_getHttpPath();
		$object_link .= "/goto.php?target=".$obj_type."_".$a_ref_id.
			"&client_id=".CLIENT_ID;
		$this->appendBody($object_link);

		$this->appendBody("\n\n");
		$this->appendBody(ilMail::_getAutoGeneratedMessageString($this->getLanguage()));
		$this->appendBody(ilMail::_getInstallationSignature());
		
		// #10044
		$mail = new ilMail($ilUser->getId());
		$mail->enableSOAP(false); // #10410
		$mail->sendMail(ilObjUser::_lookupLogin($a_user_id),null,null,
			$this->getSubject(),$this->getBody(),null,array("system"));
	}
}

?>