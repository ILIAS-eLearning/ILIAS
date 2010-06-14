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
		$this->addColumn($this->lng->txt("groups_nr"), "groups_nr");
		$this->addColumn($this->lng->txt("groups"));

		// $this->setExternalSorting(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.crs_members_grp_row.html", "Modules/Course");
		$this->initFilter();

	    $this->getItems($ref_id, $this->getCurrentFilter());
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;

		/*
		$item = $this->addFilterItemByMetaType("country", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["country"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("registration_filter", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["registration"] = $item->getDate();

		$item = $this->addFilterItemByMetaType("gender", ilTable2GUI::FILTER_SELECT, true);
		$item->setOptions(array("" => $lng->txt("all"), "m" => $lng->txt("gender_m"), "f" => $lng->txt("gender_f")));
		$this->filter["gender"] = $item->getValue();

        $item = $this->addFilterItemByMetaType("city", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["city"] = $item->getValue();
		
        $item = $this->addFilterItemByMetaType("language", ilTable2GUI::FILTER_LANGUAGE, true);
		$this->filter["language"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("user_total", ilTable2GUI::FILTER_NUMBER_RANGE, true);
		$this->filter["user_total"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("trac_first_access", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["first_access"] = $item->getDate();

		$item = $this->addFilterItemByMetaType("trac_last_access", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["last_access"] = $item->getDate();
		 */
	}

	/**
	 * Build item rows for given object and filter(s)
	 *
	 * @param	array	&$rows
	 * @param	array	$filter
	 */
	function getItems($ref_id, array $filter = NULL)
	{
		global $lng;

		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		$part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
		$members = $part->getMembers();
		if(count($members))
		{
			// :TODO: offset/limit

			// what about userQuery?!

			include_once './Services/User/classes/class.ilUserUtil.php';
			foreach(ilUserUtil::getNamePresentation($members, false, false, "", true) as $usr_id => $name)
			{
				$usr_data[] = array("usr_id" => $usr_id, "name" => $name);
			}
			
			$this->setMaxCount(sizeof($members));
			$this->setData($usr_data);
		}
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);

		$this->tpl->setVariable("TXT_USER", $a_set["name"]);
		


	}

	protected function getCurrentFilter()
	{
	 
	}
}
?>
