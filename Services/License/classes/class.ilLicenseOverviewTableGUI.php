<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * License overview table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesLicense
 */
class ilLicenseOverviewTableGUI extends ilTable2GUI
{	
	protected $item_list_guis; // [array]
	
	function __construct($a_parent_obj, $a_parent_cmd, $a_mode, ilObjectGUI $a_parent_gui)
	{	
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setId("licovw");
		$this->setTitle($this->lng->txt("licenses"));
		
		$this->addColumn($this->lng->txt("title"), "title");		
		$this->addColumn($this->lng->txt("comment"));
		$this->addColumn($this->lng->txt("existing_licenses"), "existing");
		$this->addColumn($this->lng->txt("used_licenses"), "used");
		$this->addColumn($this->lng->txt("remaining_licenses"), "remaining");
		$this->addColumn($this->lng->txt("potential_accesses"), "potential");
		
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("ASC");
		
		$this->setRowTemplate("tpl.lic_show_licenses.html", "Services/License");

		$this->getItems($a_mode, $a_parent_gui);			
	}

	protected function getItems($a_mode, ilObjectGUI $a_parent_gui)
	{		
		$data = array();
		
		if ($a_mode == ilLicenseOverviewGUI::LIC_MODE_ADMINISTRATION)
		{
   			$objects = ilLicense::_getLicensedObjects();
   		}
   		else
   		{
    		$objects = ilLicense::_getLicensedChildObjects($a_parent_gui->object->getRefId());
   		}				
		foreach($objects as $item)
		{					
			$license = new ilLicense($item["obj_id"]);
		
			$remaining_licenses = ($license->getLicenses() == "0") 
				? $this->lng->txt("arbitrary")  
				: $license->getRemainingLicenses();
			
			$data[] = array(
				"title" => $item["title"]
				,"comment" => nl2br(trim($license->getRemarks()))
				,"existing" => $license->getLicenses()
				,"used" => $license->getAccesses()
				,"remaining" => $remaining_licenses
				,"potential" => $license->getPotentialAccesses()
				,"listGUI" => $this->getItemHTML($item)
			);
		}
		
		$this->setData($data);
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("TITLE", $a_set["listGUI"]);
		$this->tpl->setVariable("REMARKS", $a_set["comment"]);
		$this->tpl->setVariable("LICENSES", $a_set["existing"]);
		$this->tpl->setVariable("USED_LICENSES", $a_set["used"]);
		$this->tpl->setVariable("REMAINING_LICENSES", $a_set["remaining"]);
		$this->tpl->setVariable("POTENTIAL_ACCESSES", $a_set["potential"]);
	}
		
	/**
	* get the html code for the list items
	*/
	protected function getItemHTML($item = array())
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
	protected function getItemListGUI($a_type)
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