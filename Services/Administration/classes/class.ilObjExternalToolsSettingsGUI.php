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
	function ilObjExternalToolsSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "extt";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		$lng->loadLanguageModule("delic");
		$lng->loadLanguageModule("maps");
		$lng->loadLanguageModule("mathjax");
	}

	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}	
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
//				$this->ctrl->getLinkTarget($this, "view"), 
//				array("view","editDelicious", "editGoogleMaps","editMathJax", ""), "", "");
			
				$this->ctrl->getLinkTarget($this, "editSocialBookmarks"),
				array("editDelicious", "editMaps","editMathJax", ""), "", "");
			$this->lng->loadLanguageModule('ecs');
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	function addSocialBookmarkObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editSocialBookmarks");

		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		$form = ilSocialBookmarks::_initForm($this, 'create');
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
	}

	function createSocialBookmarkObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		$form = ilSocialBookmarks::_initForm($this, 'create');
		if ($form->checkInput())
		{
			$title = $form->getInput('title');
			$link = $form->getInput('link');
			$file = $form->getInput('image_file');
			$active = $form->getInput('activate');

			$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
			$icon_path = ilUtil::getWebspaceDir() . DIRECTORY_SEPARATOR . 'social_bm_icons' . DIRECTORY_SEPARATOR . time() . '.' . $extension;

			$path = ilUtil::getWebspaceDir() . DIRECTORY_SEPARATOR . 'social_bm_icons';
			if (!is_dir($path))	
				ilUtil::createDirectory($path);

			ilSocialBookmarks::_insertSocialBookmark($title, $link, $active, $icon_path);

			ilUtil::moveUploadedFile($file['tmp_name'], $file['name'], $icon_path);

			$this->editSocialBookmarksObject();
		}
		else
		{
			$this->__initSubTabs("editSocialBookmarks");
			$form->setValuesByPost();
			$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
		}
	}

	function updateSocialBookmarkObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		$form = ilSocialBookmarks::_initForm($this, 'update');
		if ($form->checkInput())
		{
			$title = $form->getInput('title');
			$link = $form->getInput('link');
			$file = $form->getInput('image_file');
			$active = $form->getInput('activate');
			$id = $form->getInput('sbm_id');

			if (!$file['name'])
				ilSocialBookmarks::_updateSocialBookmark($id, $title, $link, $active);
			else
			{
				$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
				$icon_path = ilUtil::getWebspaceDir() . DIRECTORY_SEPARATOR . 'social_bm_icons' . DIRECTORY_SEPARATOR . time() . '.' . $extension;

				$path = ilUtil::getWebspaceDir() . DIRECTORY_SEPARATOR . 'social_bm_icons';
				if (!is_dir($path))	
					ilUtil::createDirectory($path);

				ilSocialBookmarks::_deleteImage($id);
				ilSocialBookmarks::_updateSocialBookmark($id, $title, $link, $active, $icon_path);	
				ilUtil::moveUploadedFile($file['tmp_name'], $file['name'], $icon_path);
			}

			$this->editSocialBookmarksObject();
		}
		else
		{
			$this->__initSubTabs("editSocialBookmarks");
			$form->setValuesByPost();
			$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
		}
	}

	/**
	* edit a social bookmark
	* 
	* @access	public
	*/
	function editSocialBookmarkObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;

		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editSocialBookmarks");

		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';		
		$row = ilSocialBookmarks::_getEntry($_GET['sbm_id']);
		$dset = array
		(
			'sbm_id' => $row->sbm_id,
			'title' => $row->sbm_title,
			'link' => $row->sbm_link,
			'activate' => $row->sbm_active
		);

		$form = ilSocialBookmarks::_initForm($this, 'update');
		$form->setValuesByArray($dset);
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
	}

	function enableSocialBookmarksObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;

		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$ids = ((int)$_GET['sbm_id']) ? array((int)$_GET['sbm_id']) : $_POST['sbm_id'];
		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		ilSocialBookmarks::_setActive($ids, true);
		$this->editSocialBookmarksObject();
	}

	function disableSocialBookmarksObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;

		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);

		}
		$ids = ((int)$_GET['sbm_id']) ? array((int)$_GET['sbm_id']) : $_POST['sbm_id'];
		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		ilSocialBookmarks::_setActive($ids, false);
		$this->editSocialBookmarksObject();
	}

	function deleteSocialBookmarksObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);

		}

		$this->__initSubTabs("editSocialBookmarks");

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		$ids = ((int)$_GET['sbm_id']) ? array((int)$_GET['sbm_id']) : $_POST['sbm_id'];

		// set confirm/cancel commands
		$c_gui->setFormAction($ilCtrl->getFormAction($this, "confirmDeleteSocialBookmarks"));
		$c_gui->setHeaderText($lng->txt("socialbm_sure_delete_entry"));
		$c_gui->setCancel($lng->txt("cancel"), "editSocialBookmarks");
		$c_gui->setConfirm($lng->txt("confirm"), "confirmDeleteSocialBookmarks");
		
		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		// add items to delete
		foreach($ids as $id)
		{
			$entry = ilSocialBookmarks::_getEntry($id);
			$c_gui->addItem("sbm_id[]", $id, $entry->sbm_title . ' (' . str_replace('{', '&#123;', $entry->sbm_link) . ')');
		}
		
		$this->tpl->setVariable('ADM_CONTENT', $c_gui->getHTML());
	}

	function confirmDeleteSocialBookmarksObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}


		$ids = ((int)$_GET['sbm_id']) ? array((int)$_GET['sbm_id']) : $_POST['sbm_id'];
		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		ilSocialBookmarks::_delete($ids, false);
		$this->editSocialBookmarksObject();
	}

	/**
	* Configure social bookmark settings
	* 
	* @access	public
	*/
	function editSocialBookmarksObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editSocialBookmarks");
		


		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

		include_once './Services/Administration/classes/class.ilSocialBookmarks.php';
		$rset = ilSocialBookmarks::_getEntry();

		$counter = 0;
		foreach($rset as $row)
		{
			$current_selection_list = new ilAdvancedSelectionListGUI();
			$current_selection_list->setListTitle($lng->txt("actions"));
			$current_selection_list->setId("act_".$counter++);

			$ilCtrl->setParameter($this, 'sbm_id', $row->sbm_id);

			$current_selection_list->addItem($lng->txt("edit"), '', $ilCtrl->getLinkTarget($this, "editSocialBookmark"));
			$current_selection_list->addItem($lng->txt("delete"), '', $ilCtrl->getLinkTarget($this, "deleteSocialBookmarks"));
			
			$toggle_action = '';
			if ($row->sbm_active)
			{
				$current_selection_list->addItem($lng->txt("socialbm_disable"), '', $toggle_action = $ilCtrl->getLinkTarget($this, "disableSocialBookmarks"));
			}
			else
			{
				$current_selection_list->addItem($lng->txt("socialbm_enable"), '', $toggle_action = $ilCtrl->getLinkTarget($this, "enableSocialBookmarks"));
			}



			$dset[] = array
			(
				'CHECK' => ilUtil::formCheckbox(0, 'sbm_id[]', $row->sbm_id),
				'ID' => $row->sbm_id,
				'TITLE' => $row->sbm_title,
				'LINK' => str_replace('{', '&#123;', $row->sbm_link),
				'ICON' => $row->sbm_icon,
				'ACTIVE' => $row->sbm_active ? $lng->txt('enabled') : $lng->txt('disabled'),
				'ACTIONS' => $current_selection_list->getHTML(),
				'TOGGLE_LINK' => $toggle_action
			);
			$ilCtrl->clearParameters($this);
		}

		require_once 'Services/Table/classes/class.ilTable2GUI.php';
		$table = new ilTable2GUI($this, 'editSocialBookmarks');
		$table->setFormName('smtable');
		$table->setId('smtable');
		$table->setPrefix('sm');
		$table->setFormAction($ilCtrl->getFormAction($this, 'saveSocialBookmarks'));
		$table->addColumn('', 'check', '', true);
		$table->addColumn($lng->txt('icon'), '');
		$table->addColumn($lng->txt('title'), 'TITLE');
		$table->addColumn($lng->txt('link'), 'LINK');
		$table->addColumn($lng->txt('active'), 'ACTIVE');
		$table->addColumn($lng->txt('actions'), '');
		$table->setTitle($lng->txt('bm_manage_social_bm'));
		$table->setData($dset);
		$table->setRowTemplate('tpl.social_bookmarking_row.html', 'Services/Administration');
		$table->setSelectAllCheckbox('sbm_id');

		$table->setDefaultOrderField("title");
		$table->setDefaultOrderDirection("asc");

		$table->addMultiCommand('enableSocialBookmarks', $lng->txt('socialbm_enable'));
		$table->addMultiCommand('disableSocialBookmarks', $lng->txt('socialbm_disable'));
		$table->addMultiCommand('deleteSocialBookmarks', $lng->txt('delete'));

		$table->addCommandButton('addSocialBookmark', $lng->txt('create'));
		
		$this->tpl->setVariable('ADM_CONTENT', $table->getHTML());
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
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editMaps");
		
		$std_latitude = ilMapUtil::getStdLatitude();
		$std_longitude = ilMapUtil::getStdLongitude();
		$std_zoom = ilMapUtil::getStdZoom();
		$type = ilMapUtil::getType();
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
		
		// location property
		$loc_prop = new ilLocationInputGUI($lng->txt("maps_std_location"),
			"std_location");
		$loc_prop->setLatitude($std_latitude);
		$loc_prop->setLongitude($std_longitude);
		$loc_prop->setZoom($std_zoom);
		$form->addItem($loc_prop);
		
		$form->addCommandButton("saveMaps", $lng->txt("save"));
		$form->addCommandButton("view", $lng->txt("cancel"));
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	* Save Maps Setttings
	*/
	function saveMapsObject()
	{
		require_once("Services/Maps/classes/class.ilMapUtil.php");
		
		global $ilCtrl;
		
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
//		$overview = ($a_cmd == 'view' or $a_cmd == '') ? true : false;
		//$delicious = ($a_cmd == 'editDelicious') ? true : false;
		
		if($a_cmd == 'view' || $a_cmd == '') 
		{
			$a_cmd = 'editSocialBookmarks';
		}
		$socialbookmarks = ($a_cmd == 'editSocialBookmarks') ? true : false;
		$maps = ($a_cmd == 'editMaps') ? true : false;
		$mathjax = ($a_cmd == 'editMathJax') ? true : false;

//		$this->tabs_gui->addSubTabTarget("overview", $this->ctrl->getLinkTarget($this, "view"),
//										 "", "", "", $overview);
		/*$this->tabs_gui->addSubTabTarget("delic_extt_delicious", $this->ctrl->getLinkTarget($this, "editDelicious"),
											"", "", "", $delicious);*/

		$this->tabs_gui->addSubTabTarget("maps_extt_maps", $this->ctrl->getLinkTarget($this, "editMaps"),
										 "", "", "", $maps);
		$this->tabs_gui->addSubTabTarget("mathjax_mathjax", $this->ctrl->getLinkTarget($this, "editMathJax"),
											"", "", "", $mathjax);
		$this->tabs_gui->addSubTabTarget("socialbm_extt_social_bookmarks", $this->ctrl->getLinkTarget($this, "editSocialBookmarks"),
											"", "", "", $socialbookmarks);
	}
	
	function &executeCommand()
	{
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilecssettingsgui':
				$this->tabs_gui->setTabActive('ecs_server_settings');
				include_once('./Services/WebServices/ECS/classes/class.ilECSSettingsGUI.php');
				$this->ctrl->forwardCommand(new ilECSSettingsGUI());
				break;
			
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				$this->tabs_gui->setTabActive('perm_settings');
				break;

			default:
				$this->tabs_gui->setTabActive('settings');
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSocialBookmarks";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
} // END class.ilObjExternalToolsSettingsGUI
?>
