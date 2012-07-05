<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* Class ilDataCollectionFieldListTableGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @extends ilTable2GUI
* @ilCtrl_Calls ilDateTime
*/


class ilDataCollectionFieldListTableGUI  extends ilTable2GUI
{
	public function  __construct($a_parent_obj, $a_parent_cmd, $a_data)
	{
		global $lng, $tpl, $ilCtrl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

	 	$this->parent_obj = $a_parent_obj;
	 	
	 	$this->addColumn($lng->txt("dcl_order"),  "order",  "30px");
		$this->addColumn($lng->txt("dcl_title"),  "title",  "auto");
		$this->addColumn($lng->txt("dcl_description"),  "description",  "auto");
		$this->addColumn($lng->txt("dcl_field_datatype"),  "datatype",  "auto");
		$this->addColumn($lng->txt("dcl_required"),  "required",  "auto");
		$this->addColumn($lng->txt("edit"), 	 "edit", 	 "auto");

		$ilCtrl->setParameterByClass("ildatacollectionfieldeditgui","table_id", $this->parent_obj->table_id);
		$this->addHeaderCommand($ilCtrl->getLinkTargetByClass("ildatacollectionfieldeditgui", "create"),$lng->txt("dcl_add_new_field"));


		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setFormName('field_list');
		$this->setData($a_data);
		$this->order = 10;
		
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php');
		$this->datatypes = ilDataCollectionDatatype::getAllDatatypes();
	
		$this->setTitle($lng->txt("dcl_table_list_fields"));

		$this->setRowTemplate("tpl.field_list_row.html", "Modules/DataCollection");
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilUser;
		
		$a_set = (object) $a_set;
		
		
		$this->tpl->setVariable('NAME', "order[".$a_set->id."]");
		$this->tpl->setVariable('VALUE', $this->order);
		$this->order = $this->order + 10;
		$this->tpl->setVariable('TITLE', $a_set->title);
		$this->tpl->setVariable('DESCRIPTION', $a_set->description);
		$this->tpl->setVariable('DATATYPE', $this->datatypes[$a_set->datatype_id]['title']);
		
		switch($a_set->required)
		{
			case 0:
				$required = ilUtil::getImagePath('icon_not_ok.gif');
				break;
			case 1:
				$required = ilUtil::getImagePath('icon_ok.gif');
				break;
		}

		$this->tpl->setVariable('REQUIRED', $required);
		$this->tpl->setVariable('EDIT', $lng->txt('edit'));
		$ilCtrl->setParameterByClass("ildatacollectionfieldeditgui", "field_id", $a_set->id);
		$this->tpl->setVariable('EDIT_LINK', $ilCtrl->getLinkTargetByClass("ildatacollectionfieldeditgui", 'edit'));
	}	
}

?>