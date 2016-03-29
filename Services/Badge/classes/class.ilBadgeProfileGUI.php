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
	
	
	//
	// list
	// 
	
	protected function listBadges()
	{
		global $ilTabs, $tpl;
		
		$ilTabs->activateSubTab("ilias_badges");
		
		include_once "Services/Badge/classes/class.ilBadgePersonalTableGUI.php";
		$tbl = new ilBadgePersonalTableGUI($this, "listBadges");
		
		$tpl->setContent($tbl->getHTML());		
	}
	
	protected function applyFilter()
	{
		include_once "Services/Badge/classes/class.ilBadgePersonalTableGUI.php";
		$tbl = new ilBadgePersonalTableGUI($this, "listBadges");
		$tbl->resetOffset();
		$tbl->writeFilterToSession();
		$this->listBadges();
	}
	
	protected function resetFilter()
	{
		include_once "Services/Badge/classes/class.ilBadgePersonalTableGUI.php";
		$tbl = new ilBadgePersonalTableGUI($this, "listBadges");
		$tbl->resetOffset();
		$tbl->resetFilter();
		$this->listBadges();
	}
	
	protected function getMultiSelection()
	{
		global $lng, $ilCtrl, $ilUser;
		
		$ids = $_POST["badge_id"];
		if(is_array($ids))
		{
			$res = array();
			include_once "Services/Badge/classes/class.ilBadge.php";
			include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
			foreach($ids as $id)
			{
				$ass = new ilBadgeAssignment($id, $ilUser->getId());
				if($ass->getTimestamp())
				{
					$res[] = $ass;
				}
			}
			
			return $res;
		}
		else
		{
			ilUtil::sendFailure($lng->txt("select_one"), true);
			$ilCtrl->redirect($this, "listBadges");
		}
	}
	
	protected function activate()
	{		
		global $lng, $ilCtrl;
		
		foreach($this->getMultiSelection() as $ass)
		{
			// already active?
			if(!$ass->getPosition())
			{
				$ass->setPosition(999);
				$ass->store();
			}									
		}
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "listBadges");
	}
	
	protected function deactivate()
	{		
		global $lng, $ilCtrl;
		
		foreach($this->getMultiSelection() as $ass)
		{
			// already inactive?
			if($ass->getPosition())
			{
				$ass->setPosition(null);
				$ass->store();
			}									
		}
		
		ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "listBadges");		
	}
	
	
	//
	// (mozilla) backpack
	//
	
	protected function addToBackpackMulti()
	{		
		global $tpl, $ilTabs, $ilCtrl, $lng;
		
		$res = array();
		foreach($this->getMultiSelection() as $ass)
		{
			$url = $this->prepareBadge($ass->getBadgeId());
			if($url !== false)
			{
				$badge = new ilBadge($ass->getBadgeId());
				$titles[] = $badge->getTitle();
				$res[] = $url;
			}
		}
		
		// :TODO: use local copy instead?
		$tpl->addJavascript("https://backpack.openbadges.org/issuer.js", false);	
			
		$tpl->addJavascript("Services/Badge/js/ilBadge.js");	
		$tpl->addOnloadCode("il.Badge.publishMulti(['".implode("','", $res)."']);");		
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listBadges"));
		
		ilUtil::sendInfo(sprintf($lng->txt("badge_add_to_backpack_multi"), implode(", ", $titles)));
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
				foreach($bp_badges as $idx => $badge)
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
	
	protected function prepareBadge($a_badge_id)
	{
		global $ilUser;
		
		// check if current user has given badge
		include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
		$ass = new ilBadgeAssignment($a_badge_id, $ilUser->getId());
		if($ass->getTimestamp())
		{			
			$url = null;
			try 
			{					
				$url = $ass->getStaticUrl();
			} 
			catch (Exception $ex) {
				
			}				
			if($url)
			{				
				return $url;
			}				
		}
		
		return false;		
	}
	
	protected function addToBackpack()
	{
		global $ilCtrl;
		
		if(!$ilCtrl->isAsynch() ||
			!ilBadgeHandler::getInstance()->isObiActive())
		{
			return false;
		}
		
		$res = new stdClass();
		
		$url = false;
		$badge_id = (int)$_GET["id"];
		if($badge_id)
		{
			$url = $this->prepareBadge($badge_id);				
		}
		
		if($url !== false)
		{
			$res->error = false;
			$res->url = $url;
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