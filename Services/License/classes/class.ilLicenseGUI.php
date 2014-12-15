<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/License/classes/class.ilLicense.php";

/**
* Class ilLicenseGUI
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilLicenseGUI.php $
*
* @ilCtrl_Calls ilLicenseGUI: 
*
* @package ilias-license
*/

class ilLicenseGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilLicenseGUI(&$a_parent_gui)
	{
		global $ilCtrl, $tpl, $lng;

		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("license");
		$this->parent_gui =& $a_parent_gui;
		$this->license =& new ilLicense($this->parent_gui->object->getId());
	}

	/**
	* Execute a command (main entry point)
	* @access public
	*/
	function &executeCommand()
	{
		global $rbacsystem, $ilErr;

		// access to all functions in this class are only allowed if edit_permission is granted
		if (!$rbacsystem->checkAccess("edit_permission",$this->parent_gui->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->MESSAGE);
		}

		$cmd = $this->ctrl->getCmd("editLicense");
		$this->$cmd();

		return true;
	}
	
	protected function initLicenseForm()
	{					
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "updateLicense"));
		$form->setTitle($this->lng->txt('edit_license'));
		
		$exist = new ilNumberInputGUI($this->lng->txt("existing_licenses"), "licenses");
		$exist->setInfo($this->lng->txt("zero_licenses_explanation"));
		$exist->setMaxLength(10);
		$exist->setSize(10);
		$exist->setValue($this->license->getLicenses());		
		$form->addItem($exist);
		
		$info_used = new ilNonEditableValueGUI($this->lng->txt("used_licenses"));
		$info_used->setInfo($this->lng->txt("used_licenses_explanation"));
		$info_used->setValue($this->license->getAccesses());
		$form->addItem($info_used);
		
		$remaining_licenses = ($this->license->getLicenses() == "0")
			? $this->lng->txt("arbitrary") 
			: $this->license->getRemainingLicenses();		
		
		$info_remain = new ilNonEditableValueGUI($this->lng->txt("remaining_licenses"));
		$info_remain->setInfo($this->lng->txt("remaining_licenses_explanation"));
		$info_remain->setValue($remaining_licenses);
		$form->addItem($info_remain);
		
		$info_potential = new ilNonEditableValueGUI($this->lng->txt("potential_accesses"));
		$info_potential->setInfo($this->lng->txt("potential_accesses_explanation"));
		$info_potential->setValue($this->license->getPotentialAccesses());
		$form->addItem($info_potential);
		
		$comm = new ilTextAreaInputGUI($this->lng->txt("comment"), "remarks");
		$comm->setRows(5);	
		$comm->setValue($this->license->getRemarks());
		$form->addItem($comm);
		
		$form->addCommandButton('updateLicense', $this->lng->txt('save'));
		
		return $form;		
	}

	/**
	* Show the license form
	* @access public
	*/
	function editLicense(ilPropertyFormGUI $a_form = null)
	{
		if(!$a_form)
		{
			$a_form = $this->initLicenseForm();
		}
		
		$this->tpl->setContent($a_form->getHTML());				
	}
	
	/**
	* Save the license form
	* @access public
	*/
	function updateLicense()
	{
		$form = $this->initLicenseForm();
		if($form->checkInput())
		{
			$this->license->setLicenses($form->getInput("licenses"));
			$this->license->setRemarks($form->getInput("remarks"));
			$this->license->update();
			
			ilUtil::sendSuccess($this->lng->txt('license_updated'), true);
			$this->ctrl->redirect($this,"editLicense");
		}
		
		$form->setValuesByPost();
		$this->editLicense($form);		
	}
} 
