<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/License/classes/class.ilLicense.php";

/**
* Class ilLicenseOverviewGUI
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilLicenseGUI.php $
*
* @ilCtrl_Calls ilLicenseOverviewGUI:
*
* @package ilias-license
*/

class ilLicenseOverviewGUI
{
	const LIC_MODE_ADMINISTRATION = 1;
	const LIC_MODE_REPOSITORY = 2;
	
	/**
	* Constructor
	* @access public
	*/
	function ilLicenseOverviewGUI(&$a_parent_gui, $a_mode = self::LIC_MODE_REPOSITORY)
	{
		global $ilCtrl, $tpl, $lng;

		$this->mode = $a_mode;
		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("license");
		$this->parent_gui =& $a_parent_gui;
	}

	/**
	* Execute a command (main entry point)
	* @access public
	*/
	function executeCommand()
	{
		global $rbacsystem, $ilErr;

		// access to all functions in this class are only allowed if read is granted
		if (!$rbacsystem->checkAccess("read",$this->parent_gui->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->MESSAGE);
		}

		$cmd = $this->ctrl->getCmd("showLicenses");
		$this->$cmd();

		return true;
	}

	/**
	* Show the license list
	* @access public
	*/
	function showLicenses()
	{
		include_once './Services/License/classes/class.ilLicenseOverviewTableGUI.php';
		$tbl = new ilLicenseOverviewTableGUI($this, "showLicenses", $this->mode, $this->parent_gui);		
		
		include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
		$panel = ilPanelGUI::getInstance();
		$panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
		$panel->setBody('<div class="small">'.
			$this->lng->txt("used_licenses_explanation")."<br/>".
			$this->lng->txt("remaining_licenses_explanation")."<br/>".
			$this->lng->txt("potential_accesses_explanation").
			"</div>"
		);
		
		$this->tpl->setContent($tbl->getHTML()."<br />".$panel->getHTML());	
	}
}
