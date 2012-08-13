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
	public function  __construct($a_parent_obj, $a_parent_cmd, $table_id)
	{
		global $lng, $tpl, $ilCtrl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

	 	$this->parent_obj = $a_parent_obj;
	 	
	 	$this->addColumn($lng->txt("dcl_order"),  "order",  "30px");
        $this->addColumn($lng->txt("dcl_title"),  "title",  "auto");
        $this->addColumn($lng->txt("dcl_visible"),  "visible",  "30px");
        $this->addColumn($lng->txt("dcl_description"),  "description",  "auto");
        $this->addColumn($lng->txt("dcl_field_datatype"),  "datatype",  "auto");
        $this->addColumn($lng->txt("dcl_required"),  "required",  "auto");
		//$this->addColumn($lng->txt("edit"), 	 "edit", 	 "auto");
        //$this->addColumn($lng->txt("delete"), 	 "delete", 	 "auto");
        $this->addColumn($lng->txt("actions"), 	 "actions", 	 "auto");
        

		$ilCtrl->setParameterByClass("ildatacollectionfieldeditgui","table_id", $this->parent_obj->table_id);
        $ilCtrl->setParameterByClass("ildatacollectionfieldlistgui","table_id", $this->parent_obj->table_id);
        $this->addHeaderCommand($ilCtrl->getLinkTargetByClass("ildatacollectionfieldeditgui", "create"),$lng->txt("dcl_add_new_field"));
        $this->setFormAction($ilCtrl->getFormActionByClass("ildatacollectionfieldlistgui"));
        $this->addCommandButton("save", $lng->txt("dcl_save"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setFormName('field_list');

        //those two are important as we get our data as objects not as arrays.
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

		$this->order = 10;
        $this->fillData($table_id);
		
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionDatatype.php');

		$this->setTitle($lng->txt("dcl_table_list_fields"));

		$this->setRowTemplate("tpl.field_list_row.html", "Modules/DataCollection");
	}


    private function fillData($table_id){
        $table = new ilDataCollectionTable($table_id);
        $this->setData($table->getFields(false));
    }
	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow(ilDataCollectionField $a_set)
	{
		global $lng, $ilCtrl, $ilUser, $ilAccess;
		
		$this->tpl->setVariable('NAME', "order[".$a_set->getId()."]");
		$this->tpl->setVariable('VALUE', $this->order);
		$this->order = $this->order + 10;
        $this->tpl->setVariable("CHECKBOX_NAME", "visible[".$a_set->getId()."]");
        if($a_set->isVisible())
        {
            $this->tpl->setVariable("CHECKBOX_CHECKED", "checked");
        }
		$this->tpl->setVariable('TITLE', $a_set->getTitle());
		$this->tpl->setVariable('DESCRIPTION', $a_set->getDescription());
		$this->tpl->setVariable('DATATYPE', $a_set->getDatatypeTitle());
		
		switch($a_set->getRequired())
		{
			case 0:
				$required = ilUtil::getImagePath('icon_not_ok.png');
				break;
			case 1:
				$required = ilUtil::getImagePath('icon_ok.png');
				break;
		}

		
		$this->tpl->setVariable('REQUIRED', $required);
		$ilCtrl->setParameterByClass("ildatacollectionfieldeditgui", "field_id", $a_set->getId());
		
		if(!$a_set->isStandardField())
		{
			include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($a_set->getId());
			$alist->setListTitle($lng->txt("actions"));
			
			//if($ilAccess->checkAccess('add_entry', "", $_GET['ref_id']))
			//{
				$alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass("ildatacollectionfieldeditgui", 'edit'));
				$alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass("ildatacollectionfieldeditgui", 'delete'));
			//}

			$this->tpl->setVariable("ACTIONS", $alist->getHTML());
		}
	}
}

?>