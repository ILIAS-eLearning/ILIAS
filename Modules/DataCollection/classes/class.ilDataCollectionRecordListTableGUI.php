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
	public function  __construct($a_parent_obj, $a_parent_cmd, $a_data, $tabledefinition)
	{
		global $lng, $tpl, $ilCtrl;

		parent::__construct($a_parent_obj, $a_parent_cmd);

	 	$this->parent_obj = $a_parent_obj;
		
		$this->setFormName('record_list');
		
		$this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");

		$this->tabledefinition = $tabledefinition;

		//
		// Spalten werden aufgrund von allen verfügbaren array_keys erstellt, ev. manuell oder gefiltert
		//
        //TODO derzeit entspreicht die Reihenfolge der Überschrift nicht der Reihenfolge der Werte.
		if(is_array($tabledefinition))
		{
			foreach($tabledefinition as $key => $value)
			{
				$this->addColumn($value, $key, 'auto');
			}
				$this->addColumn($lng->txt("edit"),  "edit",  "auto");
		}

		$this->addMultiCommand('export', $lng->txt('export'));

		$ilCtrl->setParameterByClass("ildatacollectionrecordeditgui","table_id", $this->parent_obj->table_id);
		$this->addHeaderCommand($ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", "create"),$lng->txt("dcl_add_new_record"));

		$this->setData($a_data);
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow($a_set)
	{
		global $ilUser, $ilCtrl, $tpl, $lng;

		$a_set = (object) $a_set;

		$this->tpl->setVariable("TITLE", $a_set->title);
		//$this->tpl->setVariable("AUTHOR", $a_set['author']);
		foreach($a_set as $a)
		{
			if (!isset($a)) {
				$a = "&nbsp;";
			}
							
			$this->tpl->setCurrentBlock("field");
			$this->tpl->setVariable("CONTENT", $a);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable('EDIT',$lng->txt('edit'));

			$ilCtrl-> setParameterByClass('ildatacollectionrecordeditgui', "record_id", $a_set->id);
		    $this->tpl->setVariable('EDIT_LINK', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'edit'));
		}

		
		return true;
	}
	
	/**
	 * setFilter
	 * a_val = 
	 */
	/*public function setFilter($a_val)
	{
		global $x;
	
		
		return true;
	}*/

	
}

?>