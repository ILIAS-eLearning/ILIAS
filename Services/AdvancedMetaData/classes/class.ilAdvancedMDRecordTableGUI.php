<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

class ilAdvancedMDRecordTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd = '')
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn('','f',1);
	 	$this->addColumn($this->lng->txt('title'),'title',"30%");
	 	$this->addColumn($this->lng->txt('md_fields'),'fields',"35%");
	 	$this->addColumn($this->lng->txt('md_adv_active'),'active',"5%");
	 	$this->addColumn($this->lng->txt('md_obj_types'),'obj_types',"30%");
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_records_row.html","Services/AdvancedMetaData");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("desc");
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{
		foreach(ilAdvancedMDRecord::_getAssignableObjectTypes() as $obj_type)
		{
			$this->tpl->setCurrentBlock('ass_obj_types');
			$this->tpl->setVariable('VAL_OBJ_TYPE',$this->lng->txt('objs_'.$obj_type));
			$this->tpl->setVariable('ASS_ID',$a_set['id']);
			$this->tpl->setVariable('ASS_NAME',$obj_type);
			if(in_array($obj_type,$a_set['obj_types']))
			{
				$this->tpl->setVariable('ASS_CHECKED','checked="checked"');
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable('VAL_DESCRIPTION',$a_set['description']);
		}
		$defs = ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($a_set['id']);
		if(!count($defs))
		{
			$this->tpl->setVariable('TXT_FIELDS',$this->lng->txt('md_adv_no_fields'));
		}
		foreach($defs as $definition_obj)
		{
			$this->tpl->setCurrentBlock('field_entry');
			$this->tpl->setVariable('FIELD_NAME',$definition_obj->getTitle());
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable('ACTIVE_CHECKED',$a_set['active'] ? ' checked="checked" ' : '');
		$this->tpl->setVariable('ACTIVE_ID',$a_set['id']);
		
		$this->ctrl->setParameter($this->parent_obj,'record_id',$a_set['id']);
		$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->parent_obj,'editRecord'));
		$this->tpl->setVariable('TXT_EDIT_RECORD',$this->lng->txt('edit'));
	}
	
	/**
	 * Parse records
	 *
	 * @access public
	 * @param array array of record objects
	 * 
	 */
	public function parseRecords($a_records)
	{
	 	foreach($a_records as $record)
	 	{
			$tmp_arr['id'] = $record->getRecordId();
			$tmp_arr['active'] = $record->isActive();
			$tmp_arr['title'] = $record->getTitle();
			$tmp_arr['description'] = $record->getDescription();
			$tmp_arr['fields'] = array();
			$tmp_arr['obj_types'] = $record->getAssignedObjectTypes();
			
			$records_arr[] = $tmp_arr;
	 	}
	 	$this->setData($records_arr ? $records_arr : array());
	}
	
} 


?>