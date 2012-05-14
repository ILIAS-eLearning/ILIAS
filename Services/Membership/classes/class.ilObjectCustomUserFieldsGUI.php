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
include_once('Services/Membership/classes/class.ilMemberAgreement.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjectCustomUserFieldsGUI
* @ingroup ServicesMembership
*/
class ilObjectCustomUserFieldsGUI
{
	const MODE_CREATE = 1;
	const MODE_UPDATE = 2;
	
	private $form = null;
	
	private $lng;
	private $tpl;
	private $ctrl;
	private $tabs_gui;
	
	private $obj_id;
	private $ref_id;
	
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
		$this->lng->loadLanguageModule(ilObject::_lookupType($a_obj_id));
		
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		
		$this->obj_id = $a_obj_id;

		// Currently only supported for container objects
		$refs = ilObject::_getAllReferences($this->obj_id);
		$this->ref_id = end($refs);
	}
	
	/**
	 * Execute Command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		global $ilErr, $ilAccess, $lng;

		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($lng->txt('permission_denied'),$ilErr->WARNING);
		}

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
	 * Get obj_id of container
	 * @return 
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	/**
	 * Show list of custom fields
	 * @return 
	 */
	protected function show()
	{
		if(ilMemberAgreement::_hasAgreementsByObjId($this->getObjId()))
		{
			ilUtil::sendInfo($this->lng->txt('ps_cdf_warning_modify'));
		}
		$this->listFields();		
	}
	
	/**
	 * List existing custom fields
	 * @return 
	 */
	protected function listFields()
	{
		global $ilToolbar;
		
		$ilToolbar->addButton(
			$this->lng->txt('ps_cdf_add_field'),
			$this->ctrl->getLinkTarget($this,'addField')
		);
		
		include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsTableGUI.php';
		$table = new ilObjectCustomUserFieldsTableGUI($this,'listFields');
		$table->parse(ilCourseDefinedFieldDefinition::_getFields($this->getObjId()));
		$this->tpl->setContent($table->getHTML());
	}
	
	/**
	 * Save Field settings (currently only required status)
	 * @return 
	 */
	protected function saveFields()
	{
		$fields = ilCourseDefinedFieldDefinition::_getFields($this->getObjId());
		foreach($fields as $field_obj)
		{
			$field_obj->enableRequired((bool) isset($_POST['required'][$field_obj->getId()]));
			$field_obj->update();
		}
		
		ilMemberAgreement::_deleteByObjId($this->getObjId());
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->listFields();
	 	return true;
	}
	
	/**
	 * Show delete confirmation screen 
	 * @return 
	 */
	protected function confirmDeleteFields()
	{
		if(!count($_POST['field_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('ps_cdf_select_one'));
			$this->listFields();
			return false;
		}
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('ps_cdf_delete_sure'));
		
		foreach($_POST['field_ids'] as $field_id)
		{
			$tmp_field = new ilCourseDefinedFieldDefinition($this->getObjId(),$field_id);
			
			$confirm->addItem('field_ids[]', $field_id, $tmp_field->getName());
		}
		
		$confirm->setConfirm($this->lng->txt('delete'), 'deleteFields');
		$confirm->setCancel($this->lng->txt('cancel'), 'listFields');
		$this->tpl->setContent($confirm->getHTML());
	}
	
	/**
	 * Delete selected fields
	 * @return 
	 */
	protected function deleteFields()
	{
		foreach((array) $_POST['field_ids'] as $field_id)
		{
			$tmp_field = new ilCourseDefinedFieldDefinition($this->obj_id,$field_id);
			$tmp_field->delete();
		}
		
		ilMemberAgreement::_deleteByObjId($this->obj_id);
		
		ilUtil::sendSuccess($this->lng->txt('ps_cdf_deleted'));
		$this->listFields();
		return true;
	}
	
	/**
	 * Show field creation form
	 * @return 
	 */
	protected function addField()
	{
		$this->initFieldForm(self::MODE_CREATE);
		
		$this->form->getItemByPostVar('va')->setValues(array(''));
		
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Save field
	 * @return 
	 */
	protected function saveField()
	{
		$this->initFieldForm(self::MODE_CREATE);
		if($this->form->checkInput())
		{
			$udf = new ilCourseDefinedFieldDefinition($this->getObjId());
			$udf->setName($this->form->getInput('na'));
			$udf->setType($this->form->getInput('ty'));
			$udf->setValues($udf->prepareValues($this->form->getInput('va')));
			$udf->enableRequired($this->form->getInput('re'));
			$udf->save();
	
			ilUtil::sendSuccess($this->lng->txt('ps_cdf_added_field'));
			$this->listFields();
			return true;
		}
		// not valid
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
		return false;
	}
	
	/**
	 * Edit one field
	 * @param object $a_mode
	 * @return 
	 */
	protected function editField()
	{
		if(!$_REQUEST['field_id'])
		{
			$this->listFields();
			return false;
		}
		
		$this->initFieldForm(self::MODE_UPDATE);
		
		$udf = new ilCourseDefinedFieldDefinition($this->getObjId(),(int) $_REQUEST['field_id']);
		$this->form->getItemByPostVar('na')->setValue($udf->getName());
		$this->form->getItemByPostVar('ty')->setValue($udf->getType());
		$this->form->getItemByPostVar('re')->setChecked($udf->isRequired());
		$this->form->getItemByPostVar('va')->setValues($udf->getValues());
		
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Update field definition
	 * @return 
	 */
	protected function updateField()
	{
		$this->initFieldForm(self::MODE_UPDATE);
		
		if($this->form->checkInput())
		{
			$udf = new ilCourseDefinedFieldDefinition($this->getObjId(),(int) $_REQUEST['field_id']);
			$udf->setName($this->form->getInput('na'));
			$udf->setType($this->form->getInput('ty'));
			$udf->setValues($udf->prepareValues($this->form->getInput('va')));
			$udf->enableRequired($this->form->getInput('re'));
			$udf->update();

			// Finally reset member agreements
			ilMemberAgreement::_deleteByObjId($this->getObjId());
			ilUtil::sendSuccess($this->lng->txt('settings_saved'));
			$this->listFields();
			return true;
		}
		
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
		return false;
	}
	
	/**
	 * Init/create property form for fields
	 * @return 
	 */
	protected function initFieldForm($a_mode)
	{
		if($this->form instanceof ilPropertyFormGUI)
		{
			return true;
		}
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();

		switch($a_mode)
		{
			case self::MODE_CREATE:
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->setTitle($this->lng->txt('ps_cdf_add_field'));
				$this->form->addCommandButton('saveField', $this->lng->txt('save'));
				$this->form->addCommandButton('listFields', $this->lng->txt('cancel'));
				break;
				
			case self::MODE_UPDATE:
				$this->ctrl->setParameter($this,'field_id',(int) $_REQUEST['field_id']);
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->setTitle($this->lng->txt('ps_cdf_edit_field'));
				$this->form->addCommandButton('updateField', $this->lng->txt('save'));
				$this->form->addCommandButton('listFields', $this->lng->txt('cancel'));
				break;
		}
		
		// Name
		$na = new ilTextInputGUI($this->lng->txt('ps_cdf_name'),'na');
		$na->setSize(32);
		$na->setMaxLength(255);
		$na->setRequired(true);
		$this->form->addItem($na);
		
		// Type
		$ty = new ilRadioGroupInputGUI($this->lng->txt('ps_field_type'),'ty');
		$ty->setRequired(true);
		$this->form->addItem($ty);
		
		//		Text type	
		$ty_te = new ilRadioOption($this->lng->txt('ps_type_txt_long'),IL_CDF_TYPE_TEXT);
		$ty->addOption($ty_te);

		//		Select Type
		$ty_se = new ilRadioOption($this->lng->txt('ps_type_select_long'),IL_CDF_TYPE_SELECT);
		$ty->addOption($ty_se);
		
		//			Select Type Values
		$ty_se_mu = new ilTextWizardInputGUI($this->lng->txt('ps_cdf_value'),'va');
		$ty_se_mu->setRequired(true);
		$ty_se_mu->setSize(32);
		$ty_se_mu->setMaxLength(128);
		$ty_se->addSubItem($ty_se_mu);
		
		
		// Required
		$re = new ilCheckboxInputGUI($this->lng->txt('ps_cdf_required'),'re');
		$re->setValue(1);
		$this->form->addItem($re);
	}
}
?>