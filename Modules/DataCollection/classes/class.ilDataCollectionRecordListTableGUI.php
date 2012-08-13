<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
* Class ilDataCollectionRecordListTableGUI
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @version $Id: 
*
* @extends ilTable2GUI
*
*/


class ilDataCollectionRecordListTableGUI  extends ilTable2GUI
{

    private $table;

	public function  __construct($a_parent_obj, $a_parent_cmd, ilDataCollectionTable $table)
	{
		global $lng, $tpl, $ilCtrl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->table = $table;

		include_once("class.ilDataCollectionDatatype.php");
		include_once("class.ilObjDataCollectionFile.php");
		
	 	$this->parent_obj = $a_parent_obj;
		$this->setFormName('record_list');
		
		$this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");

        foreach($this->table->getVisibleFields() as $field){
            $this->addColumn($field->getTitle());
        }

        $this->addColumn($lng->txt("dcl_edit"));
        $this->addColumn($lng->txt("dcl_delete"));

        $this->setData($table->getRecords());

		$this->addMultiCommand('export', $lng->txt('dcl_export'));
        //leave these two
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

		$ilCtrl->setParameterByClass("ildatacollectionrecordeditgui","table_id", $this->parent_obj->table_id);
		$this->addHeaderCommand($ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", "create"),$lng->txt("dcl_add_new_record"));


	}
	
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow(ilDataCollectionRecord $record)
	{
		global $ilUser, $ilCtrl, $tpl, $lng;

		$this->tpl->setVariable("TITLE", $this->table->getTitle());

		foreach($this->table->getVisibleFields() as $field)
		{
			$this->tpl->setCurrentBlock("field");
			$this->tpl->setVariable("CONTENT", $record->getRecordFieldValue($field->getId()));

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable('EDIT',$lng->txt('edit'));
		$ilCtrl->setParameterByClass('ildatacollectionrecordeditgui', "record_id", $record->getId());
		$this->tpl->setVariable('EDIT_LINK', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'edit'));

        $this->tpl->setVariable('DELETE',$lng->txt('delete'));
		$ilCtrl->setParameterByClass('ildatacollectionrecordeditgui', "record_id", $record->getId());
		$this->tpl->setVariable('DELETE_LINK', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'delete'));
		
		return true;
    }

	
}

?>