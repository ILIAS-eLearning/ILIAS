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
include_once('Modules/Course/classes/class.ilCourseAgreement.php');

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
		unset($_SESSION['il_cdf_delete']);
		unset($_SESSION['il_cdf_select_num_values']);
		
		
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
		if(ilCourseAgreement::_hasAgreementsByObjId($this->obj_id))
		{
			$this->tpl->setCurrentBlock('warning_modify');
			$this->tpl->setVariable('TXT_WARNING',$this->lng->txt('ps_cdf_warning_modify'));
			$this->tpl->parseCurrentBlock();
		}
		$counter = 0;
		foreach($fields as $field_obj)
		{
			if($field_obj->getType() == IL_CDF_TYPE_SELECT or 1)
			{
				$this->tpl->setCurrentBlock('show_edit');
				
				$this->ctrl->setParameter($this,'field_id',$field_obj->getId());
				$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this,'editField'));
				$this->ctrl->clearParameters($this);
				
				$this->tpl->setVariable('EDIT',$this->lng->txt('edit'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock('table_content');
			$this->tpl->setVariable('ROWCOL',ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable('CHECKBOX',ilUtil::formCheckbox(0,'field_id[]',$field_obj->getId()));
			$this->tpl->setVariable('NAME',$field_obj->getName());
			$this->tpl->setVariable('TYPE',$field_obj->getType() == IL_CDF_TYPE_SELECT ?
											$this->lng->txt('ps_type_select') :
											$this->lng->txt('ps_type_text'));
			$this->tpl->setVariable('REQUIRED',ilUtil::formCheckbox((int) $field_obj->isRequired(),'required['.$field_obj->getId().']',1));
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
	 * Edit field
	 *
	 * @access public
	 */
	public function editField()
	{
		$_SESSION['il_cdf_select_num_values'] = $_SESSION['il_cdf_select_num_values'] ? $_SESSION['il_cdf_select_num_values'] : 1; 
		
		if(!$_GET['field_id'])
		{
			ilUtil::sendFailure('No field given');
			$this->show();
			return false;
		}
		$cdf = new ilCourseDefinedFieldDefinition($this->obj_id,(int) $_GET['field_id']);
		
		$name = isset($_POST['cmd']) ? ilUtil::prepareFormOutput($_POST['field_name'],true) : ilUtil::prepareFormOutput($cdf->getName());
		$required = isset($_POST['cmd']) ? $_POST['required'] : $cdf->isRequired();		

		$this->ctrl->setParameter($this,'field_id',(int) $_GET['field_id']);
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_edit_select_field.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		
						
		$this->tpl->setVariable('TXT_FIELD_NAME',$this->lng->txt('ps_name_field'));
		$this->tpl->setVariable('FIELD_NAME_VALUE',$name);
	
		$this->tpl->setVariable('TXT_REQUIRED',$this->lng->txt('required_field'));
		$this->tpl->setVariable('BTN_ADD',$this->lng->txt('save'));
		$this->tpl->setVariable('BTN_PREVIOUS',$this->lng->txt('cancel'));
		
		$this->tpl->setVariable('REQUIRED',ilUtil::formCheckbox($required,'required',1));
		
		// Old values
		$i = 1;
		foreach($cdf->getValues() as $key => $value)
		{
			$this->ctrl->setParameter($this,'del_field',$i - 1);
			
			$this->tpl->setCurrentBlock('values');
			
			$this->ctrl->setParameter($this,'del_field',$i - 1);
			$this->tpl->setVariable('DELETE',$this->lng->txt('delete'));
			$this->tpl->setVariable('LINK_DELETE',$this->ctrl->getLinkTarget($this,'deleteField'));
			$this->tpl->setVariable('FIELD_NAME',ilUtil::prepareFormOutput($value));
			$this->tpl->setVariable("TXT_VALUES",$this->lng->txt('ps_cdf_value').' '.($i++));
			$this->tpl->parseCurrentBlock();
		}
		switch($cdf->getType())
		{
			case IL_CDF_TYPE_SELECT:
				$this->tpl->setVariable('TXT_SELECT_TYPE',$this->lng->txt('ps_edit_select_field'));
				$this->tpl->setVariable('BTN_NEW_VALUE',$this->lng->txt('ps_btn_add_value'));
				// New values
				for($j = 0; $j < $_SESSION['il_cdf_select_num_values'];$j++)
				{
					$this->tpl->setCurrentBlock('new_values');
					$this->tpl->setVariable('COUNTER',$j);
					$this->tpl->setVariable("TXT_NEW_VALUES",$this->lng->txt('ps_cdf_value').' '.($i + $j));
					$this->tpl->setVariable("NEW_FIELD_NAME",$_POST['new_field_values'][$j]);
					$this->tpl->parseCurrentBlock();
				}
				break;
			
			case IL_CDF_TYPE_TEXT:
				$this->tpl->setVariable('TXT_SELECT_TYPE',$this->lng->txt('ps_edit_text_field'));
				break;
		}		
	}
	
	/**
	 * Delete field
	 *
	 * @access public
	 */
	public function deleteField()
	{
		if(!$_GET['field_id'])
		{
			ilUtil::sendFailure('No field given');
			$this->show();
			return false;
		}
		$cdf = new ilCourseDefinedFieldDefinition($this->obj_id,(int) $_GET['field_id']);
		$cdf->deleteValue((int) $_GET['del_field']);
		
		ilUtil::sendSuccess($this->lng->txt('ps_cdf_deleted_field'));
		$this->editField();
	}
	

	/**
	 * Increment values
	 *
	 * @access public
	 */
	public function addNewValue()
	{
		$_SESSION['il_cdf_select_num_values'] += 1;
		$this->editField();
		return true;
	}
	
	/**
	 * Update Field
	 *
	 * @access public
	 */
	public function updateField()
	{
		if(!$_GET['field_id'])
		{
			ilUtil::sendFailure('No field given');
			$this->show();
			return false;
		}
		
	 	$cdf = new ilCourseDefinedFieldDefinition($this->obj_id,$_GET['field_id']);
	
		if(!strlen($_POST['field_name']))
		{
			ilUtil::sendFailure($this->lng->txt('ps_cdf_no_name_given'));
			$this->editField();
			return false;
		}

		switch($cdf->getType())
		{
			case IL_CDF_TYPE_SELECT:
				$values = $cdf->prepareValues($_POST['new_field_values']);
				$cdf->appendValues($values);
				break;
			default:
				break;
			
		}
		
		$cdf->setName(ilUtil::stripSlashes($_POST['field_name']));
		$cdf->enableRequired((int) $_POST['required']);
		$cdf->update();
		
		// Finally reset member agreements
		ilCourseAgreement::_deleteByObjId($this->obj_id);
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->show();
		return true;
	}
	
	
	
	
	/**
	 * Confirm delete
	 *
	 * @access public
	 */
	public function confirmDelete()
	{
		if(!count($_POST['field_id']))
		{
			ilUtil::sendFailure($this->lng->txt('ps_cdf_select_one'));
			$this->show();
			return false;
		}
		
		ilUtil::sendQuestion($this->lng->txt('ps_cdf_delete_sure'));
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_confirm_delete.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_NAME',$this->lng->txt('ps_cdf_name'));
		$this->tpl->setVariable('TXT_TYPE',$this->lng->txt('ps_cdf_type'));
		$this->tpl->setVariable('DELETE',$this->lng->txt('delete'));
		$this->tpl->setVariable('CANCEL',$this->lng->txt('cancel'));
		
		$counter = 0;
		foreach($_POST['field_id'] as $field_id)
		{
			$tmp_field = new ilCourseDefinedFieldDefinition($this->obj_id,$field_id);
			
			$this->tpl->setCurrentBlock('del_row');
			$this->tpl->setVariable('CSS_ROW',ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable('DEL_NAME',$tmp_field->getName());
			$this->tpl->setVariable('DEL_TYPE',$tmp_field->getType() == IL_CDF_TYPE_SELECT ?
											$this->lng->txt('ps_type_select') :
											$this->lng->txt('ps_type_text'));
			$this->tpl->parseCurrentBlock();
		}
		
		$_SESSION['il_cdf_delete'] = $_POST['field_id'];
	}
	
	/**
	 * Delete course fields
	 *
	 * @access public
	 */
	public function delete()
	{
		if(!count($_SESSION['il_cdf_delete']))
		{
			ilUtil::sendFailure($this->lng->txt('ps_cdf_select_one'));
			$this->show();
			return false;
		}
		foreach($_SESSION['il_cdf_delete'] as $field_id)
		{
			$tmp_field = new ilCourseDefinedFieldDefinition($this->obj_id,$field_id);
			$tmp_field->delete();
		}
		
		ilCourseAgreement::_deleteByObjId($this->obj_id);
		
		ilUtil::sendSuccess($this->lng->txt('ps_cdf_deleted'));
		unset($_SESSION['il_cdf_delete']);
		
		$this->show();
		return true;
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
		$fields = ilCourseDefinedFieldDefinition::_getFields($this->obj_id);
		foreach($fields as $field_obj)
		{
			$field_obj->enableRequired((bool) isset($_POST['required'][$field_obj->getId()]));
			$field_obj->update();
		}
		
		ilCourseAgreement::_deleteByObjId($this->obj_id);
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
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
		$_SESSION['il_cdf_select_num_values'] = 3;
		
		
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
				$this->addSelectField();
				break;
		}
	}
	
	/*
	 * @access public
	 */
	public function addSelectField()
	{
		$_SESSION['il_cdf_select_num_values'] = $_SESSION['il_cdf_select_num_values'] ? $_SESSION['il_cdf_select_num_values'] : 3; 
		
		$this->ctrl->setParameter($this,'field_type',(int) $_REQUEST['field_type']);

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_add_select.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_SELECT_TYPE',$this->lng->txt('ps_new_select_field'));
		$this->tpl->setVariable('TXT_FIELD_NAME',$this->lng->txt('ps_name_field'));
		$this->tpl->setVariable('TXT_REQUIRED',$this->lng->txt('required_field'));
		$this->tpl->setVariable('BTN_ADD',$this->lng->txt('save'));
		$this->tpl->setVariable('BTN_PREVIOUS',$this->lng->txt('btn_previous'));
		$this->tpl->setVariable('BTN_NEW_VALUE',$this->lng->txt('ps_btn_add_value'));
		
		$this->tpl->setVariable('REQUIRED',ilUtil::formCheckbox($_POST['required'] ? 1 : 0,'required',1));
		$this->tpl->setVariable('FIELD_NAME_VALUE',$_POST['field_name']);
		
		for($i = 0; $i < $_SESSION['il_cdf_select_num_values'];$i++)
		{
			$this->tpl->setCurrentBlock("values");
			$this->tpl->setVariable('COUNTER',$i);
			$this->tpl->setVariable("TXT_VALUES",$this->lng->txt('ps_cdf_value').' '.($i + 1));
			$this->tpl->setVariable("FIELD_NAME",$_POST['field_values'][$i]);
			$this->tpl->parseCurrentBlock();
		}
		
	}
	
	/**
	 * Increment values
	 *
	 * @access public
	 */
	public function addValue()
	{
		$_SESSION['il_cdf_select_num_values'] += 1;
		$this->addSelectField();
		return true;
	}
	
	

	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function addTextField()
	{
		$this->ctrl->setParameter($this,'field_type',(int) $_REQUEST['field_type']);
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_fields_add_text.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_SELECT_TYPE',$this->lng->txt('ps_new_text_field'));
		$this->tpl->setVariable('TXT_FIELD_NAME',$this->lng->txt('ps_name_field'));
		$this->tpl->setVariable('TXT_REQUIRED',$this->lng->txt('required_field'));
		$this->tpl->setVariable('BTN_ADD',$this->lng->txt('btn_add'));
		$this->tpl->setVariable('BTN_PREVIOUS',$this->lng->txt('btn_previous'));
		
		$this->tpl->setVariable('REQUIRED',ilUtil::formCheckbox(0,'required',1));
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
			ilUtil::sendFailure($this->lng->txt('ps_cdf_no_name_given'));
			$this->chooseDefinitions();
			return false;
		}
		if($_REQUEST['field_type'] == IL_CDF_TYPE_TEXT)
		{
			$cdf->setType(IL_CDF_TYPE_TEXT);
		}
		else
		{
			$cdf->setType(IL_CDF_TYPE_SELECT);
			$values = $cdf->prepareValues($_POST['field_values']);
			$cdf->setValues($values);
		}
		
		$cdf->setName(ilUtil::stripSlashes($_POST['field_name']));
		$cdf->enableRequired((int) $_POST['required']);
		$cdf->save();
		
		ilUtil::sendSuccess($this->lng->txt('ps_cdf_added_field'));
		$this->show();
		return true;
	}
}


?>