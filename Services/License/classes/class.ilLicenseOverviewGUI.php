<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/License/classes/class.ilLicense.php";

define("LIC_MODE_ADMINISTRATION",1);
define("LIC_MODE_REPOSITORY",2);

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
	/**
	* Constructor
	* @access public
	*/
	function ilLicenseOverviewGUI(&$a_parent_gui, $a_mode=LIC_MODE_REPOSITORY)
	{
		global $ilCtrl, $tpl, $lng;

		$this->module = "Services/License";
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
	function &executeCommand()
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
		include_once './Services/Table/classes/class.ilTableGUI.php';

		if ($this->mode == LIC_MODE_ADMINISTRATION)
		{
   			$objects = ilLicense::_getLicensedObjects();
   		}
   		else
   		{
    			$objects = ilLicense::_getLicensedChildObjects($this->parent_gui->object->getRefId());
   		}
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.lic_show_licenses.html',$this->module);

		foreach ($objects as $data)
		{
			$license =& new ilLicense($data["obj_id"]);
			$licenses = strval($license->getLicenses());
			$remarks = $license->getRemarks();
			$used_licenses = strval($license->getAccesses());
			$remaining_licenses = $licenses == "0" ? $this->lng->txt("arbitrary") : strval($license->getRemainingLicenses());
			$potential_accesses = strval($license->getPotentialAccesses());

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("TITLE", $this->getItemHTML($data));
			$this->tpl->setVariable("REMARKS", $remarks);
			$this->tpl->setVariable("LICENSES", $licenses);
			$this->tpl->setVariable("USED_LICENSES", $used_licenses);
			$this->tpl->setVariable("REMAINING_LICENSES", $remaining_licenses);
			$this->tpl->setVariable("POTENTIAL_ACCESSES", $potential_accesses);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_REMARKS", $this->lng->txt("comment"));
		$this->tpl->setVariable("TXT_EXISTING_LICENSES", $this->lng->txt("existing_licenses"));
		$this->tpl->setVariable("TXT_USED_LICENSES", $this->lng->txt("used_licenses"));
		$this->tpl->setVariable("TXT_REMAINING_LICENSES", $this->lng->txt("remaining_licenses"));
		$this->tpl->setVariable("TXT_POTENTIAL_ACCESSES", $this->lng->txt("potential_accesses"));
		$this->tpl->setVariable("TXT_USED_LICENSES_EXPLANATION", $this->lng->txt("used_licenses_explanation"));
		$this->tpl->setVariable("TXT_REMAINING_LICENSES_EXPLANATION", $this->lng->txt("remaining_licenses_explanation"));
		$this->tpl->setVariable("TXT_POTENTIAL_ACCESSES_EXPLANATION", $this->lng->txt("potential_accesses_explanation"));
	}


	/**
	* get the html code for the list items
	*/
	function getItemHTML($item = array())
	{
		$item_list_gui =& $this->getItemListGUI($item["type"]);
		$item_list_gui->enableCommands(true);
		$item_list_gui->enableDelete(false);
		$item_list_gui->enableCut(false);
		$item_list_gui->enableCopy(false);
		$item_list_gui->enablePayment(false);
		$item_list_gui->enableLink(false);
        $item_list_gui->enableProperties(false);
		$item_list_gui->enableDescription(false);
		$item_list_gui->enablePreconditions(false);
		$item_list_gui->enableSubscribe(false);
		$item_list_gui->enableInfoScreen(false);

		return $item_list_gui->getListItemHTML($item["ref_id"],
			$item["obj_id"], $item["title"], $item["description"]);
	}

	/**
	* get item list gui class for type
	*/
	function &getItemListGUI($a_type)
	{
		global $objDefinition;
		if (!is_object($this->item_list_guis[$a_type]))
		{
			$class = $objDefinition->getClassName($a_type);
			$location = $objDefinition->getLocation($a_type);
			$full_class = "ilObj".$class."ListGUI";
			include_once($location."/class.".$full_class.".php");
			$item_list_gui = new $full_class();
			$this->item_list_guis[$a_type] =& $item_list_gui;
		}
		else
		{
			$item_list_gui =& $this->item_list_guis[$a_type];
		}
		return $item_list_gui;
	}
}
?>
