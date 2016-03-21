<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once "Services/Badge/classes/class.ilBadgeHandler.php";
	
/**
 * Class ilBadgeProfileGUI
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeProfileGUI
{	
	function executeCommand()
	{
		global $ilCtrl, $lng;
		
		$lng->loadLanguageModule("badge");
		
		switch($ilCtrl->getNextClass())
		{			
			default:			
				$this->setTabs();
				$cmd = $ilCtrl->getCmd("listBadges");							
				$this->$cmd();
				break;
		}
	}
	
	protected function setTabs()
	{
		global $ilTabs, $lng, $ilCtrl;
		
		$ilTabs->addSubTab("ilias_badges",
			$lng->txt("badge_personal_badges"),
			$ilCtrl->getLinkTarget($this, "listBadges"));
		
		if(ilBadgeHandler::getInstance()->isObiActive())
		{
			$ilTabs->addSubTab("backpack_badges",
				$lng->txt("badge_backpack_list"),
				$ilCtrl->getLinkTarget($this, "listBackpackGroups"));
		}
	}
	
	protected function listBackpackGroups()
	{
		global $ilUser, $lng, $tpl, $ilCtrl, $ilTabs;
		
		if(!ilBadgeHandler::getInstance()->isObiActive())
		{
			$ilCtrl->redirect($this, "listBadges");
		}		
		
		$ilTabs->activateSubTab("backpack_badges");
				
		include_once "Services/Badge/classes/class.ilBadgeBackpack.php";
		$bp = new ilBadgeBackpack($ilUser->getEmail());
		$bp_groups = $bp->getGroups();

		if(!is_array($bp_groups))
		{
			ilUtil::sendInfo(sprintf($lng->txt("badge_backpack_connect_failed"), $ilUser->getEmail()));
			return;		
		}		
		else if(!sizeof($bp_groups))
		{
			ilUtil::sendInfo($lng->txt("badge_backpack_no_groups"));
			return;
		}

		$tmpl = new ilTemplate("tpl.badge_backpack.html", true, true, "Services/Badge");

		$tmpl->setVariable("BACKPACK_TITLE", $lng->txt("badge_backpack_list"));
		
		ilDatePresentation::setUseRelativeDates(false);

		foreach($bp_groups as $group_id => $group)
		{			
			$bp_badges = $bp->getBadges($group_id);
			if(sizeof($bp_badges))
			{
				foreach($bp_badges as $badge)
				{
					$tmpl->setCurrentBlock("badge_bl");
					$tmpl->setVariable("BADGE_TITLE", $badge["title"]);
					$tmpl->setVariable("BADGE_DESC", $badge["description"]);
					$tmpl->setVariable("BADGE_IMAGE", $badge["image_url"]);
					$tmpl->setVariable("BADGE_CRITERIA", $badge["criteria_url"]);
					$tmpl->setVariable("BADGE_ISSUER", $badge["issuer_name"]);
					$tmpl->setVariable("BADGE_ISSUER_URL", $badge["issuer_url"]);
					$tmpl->setVariable("BADGE_DATE", ilDatePresentation::formatDate($badge["issued_on"]));
					$tmpl->parseCurrentBlock();							
				}
			}

			$tmpl->setCurrentBlock("group_bl");
			$tmpl->setVariable("GROUP_TITLE", $group["title"]);
			$tmpl->parseCurrentBlock();			
		}

		$tpl->setContent($tmpl->get());		
	}
	
	protected function listBadges()
	{
		global $ilTabs, $tpl;
		
		$ilTabs->activateSubTab("ilias_badges");
		
		include_once "Services/Badge/classes/class.ilBadgePersonalTableGUI.php";
		$tbl = new ilBadgePersonalTableGUI($this, "listBadges");
		
		$tpl->setContent($tbl->getHTML());		
	}
	
	protected function addToBackpack()
	{
		global $ilCtrl, $ilUser;
		
		if(!$ilCtrl->isAsynch() ||
			!ilBadgeHandler::getInstance()->isObiActive())
		{
			return false;
		}
		
		$res = new stdClass();
		
		$badge_id = (int)$_GET["id"];
		if($badge_id)
		{
			// check if current user has given badge
			include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
			$ass = new ilBadgeAssignment($badge_id, $ilUser->getId());
			if($ass->getTimestamp())
			{			
				$url = null;
				try 
				{					
					$url = $ass->getStaticUrl();
				} 
				catch (Exception $ex) {
					$res->error = true;
					$res->message = $ex->getMessage();	
				}				
				if($url)
				{				
					$res->error = false;
					$res->url = $url;
				}				
			}
			else
			{
				$res->error = true;
				$res->message = "missing badge assignment";
			}
		}
		else
		{
			$res->error = true;
			$res->message = "missing badge id";
		}
		
		echo json_encode($res);
		exit();				
	}
	
}