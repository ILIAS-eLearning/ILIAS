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
	
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_parent_ref_id, ilBadge $a_award_bagde = null, $a_parent_obj_id = null)
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
			$parent = "";
			if($a_parent_obj_id)
			{
				$title = ilObject::_lookupTitle($a_parent_obj_id);
				if(!$title)
				{
					include_once "Services/Object/classes/class.ilObjectDataDeletionLog.php";
					$title = ilObjectDataDeletionLog::get($a_parent_obj_id);
					if($title)
					{
						$title = $title["title"];
					}
				}
				$parent = $title.": ";				
			}
			$this->setTitle($parent.$lng->txt("users"));		
		}
		
		$this->addColumn($lng->txt("name"), "name");			
		$this->addColumn($lng->txt("login"), "login");			
		$this->addColumn($lng->txt("obj_bdga"), "");			
		$this->setDefaultOrderField("name");
				
		$this->setRowTemplate("tpl.user_row.html", "Services/Badge");	
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setFilterCommand("apply".ucfirst($this->getParentCmd()));
		$this->setResetCommand("reset".ucfirst($this->getParentCmd()));

		$this->initFilter();		
				
		$this->getItems($a_parent_ref_id, $this->award_badge, $a_parent_obj_id);					
	}
	
	public function initFilter()
	{
		global $lng;
		
		$name = $this->addFilterItemByMetaType("name", self::FILTER_TEXT, false, $lng->txt("name"));		
		$this->filter["name"] = $name->getValue();		
	}
	
	function getItems($a_parent_ref_id, ilBadge $a_award_bagde = null, $a_parent_obj_id = null)
	{		
		$data = array();
					
		if(!$a_parent_obj_id)
		{
			$a_parent_obj_id = ilObject::_lookupObjId($a_parent_ref_id);
		}
		
		// repository context: walk tree for available users
		if($a_parent_ref_id)
		{
			$user_ids = ilBadgeHandler::getInstance()->getUserIds($a_parent_ref_id, $a_parent_obj_id);		
		}			
		
		$badges = array();
		include_once "Services/Badge/classes/class.ilBadge.php";
		foreach(ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge)
		{
			$badges[$badge->getId()] = $badge; 
		}

		$assignments = array();
		include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
		foreach(ilBadgeAssignment::getInstancesByParentId($a_parent_obj_id) as $ass)
		{
			$assignments[$ass->getUserId()][] = $ass;			
		}
		
		// administration context: show only existing assignments
		if(!$user_ids)
		{
			$user_ids = array_keys($assignments);
		}

		include_once "Services/Badge/classes/class.ilBadgeRenderer.php";
		include_once "Services/User/classes/class.ilUserQuery.php";
		$uquery = new ilUserQuery();
		$uquery->setUserFilter($user_ids);
		
		if($this->filter["name"])
		{
			$uquery->setTextFilter($this->filter["name"]);
		}
		
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
				foreach($assignments[$id] as $user_ass)
				{						
					$data[$id]["badges"][] = new ilBadgeRenderer($user_ass);
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
			foreach($a_set["badges"] as $badge_ass)
			{
				$this->tpl->setVariable("BADGE", $badge_ass->getHTML());
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}
