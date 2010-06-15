<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup Modules
 */
class ilCourseParticipantsGroupsTableGUI extends ilTable2GUI
{
	protected $filter;	     // array
	protected $groups;		 // array
	protected $participants; // array
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $ref_id)
	{
		global $ilCtrl, $ilObjDataCache;

		$this->ref_id = $ref_id;
		$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);

		$this->setId("tblcrsprtgrp");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		// $this->setTitle($lng->txt("tr_summary"));
		$this->setLimit(9999);
		// $this->setShowTemplates(true);

		$this->addColumn("", "");
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("crs_groups_nr"), "groups_nr");
		$this->addColumn($this->lng->txt("groups"));

		// $this->setExternalSorting(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.crs_members_grp_row.html", "Modules/Course");
		$this->setSelectAllCheckbox("usrs");

		$this->initGroups();
		
		$this->addMultiItemSelectionButton("grp_id", $this->groups, "add", $this->lng->txt("crs_add_to_group"));
		$this->initFilter();
	    $this->getItems();
	}

	/**
	 * find groups in course, exclude groups in groups
	 */
	function initGroups()
    {
		global $tree;
		
		$parent_node = $tree->getNodeData($this->ref_id);
		$groups = $tree->getSubTree($parent_node, true, "grp");
		if(sizeof($groups))
		{
			include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
			$this->participants = $this->groups = array();
			foreach($groups as $idx => $group_data)
			{
				// check for group in group
				if($group_data["parent"] != $this->ref_id  && $tree->checkForParentType($group_data["ref_id"], "grp"))
				{
					unset($groups[$idx]);
				}
				else
				{
					$this->groups[$group_data["ref_id"]] = $group_data["title"];
					$gobj = ilGroupParticipants::_getInstanceByObjId($group_data["obj_id"]);
					$this->participants[$group_data["ref_id"]] = $gobj->getParticipants();
				}
			}
		}
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;

		$item = $this->addFilterItemByMetaType("name", ilTable2GUI::FILTER_TEXT);
		$this->filter["name"] = $item->getValue();

		if($this->groups)
		{
			$item = $this->addFilterItemByMetaType("group", ilTable2GUI::FILTER_SELECT);
			$item->setOptions(array("" => $lng->txt("all"))+$this->groups);
			$this->filter["group"] = $item->getValue();
		}
	}

	/**
	 * Build item rows for given object and filter(s)
	 */
	function getItems()
	{
        if($this->groups)
		{
			include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
			$part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
			$members = $part->getMembers();
			if(count($members))
			{
				include_once './Services/User/classes/class.ilUserUtil.php';
                $usr_data = array();
				foreach(ilUserUtil::getNamePresentation($members, false, false, "", true) as $usr_id => $name)
				{
					$user_groups = array();
					foreach(array_keys($this->participants) as $group_id)
					{
						if(in_array($usr_id, $this->participants[$group_id]))
						{
							$user_groups[$group_id] = $this->groups[$group_id];
						}
					}
					
					if((!$this->filter["name"] || stristr($name, $this->filter["name"])) &&
						(!$this->filter["group"] || array_key_exists($this->filter["group"], $user_groups)))
					{
						$usr_data[] = array("usr_id" => $usr_id,
							"name" => $name,
							"groups" => $user_groups,
							);
					}
				}

				// ???
				$usr_data = array_slice($usr_data, (int)$this->getOffset(), (int)$this->getLimit());

				$this->setMaxCount(sizeof($members));
				$this->setData($usr_data);
			}

			return $titles;
		}
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);

		$this->tpl->setVariable("TXT_USER", $a_set["name"]);
		$this->tpl->setVariable("VAL_GROUP_NUMBER", sizeof($a_set["groups"]));

		if(sizeof($a_set["groups"]))
		{
			$this->tpl->setCurrentBlock("groups");
			foreach($a_set["groups"] as $grp_id => $title)
			{
				$this->tpl->setVariable("TXT_GROUP_TITLE", $title);
				$this->tpl->setVariable("TXT_GROUP_REMOVE", $lng->txt("remove"));

				$ilCtrl->setParameter($this->parent_obj, "usr_id", $a_set["usr_id"]);
				$ilCtrl->setParameter($this->parent_obj, "grp_id", $grp_id);
				$this->tpl->setVariable("URL_REMOVE", $ilCtrl->getLinkTarget($this->parent_obj, "confirmremove"));
				$ilCtrl->setParameter($this->parent_obj, "grp_id", "");
				$ilCtrl->setParameter($this->parent_obj, "usr_id", "");

				$this->tpl->parseCurrentBlock();
			}
		}
	}
}
?>
