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
include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilCourseUserFieldsGUI
* @ingroup ModulesCourse
*/
class ilCourseUserFieldsGUI
{
	private $lng;
	private $tpl;
	private $ctrl;
	private $tabs_gui;
	
	private $obj_id;
	
	private $cdf;
	
	/**
	 *  Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_obj_id)
	{
		global $lng,$tpl,$ilCtrl,$ilTabs;
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('ps');
		
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		
		$this->obj_id = $a_obj_id;
	}
	
	/**
	 * Execute Command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		
		switch($next_class = $this->ctrl->getNextClass($this))
		{
			default:
				if(!$cmd)
				{
					$cmd = 'show';
				}
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Show defined fields
	 *
	 * @access public
	 */
	public function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_list.html','Modules/Course');
		
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TABLE_TITLE',$this->lng->txt('ps_crs_user_fields'));
		$this->tpl->setVariable('HEAD_NAME',$this->lng->txt('ps_cdf_name'));
		$this->tpl->setVariable('HEAD_TYPE',$this->lng->txt('ps_cdf_type'));
		$this->tpl->setVariable('HEAD_REQUIRED',$this->lng->txt('ps_cdf_required'));
		
		$this->tpl->setVariable('ADD',$this->lng->txt('ps_cdf_add_field'));
		$this->tpl->setVariable('LINK_ADD',$this->ctrl->getLinkTarget($this,'fieldSelection'));
		
		
		$fields = ilCourseDefinedFieldDefinition::_getFields($this->obj_id);
		
		if(!count($fields))
		{
			$this->tpl->setCurrentBlock('table_empty');
			$this->tpl->setVariable('EMPTY_TXT',$this->lng->txt('ps_cdf_no_fields'));
			$this->tpl->parseCurrentBlock();
		}
		$counter = 0;
		foreach($fields as $field_obj)
		{
			$this->tpl->setCurrentBlock('table_content');
			$this->tpl->setVariable('ROWCOL',ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable('CHECKBOX',ilUtil::formCheckbox(0,'field_id[]',1));
			$this->tpl->setVariable('NAME',$field_obj->getName());
			$this->tpl->setVariable('TYPE',$field_obj->getType() == IL_CDF_TYPE_SELECT ?
											$this->lng->txt('ps_type_select') :
											$this->lng->txt('ps_type_text'));
			$this->tpl->setVariable('',ilUtil::formCheckbox(0,'field_id['.$field_obj->getId().']',1));
			
			if($field_obj->getType() == IL_CDF_TYPE_SELECT)
			{
				$this->tpl->setCurrentBlock('show_edit');
				
				$this->ctrl->setParameter($this,'field_id',$field_obj->getId());
				$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this,'editField'));
				$this->ctrl->clearParameters($this);
				
				$this->tpl->setVariable('EDIT',$this->lng->txt('edit'));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable('BTN_DELETE',$this->lng->txt('delete'));
		
		if(count($fields))
		{
			$this->tpl->setCurrentBlock('show_save');
			$this->tpl->setVariable('BTN_SAVE',$this->lng->txt('save'));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * Save
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function save()
	{
	 	$this->show();
	 	return true;
	}
	
	/**
	 * Field selection
	 *
	 * @access public
	 * 
	 */
	public function fieldSelection()
	{
		// number of values defaults to 3
		$_SESSION['il_crs_num_values'] = 3;
		
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_selection.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_SELECT_TYPE',$this->lng->txt('ps_cdf_add_field'));
		$this->tpl->setVariable('FIELD_TYPE',$this->lng->txt('ps_field_type'));
		$this->tpl->setVariable('TXT_TEXT',$this->lng->txt('ps_type_txt_long'));
		$this->tpl->setVariable('TXT_SELECT',$this->lng->txt('ps_type_select_long'));
		$this->tpl->setVariable('BTN_MORE',$this->lng->txt('btn_next'));
		$this->tpl->setVariable('BTN_CANCEL',$this->lng->txt('cancel'));
		
		$this->tpl->setVariable("TYPE_TEXT",ilUtil::formRadioButton(1,'field_type',IL_CDF_TYPE_TEXT));
		$this->tpl->setVariable("TYPE_SELECT",ilUtil::formRadioButton(0,'field_type',IL_CDF_TYPE_SELECT));
		 	
	}
	
	/**
	 * Choose Definitions
	 *
	 * @access public
	 * 
	 */
	public function chooseDefinitions()
	{
		switch($field_type = (int) $_REQUEST['field_type'])
		{
			case IL_CDF_TYPE_TEXT:
				$this->addTextField();
				break;

			case IL_CDF_TYPE_SELECT:
				$this->showSelectTable();
				break;
		}
	}

	/**
	 * 
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function addTextField()
	{
		$this->ctrl->setParameter($this,'field_type',(int) $_REQUEST['field_type']);
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_add_text.html','Modules/Course');
		$this->tpl->setVariable('TXT_SELECT_TYPE',$this->lng->txt('ps_new_text_field'));
		$this->tpl->setVariable('TXT_FIELD_NAME',$this->lng->txt('ps_name_field'));
		$this->tpl->setVariable('TXT_REQUIRED',$this->lng->txt('required_field'));
		$this->tpl->setVariable('BTN_ADD',$this->lng->txt('btn_add'));
		$this->tpl->setVariable('BTN_PREVIOUS',$this->lng->txt('btn_previous'));
	}

	/**
	 * Save New Field
	 *
	 * @access public
	 */
	public function saveField()
	{
	 	$cdf = new ilCourseDefinedFieldDefinition($this->obj_id);
	
		if(!strlen($_POST['field_name']))
		{
			sendInfo($this->lng->txt('ps_cdf_no_name_given'));
			$this->chooseDefinitions();
			return false;
		}
		if($_REQUEST['field_type'] == IL_CDF_TYPE_TEXT)
		{
			$cdf->setFieldType(IL_CDF_TYPE_TEXT);
		}
		
		$cdf->setName(ilUtil::stripSlashes($_POST['field_name']));
		$cdf->enableRequired(1);
		$cdf->save();
		
		sendInfo($this->lng->txt('ps_cdf_added_field'));
		$this->show();
		return true;
	}
}


?>