<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for user badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgePersonalTableGUI extends ilTable2GUI
{		
	function __construct($a_parent_obj, $a_parent_cmd, $a_user_id = null)
	{
		global $lng, $ilUser, $ilCtrl, $tpl;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$this->setId("bdgprs");
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setTitle($lng->txt("badge_personal_badges"));		
				
		$this->addColumn($lng->txt("title"), "title");			
		$this->addColumn($lng->txt("object"), "parent_title");			
		$this->addColumn($lng->txt("badge_issued_on"), "issued_on");	
		
		if(ilBadgeHandler::getInstance()->isObiActive())
		{
			$this->addColumn($lng->txt("actions"), "");	
			
			// :TODO: use local copy instead?
			$tpl->addJavascript("https://backpack.openbadges.org/issuer.js", false);	
			
			$tpl->addJavascript("Services/Badge/js/ilBadge.js");			
			$tpl->addOnLoadCode('il.Badge.setUrl("'.
				$ilCtrl->getLinkTarget($this->getParentObject(), "addtoBackpack", "", true, false).
			'")');
		}
		
		$this->setDefaultOrderField("title");
		
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate("tpl.personal_row.html", "Services/Badge");			
		
		$this->getItems($a_user_id);				
	}
	
	function getItems($a_user_id)
	{	
		$data = array();
		
		include_once "Services/Badge/classes/class.ilBadge.php";
		include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
		include_once "Services/Badge/classes/class.ilBadgeRenderer.php";
		foreach(ilBadgeAssignment::getInstancesByUserId($a_user_id) as $ass)
		{
			$badge = new ilBadge($ass->getBadgeId());
			
			$parent = null;
			if($badge->getParentId())
			{
				$parent = $badge->getParentMeta();	
				if($parent["type"] == "bdga")
				{
					$parent = null;
				}				
			}
			
			$data[] = array(
				"id" => $badge->getId(),
				"title" => $badge->getTitle(),
				"image" => $badge->getImagePath(),
				"issued_on" => $ass->getTimestamp(),
				"parent_title" => $parent ? $parent["title"] : null,
				"parent" => $parent,
				"renderer" => new ilBadgeRenderer($ass)
			);			
		}		
		
		$this->setData($data);
	}
	
	function fillRow($a_set)
	{
		global $lng;
		
		$this->tpl->setVariable("PREVIEW", $a_set["renderer"]->getHTML());
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_ISSUED_ON", ilDatePresentation::formatDate(new ilDateTime($a_set["issued_on"], IL_CAL_UNIX)));
		
		if($a_set["parent"])
		{
			$this->tpl->setVariable("TXT_PARENT", $a_set["parent_title"]);
			$this->tpl->setVariable("SRC_PARENT", 
				ilObject::_getIcon($a_set["parent"]["id"], "big", $a_set["parent"]["type"]));			
		}		
		
		if(ilBadgeHandler::getInstance()->isObiActive())
		{
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->setVariable("TXT_PUBLISH", $lng->txt("badge_add_to_backpack"));
		}
	}	
}
	