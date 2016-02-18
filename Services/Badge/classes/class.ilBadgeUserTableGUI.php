<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for badge user listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgeUserTableGUI extends ilTable2GUI
{		
	protected $award_badge; // [ilBadge]
	
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_parent_obj_id, ilBadge $a_award_bagde = null)
	{
		global $ilCtrl, $lng;
		
		$this->setId("bdgusr");
		$this->award_badge = $a_award_bagde;
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
			
		$this->setLimit(9999);		
		
		if($this->award_badge)
		{				
			$this->setTitle($lng->txt("badge_award_badge").": ".$a_award_bagde->getTitle());		
			
			$this->addColumn("", "", 1);
			
			$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));			
			$this->addMultiCommand("assignBadge", $lng->txt("badge_award_badge"));					
			$this->addMultiCommand("confirmDeassignBadge", $lng->txt("badge_remove_badge"));
		}
		else
		{			
			$this->setTitle($lng->txt("users"));		
		}
		
		$this->addColumn($lng->txt("name"), "name");			
		$this->addColumn($lng->txt("login"), "login");			
		$this->addColumn($lng->txt("obj_bdga"), "");			
		$this->setDefaultOrderField("name");
		
		$this->setRowTemplate("tpl.user_row.html", "Services/Badge");			
				
		$this->getItems($a_parent_obj_id, $this->award_badge);				
	}
	
	function getItems($a_parent_obj_id, ilBadge $a_award_bagde = null)
	{		
		$data = array();
		
		$assignments = array();
		include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
		foreach(ilBadgeAssignment::getInstancesByParentId($a_parent_obj_id) as $ass)
		{
			$assignments[$ass->getUserId()][] = $ass->getBadgeId();			
		}
						
		$user_ids = $a_award_bagde
			?  $a_award_bagde->getTypeInstance()->getAvailableUserIds($a_parent_obj_id)
		    : array_keys($assignments);
		
		if($user_ids)
		{
			$badges = array();
			foreach(ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge)
			{
				$badges[$badge->getId()] = $badge; 
			}
			
			include_once "Services/User/classes/class.ilUserQuery.php";
			$uquery = new ilUserQuery();
			$uquery->setUserFilter($user_ids);
			$tmp = $uquery->query();
			foreach($tmp["set"] as $user)
			{
				$id = $user["usr_id"];
				$data[$id] = array(
					"id" => $id,
					"name" => $user["lastname"].", ".$user["firstname"],
					"login" => $user["login"],
					"badges" => array()
				);
				
				// badges?
				if(array_key_exists($id, $assignments))
				{
					foreach($assignments[$id] as $badge_id)
					{						
						$data[$id]["badges"][] = $badges[$badge_id];
					}
				}
			}
		}
		
		$this->setData($data);		
	}
	
	protected function fillRow($a_set)
	{							
		if($this->award_badge)
		{
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		}
		
		$this->tpl->setVariable("TXT_NAME", $a_set["name"]);
		$this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);
		
		if(sizeof($a_set["badges"]))
		{
			$this->tpl->setCurrentBlock("badges_bl");
			foreach($a_set["badges"] as $badge)
			{
				$this->tpl->setVariable("IMG_BADGE", $badge->getImagePath());
				$this->tpl->setVariable("TXT_BADGE", $badge->getTitle());
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}
