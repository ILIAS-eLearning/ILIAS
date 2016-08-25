<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";


/**
* Class ilObjExternalToolsSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
*/
class ilObjExternalToolsSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function __construct($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "extt";
		parent::__construct($a_data,$a_id,$a_call_by_reference,false);
		
		$lng->loadLanguageModule("delic");
		$lng->loadLanguageModule("maps");
		$lng->loadLanguageModule("mathjax");
	}

	function getAdminTabs()
	{
		$this->getTabs();
	}	
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs()
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"),
				array("editMaps", "editMathJax", ""), "", "");
			$this->lng->loadLanguageModule('ecs');
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	 * Configure MathJax settings
	 */
	function editMathJaxObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl;
		
		$mathJaxSetting = new ilSetting("MathJax");
		$path_to_mathjax = $mathJaxSetting->get("path_to_mathjax");
		
		$this->__initSubTabs("editMathJax");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("mathjax_settings"));
		
		// Enable MathJax
		$enable = new ilCheckboxInputGUI($lng->txt("mathjax_enable_mathjax"), "enable");
		$enable->setChecked($mathJaxSetting->get("enable"));
		$enable->setInfo($lng->txt("mathjax_enable_mathjax_info")." <a target='blank' href='http://www.mathjax.org/'>MathJax</a>");
		$form->addItem($enable);
		
		// Path to mathjax
		$text_prop = new ilTextInputGUI($lng->txt("mathjax_path_to_mathjax"), "path_to_mathjax");
		$text_prop->setInfo($lng->txt("mathjax_path_to_mathjax_desc"));
		$text_prop->setValue($path_to_mathjax);
		$text_prop->setRequired(true);
		$text_prop->setMaxLength(400);
		$text_prop->setSize(100);
		$enable->addSubItem($text_prop);
		
		// mathjax limiter
		$options = array(
			0 => '\(...\)',
			1 => '[tex]...[/tex]',
			2 => '&lt;span class="math"&gt;...&lt;/span&gt;'
			);
		$si = new ilSelectInputGUI($this->lng->txt("mathjax_limiter"), "limiter");
		$si->setOptions($options);
		$si->setValue($mathJaxSetting->get("limiter"));
		$si->setInfo($this->lng->txt("mathjax_limiter_info"));
		$enable->addSubItem($si);

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$form->addCommandButton("saveMathJax", $lng->txt("save"));
		}
				
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	/**
	 * Save MathJax Setttings
	 */
	function saveMathJaxObject()
	{
		global $ilCtrl, $lng, $ilAccess;
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$path_to_mathjax = ilUtil::stripSlashes($_POST["path_to_mathjax"]);
			$mathJaxSetting = new ilSetting("MathJax");
			if ($_POST["enable"])
			{
				$mathJaxSetting->set("path_to_mathjax", $path_to_mathjax);
				$mathJaxSetting->set("limiter", (int) $_POST["limiter"]);
			}
			$mathJaxSetting->set("enable", ilUtil::stripSlashes($_POST["enable"]));
			ilUtil::sendInfo($lng->txt("msg_obj_modified"));
		}
		$ilCtrl->redirect($this, "editMathJax");
	}

	/**
	* Configure maps settings
	* 
	* @access	public
	*/
	function editMapsObject()
	{
		require_once("Services/Maps/classes/class.ilMapUtil.php");
		
		global $ilAccess, $lng, $ilCtrl, $tpl;
		
		$this->__initSubTabs("editMaps");
		$std_latitude = ilMapUtil::getStdLatitude();
		$std_longitude = ilMapUtil::getStdLongitude();
		$std_zoom = ilMapUtil::getStdZoom();
		$type = ilMapUtil::getType();
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once("./Services/Form/classes/class.ilCheckboxOption.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("maps_settings"));
		
		// Enable Maps
		$enable = new ilCheckboxInputGUI($lng->txt("maps_enable_maps"), "enable");
		$enable->setChecked(ilMapUtil::isActivated());
		$enable->setInfo($lng->txt("maps_enable_maps_info"));
		$form->addItem($enable);
		
		// Select type
		$types = new ilSelectInputGUI($lng->txt("maps_map_type"), "type");
		$types->setOptions(ilMapUtil::getAvailableMapTypes());
		$types->setValue($type);
		$form->addItem($types);

		// map data server property
		if($type == "openlayers") {
			$tile = new ilTextInputGUI($lng->txt("maps_tile_server"),"tile");
			$tile->setValue(ilMapUtil::getStdTileServers());
			$tile->setInfo(sprintf($lng->txt("maps_custom_tile_server_info"),ilMapUtil::DEFAULT_TILE));
			$geolocation = new ilTextInputGUI($lng->txt("maps_geolocation_server"),"geolocation");
			$geolocation->setValue(ilMapUtil::getStdGeolocationServer());
			$geolocation->setInfo($lng->txt("maps_custom_geolocation_server_info"));

			$form->addItem($tile);
			$form->addItem($geolocation);
		}

		// location property
		$loc_prop = new ilLocationInputGUI($lng->txt("maps_std_location"),
			"std_location");

		$loc_prop->setLatitude($std_latitude);
		$loc_prop->setLongitude($std_longitude);
		$loc_prop->setZoom($std_zoom);
		$form->addItem($loc_prop);

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$form->addCommandButton("saveMaps", $lng->txt("save"));
			$form->addCommandButton("view", $lng->txt("cancel"));
		}
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	* Save Maps Setttings
	*/
	function saveMapsObject()
	{
		require_once("Services/Maps/classes/class.ilMapUtil.php");
		
		global $ilCtrl;
		if(ilUtil::stripSlashes($_POST["type"]) == 'openlayers' && 'openlayers' == ilMapUtil::getType()) {
			ilMapUtil::setStdTileServers(ilUtil::stripSlashes($_POST["tile"]));
			ilMapUtil::setStdGeolocationServer(ilUtil::stripSlashes($_POST["geolocation"]));
		}

		ilMapUtil::setActivated(ilUtil::stripSlashes($_POST["enable"]) == "1");
		ilMapUtil::setType(ilUtil::stripSlashes($_POST["type"]));
		ilMapUtil::setStdLatitude(ilUtil::stripSlashes($_POST["std_location"]["latitude"]));
		ilMapUtil::setStdLongitude(ilUtil::stripSlashes($_POST["std_location"]["longitude"]));
		ilMapUtil::setStdZoom(ilUtil::stripSlashes($_POST["std_location"]["zoom"]));
		$ilCtrl->redirect($this, "editMaps");
	}
	
	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		$maps = ($a_cmd == 'editMaps') ? true : false;
		$mathjax = ($a_cmd == 'editMathJax') ? true : false;

		$this->tabs_gui->addSubTabTarget("maps_extt_maps", $this->ctrl->getLinkTarget($this, "editMaps"),
										 "", "", "", $maps);
		$this->tabs_gui->addSubTabTarget("mathjax_mathjax", $this->ctrl->getLinkTarget($this, "editMathJax"),
											"", "", "", $mathjax);
	}
	
	function executeCommand()
	{
		global $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
				
		if (!$ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		switch($next_class)
		{
			case 'ilecssettingsgui':
				$this->tabs_gui->setTabActive('ecs_server_settings');
				include_once('./Services/WebServices/ECS/classes/class.ilECSSettingsGUI.php');
				$this->ctrl->forwardCommand(new ilECSSettingsGUI());
				break;
			
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				$this->tabs_gui->setTabActive('perm_settings');
				break;

			default:
				$this->tabs_gui->setTabActive('settings');
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editMaps";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
} // END class.ilObjExternalToolsSettingsGUI
?>
