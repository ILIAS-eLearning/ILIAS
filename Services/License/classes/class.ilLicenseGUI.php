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

		$this->module = "Services/License";
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

	/**
	* Show the license form
	* @access public
	*/
	function editLicense()
	{
		$licenses = strval($this->license->getLicenses());
		$used_licenses = strval($this->license->getAccesses());
		$remaining_licenses = $licenses == "0" ? $this->lng->txt("arbitrary") : strval($this->license->getRemainingLicenses());
		$potential_accesses = strval($this->license->getPotentialAccesses());
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.lic_edit_license.html',$this->module);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_EDIT_LICENSE", $this->lng->txt("edit_license"));
		$this->tpl->setVariable("TXT_EXISTING_LICENSES", $this->lng->txt("existing_licenses"));
		$this->tpl->setVariable("LICENSES", $licenses);
		$this->tpl->setVariable("TXT_ZERO_LICENSES_EXPLANATION", $this->lng->txt("zero_licenses_explanation"));
		$this->tpl->setVariable("TXT_USED_LICENSES", $this->lng->txt("used_licenses"));
		$this->tpl->setVariable("USED_LICENSES", $used_licenses);
		$this->tpl->setVariable("TXT_USED_LICENSES_EXPLANATION", $this->lng->txt("used_licenses_explanation"));
		$this->tpl->setVariable("TXT_REMAINING_LICENSES", $this->lng->txt("remaining_licenses"));
		$this->tpl->setVariable("REMAINING_LICENSES", $remaining_licenses);
		$this->tpl->setVariable("TXT_REMAINING_LICENSES_EXPLANATION", $this->lng->txt("remaining_licenses_explanation"));
		$this->tpl->setVariable("TXT_POTENTIAL_ACCESSES", $this->lng->txt("potential_accesses"));
		$this->tpl->setVariable("POTENTIAL_ACCESSES", $potential_accesses);
		$this->tpl->setVariable("TXT_POTENTIAL_ACCESSES_EXPLANATION", $this->lng->txt("potential_accesses_explanation"));
		$this->tpl->setVariable("TXT_REMARKS", $this->lng->txt("comment"));
		$this->tpl->setVariable("REMARKS", $this->license->getRemarks());
		$this->tpl->setVariable("BTN_UPDATE", $this->lng->txt("save"));
	}
	
	/**
	* Save the license form
	* @access public
	*/
	function updateLicense()
	{
		$this->license->setLicenses((int) $_REQUEST["licenses"]);
		$this->license->setRemarks($_REQUEST["remarks"]);
		$this->license->update();
		ilUtil::sendInfo($this->lng->txt('license_updated'), true);
		$this->ctrl->redirect($this,"editLicense");
	}
} 
?>
