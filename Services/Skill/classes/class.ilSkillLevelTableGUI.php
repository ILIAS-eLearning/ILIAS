<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Link/classes/class.ilLink.php");

/**
 * Skill level table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillLevelTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 */
	function __construct($a_skill_id, $a_parent_obj, $a_parent_cmd, $a_tref_id = 0)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		$this->skill_id = $a_skill_id;
		$this->skill = new ilBasicSkill($a_skill_id);
		$this->tref_id = $a_tref_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
		$this->setData($this->getSkillLevelData());
		$this->setTitle($lng->txt("skmg_skill_levels"));
		if ($this->tref_id == 0)
		{
			$this->setDescription($lng->txt("skmg_from_lower_to_higher_levels"));
		}

		if ($this->tref_id == 0)
		{
			$this->addColumn("", "", "1", true);
			$this->addColumn($this->lng->txt("skmg_nr"));
		}
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("description"));
//		$this->addColumn($this->lng->txt("skmg_trigger"));
//		$this->addColumn($this->lng->txt("skmg_certificate"))
		$this->addColumn($this->lng->txt("resources"));
		$this->addColumn($this->lng->txt("actions"));
		$this->setDefaultOrderField("nr");
		$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_level_row.html", "Services/Skill");
		$this->setEnableTitle(true);

		if ($this->tref_id == 0)
		{
			$this->addMultiCommand("confirmLevelDeletion", $lng->txt("delete"));
			if (count($this->getData()) > 0)
			{
				$this->addCommandButton("updateLevelOrder", $lng->txt("skmg_update_order"));
			}
		}
	}

	/**
	 * Should this field be sorted numeric?
	 *
	 * @return	boolean		numeric ordering; default is false
	 */
	function numericOrdering($a_field)
	{
		if ($a_field == "nr")
		{
			return true;
		}
		return false;
	}

	/**
	 * Get skill level data
	 *
	 * @param
	 * @return
	 */
	function getSkillLevelData()
	{
		$levels = $this->skill->getLevelData();
	
		// add ressource data
		$res = array();
		include_once("./Services/Skill/classes/class.ilSkillResources.php");
		$resources = new ilSkillResources($this->skill_id, $this->tref_id);
		foreach($resources->getResources() as $level_id => $item)
		{			
			$res[$level_id] = array_keys($item);
		}
		
		foreach($levels as $idx => $item)
		{
			$levels[$idx]["ressources"] = $res[$item["id"]];
		}
		
		return $levels;
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		if ($this->tref_id == 0)
		{
			$this->tpl->setCurrentBlock("cb");
			$this->tpl->setVariable("CB_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("nr");
			$this->tpl->setVariable("ORD_ID", $a_set["id"]);
			$this->tpl->setVariable("VAL_NR", ((int) $a_set["nr"]) * 10);
			$this->tpl->parseCurrentBlock();

		}
		
		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
		$ilCtrl->setParameter($this->parent_obj, "level_id", $a_set["id"]);
		if ($this->tref_id == 0)
		{
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "editLevel"));
		}
		else
		{
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "showLevelResources"));			
		}
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
/*		$this->tpl->setVariable("TXT_CERTIFICATE",
			ilBasicSkill::_lookupCertificate($this->skill->getId(),
			$a_set["id"])
			? $lng->txt("yes")
			: $lng->txt("no"));*/

/*		$trigger = ilBasicSkill::lookupLevelTrigger((int) $a_set["id"]);
		if (ilObject::_lookupType($trigger["obj_id"]) != "crs" ||
			ilObject::_isInTrash($trigger["ref_id"]))
		{
			$trigger = array();
		}

		// trigger
		if ($trigger["obj_id"] > 0)
		{
			$this->tpl->setVariable("TXT_TRIGGER",
				ilObject::_lookupTitle($trigger["obj_id"]));
		}*/
		
		if(is_array($a_set["ressources"]))
		{
			$this->tpl->setCurrentBlock("ressource_bl");
			foreach($a_set["ressources"] as $rref_id)
			{
				$robj_id = ilObject::_lookupObjId($rref_id);
				$this->tpl->setVariable("RSRC_IMG", ilUtil::img(ilObject::_getIcon($robj_id, "tiny")));
				$this->tpl->setVariable("RSRC_TITLE", ilObject::_lookupTitle($robj_id));
				$this->tpl->setVariable("RSRC_URL", ilLink::_getStaticLink($rref_id));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

}
?>
