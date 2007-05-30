<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjMediaCastGUI
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjMediaCastGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_IsCalledBy ilObjMediaCastGUI: ilRepositoryGUI, ilAdministrationGUI
*/
class ilObjMediaCastGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjMediaCastGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl, $lng;
		
		$this->type = "mcst";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$lng->loadLanguageModule("mcst");
		$lng->loadLanguageModule("news");
		
		$ilCtrl->saveParameter($this, "item_id");
	}
	
	function &executeCommand()
	{
  		global $ilUser;
  
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
  
  		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
			break;
		
			default:
				if(!$cmd)
				{
					$cmd = "infoScreen";
				}
				$cmd .= "Object";
				$this->$cmd();
	
			break;
		}
  
  		return true;
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff
			
		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect("ilias.php?baseClass=ilMediaCastHandlerGUI&ref_id=".$newObj->getRefId()."&cmd=listItems");
	}
	
	
	/**
	* List items of media cast.
	*/
	function listItemsObject()
	{
		global $tpl, $lng, $ilAccess;
		
		$med_items = $this->object->getItemsArray();
		
		include_once("./Modules/MediaCast/classes/class.ilMediaCastTableGUI.php");
		$table_gui = new ilMediaCastTableGUI($this, "listItems");
				
		$table_gui->setTitle($lng->txt("mcst_media_cast"));
		$table_gui->setData($med_items);
		
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$table_gui->addCommandButton("addCastItem", $lng->txt("add"));
			$table_gui->addMultiCommand("confirmDeletionItems", $lng->txt("delete"));
			$table_gui->setSelectAllCheckbox("item_id");
		}
		
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		$public_feed = ilBlockSetting::_lookup("news", "public_feed",
			0, $this->object->getId());
			
		// rss icon/link
		if ($public_feed)
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");

			if ($enable_internal_rss)
			{
				$table_gui->addHeaderCommand(
						ILIAS_HTTP_PATH."/feed.php?client_id=".rawurlencode(CLIENT_ID)."&".
							"ref_id=".$_GET["ref_id"],
							$lng->txt("news_feed_url"), "_blank",
							ilUtil::getImagePath("rss.gif"));
			}
		}

		$tpl->setContent($table_gui->getHTML());

	}
	
	/**
	* Add media cast item
	*/
	function addCastItemObject()
	{
		global $tpl;
		
		$this->initAddCastItemForm();
		$tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Edit media cast item
	*/
	function editCastItemObject()
	{
		global $tpl;
		
		$this->initAddCastItemForm("edit");
		$this->getCastItemValues();
		$tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Init add cast item form.
	*/
	function initAddCastItemForm($a_mode = "create")
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("mcst");
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");

		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		
		// Property Title
		$text_input = new ilTextInputGUI($lng->txt("title"), "title");
		$text_input->setRequired(true);
		$text_input->setMaxLength(200);
		$this->form_gui->addItem($text_input);
		
		// Property Content
		$text_area = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$text_area->setRequired(false);
		$this->form_gui->addItem($text_area);
		
		// Property Visibility
		if ($enable_internal_rss)
		{
			$radio_group = new ilRadioGroupInputGUI($lng->txt("access_scope"), "visibility");
			$radio_option = new ilRadioOption($lng->txt("access_users"), "users");
			$radio_group->addOption($radio_option);
			$radio_option = new ilRadioOption($lng->txt("access_public"), "public");
			$radio_group->addOption($radio_option);
			$radio_group->setInfo($lng->txt("mcst_visibility_info"));
			$radio_group->setRequired(true);
			$radio_group->setValue("users");
			$this->form_gui->addItem($radio_group);
		}
		
		// File
		$file = new ilFileInputGUI($lng->txt("file"), "file");
		if ($a_mode == "create")
		{
			$file->setRequired(true);
		}
		$file->setSuffixes(array("mp3"));
		$this->form_gui->addItem($file);
		
		// Duration
		$dur = new ilDurationInputGUI($lng->txt("mcst_duration"), "duration");
		$dur->setInfo($lng->txt("mcst_duration_info"));
		$dur->setShowDays(false);
		$dur->setShowHours(true);
		$dur->setShowSeconds(true);
		$this->form_gui->addItem($dur);
		
		// save/cancel button
		if ($a_mode == "create")
		{
			$this->form_gui->addCommandButton("saveCastItem", $lng->txt("save"));
		}
		else
		{
			$this->form_gui->addCommandButton("updateCastItem", $lng->txt("save"));
		}
		$this->form_gui->addCommandButton("listItems", $lng->txt("cancel"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveCastItem"));
		
		$this->form_gui->setTitle($lng->txt("mcst_add_new_item"));
	}
	
	/**
	* Get cast item values into form.
	*/
	public function getCastItemValues()
	{
		$values = array();
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$this->mcst_item = new ilNewsItem($_GET["item_id"]);
		
		$values["title"] = $this->mcst_item->getTitle();
		$values["description"] = $this->mcst_item->getContent();
		$values["visibility"] = $this->mcst_item->getVisibility();
		$length = explode(":", $this->mcst_item->getPlaytime());
		$values["duration"] = array("hh" => $length[0], "mm" => $length[1], "ss" => $length[2]);
		
		$this->form_gui->setValuesByArray($values);
	}
	
	/**
	* Save new cast item
	*/
	function saveCastItemObject()
	{
		global $tpl, $ilCtrl, $ilUser;
		
		$this->initAddCastItemForm();
		
		if ($this->form_gui->checkInput())
		{
			
			// create dummy object in db (we need an id)
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
			$mob = new ilObjMediaObject();

			$mob->setTitle($this->form_gui->getInput("title"));
			$mob->setDescription("");
			$mob->create();

			// determine and create mob directory, move uploaded file to directory
			//$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
			$mob->createDirectory();
			$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());

			$media_item =& new ilMediaItem();
			$mob->addMediaItem($media_item);
			$media_item->setPurpose("Standard");

			$file = $mob_dir."/".$_FILES['file']['name'];
			ilUtil::moveUploadedFile($_FILES['file']['tmp_name'],
				$_FILES['file']['name'], $file);

			// determine duration
			$duration = $this->form_gui->getInput("duration");
			if ($duration["hh"] == 0 && $duration["mm"] == 0 && $duration["ss"] == 0)
			{
				include_once("./Services/MediaObjects/classes/class.ilMediaAnalyzer.php");
				$ana = new ilMediaAnalyzer();
				$ana->setFile($file);
				$ana->analyzeFile();
				$dur = $ana->getPlaytimeString();
				$dur = explode(":", $dur);
				$duration["mm"] = $dur[0];
				$duration["ss"] = $dur[1];
			}
			$duration = 
				str_pad($duration["hh"], 2 , "0", STR_PAD_LEFT).":".
				str_pad($duration["mm"], 2 , "0", STR_PAD_LEFT).":".
				str_pad($duration["ss"], 2 , "0", STR_PAD_LEFT);
			
			
			// get mime type
			$format = ilObjMediaObject::getMimeType($file);
			$location = $_FILES['file']['name'];

			// set real meta and object data
			$media_item->setFormat($format);
			$media_item->setLocation($location);
			$media_item->setLocationType("LocalFile");
			$mob->setTitle($_FILES['file']['name']);
			$mob->setDescription($format);
			$media_item->setHAlign("Left");

			ilUtil::renameExecutables($mob_dir);
			$mob->update();
			
			//
			// @todo: save usage
			//
			
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			// create new media cast item
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$mc_item = new ilNewsItem();
			$mc_item->setMobId($mob->getId());
			$mc_item->setContentType(NEWS_AUDIO);
			$mc_item->setContextObjId($this->object->getId());
			$mc_item->setContextObjType($this->object->getType());
			$mc_item->setUserId($ilUser->getId());
			$mc_item->setPlaytime($duration);
			$mc_item->setTitle($this->form_gui->getInput("title"));
			$mc_item->setContent($this->form_gui->getInput("description"));
			if ($enable_internal_rss)
			{
				$mc_item->setVisibility($this->form_gui->getInput("visibility"));
			}
			else
			{
				$mc_item->setVisibility("users");
			}
			$mc_item->create();
			
			$ilCtrl->redirect($this, "listItems");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHTML());
		}
	}
	
	/**
	* Update cast item
	*/
	function updateCastItemObject()
	{
		global $tpl, $ilCtrl, $ilUser;
		
		$this->initAddCastItemForm("edit");
		
		if ($this->form_gui->checkInput())
		{
			// create new media cast item
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$mc_item = new ilNewsItem($_GET["item_id"]);
			$mob_id = $mc_item->getMobId();
			
			// create dummy object in db (we need an id)
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
			$mob = new ilObjMediaObject($mob_id);

			$mob->setTitle($this->form_gui->getInput("title"));
			$mob->setDescription("");

			// determine and create mob directory, move uploaded file to directory
			//$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$this->object->getId();
			$mob->createDirectory();
			$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());

			$media_item = $mob->getMediaItem("Standard");

			if ($_FILES['file']['name'] != "")
			{
				$file = $mob_dir."/".$_FILES['file']['name'];
				ilUtil::moveUploadedFile($_FILES['file']['tmp_name'],
					$_FILES['file']['name'], $file);
				// get mime type
				$format = ilObjMediaObject::getMimeType($file);
				$location = $_FILES['file']['name'];
				$media_item->setFormat($format);
				$media_item->setLocation($location);
				$media_item->setLocationType("LocalFile");
				$mob->setTitle($_FILES['file']['name']);
				$mob->setDescription($format);
				$media_item->setHAlign("Left");
	
				ilUtil::renameExecutables($mob_dir);
			}
			$file = $mob_dir."/".$media_item->getLocation();

			// set real meta and object data
			$mob->update();

			// determine duration
			$duration = $this->form_gui->getInput("duration");
			if ($duration["hh"] == 0 && $duration["mm"] == 0 && $duration["ss"] == 0)
			{
				include_once("./Services/MediaObjects/classes/class.ilMediaAnalyzer.php");
				$ana = new ilMediaAnalyzer();
				$ana->setFile($file);
				$ana->analyzeFile();
				$dur = $ana->getPlaytimeString();
				$dur = explode(":", $dur);
				$duration["mm"] = $dur[0];
				$duration["ss"] = $dur[1];
			}
			$duration = 
				str_pad($duration["hh"], 2 , "0", STR_PAD_LEFT).":".
				str_pad($duration["mm"], 2 , "0", STR_PAD_LEFT).":".
				str_pad($duration["ss"], 2 , "0", STR_PAD_LEFT);
			
			
			//
			// @todo: save usage
			//
			
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");

			$mc_item->setUserId($ilUser->getId());
			$mc_item->setPlaytime($duration);
			$mc_item->setTitle($this->form_gui->getInput("title"));
			$mc_item->setContent($this->form_gui->getInput("description"));
			if ($enable_internal_rss)
			{
				$mc_item->setVisibility($this->form_gui->getInput("visibility"));
			}
			$mc_item->update();

			$ilCtrl->redirect($this, "listItems");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHTML());
		}
	}

	/**
	* Confirmation Screen.
	*/
	function confirmDeletionItemsObject()
	{
		global $ilCtrl, $lng, $tpl;
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($ilCtrl->getFormAction($this, "deleteItems"));
		$c_gui->setHeaderText($lng->txt("info_delete_sure"));
		$c_gui->setCancel($lng->txt("cancel"), "listItems");
		$c_gui->setConfirm($lng->txt("confirm"), "deleteItems");

		// add items to delete
		include_once("./Services/News/classes/class.ilNewsItem.php");
		foreach($_POST["item_id"] as $item_id)
		{
			$item = new ilNewsItem($item_id);
			$c_gui->addItem("item_id[]", $item_id, $item->getTitle(),
				ilUtil::getImagePath("icon_mcst.gif"));
		}
		
		$tpl->setContent($c_gui->getHTML());
	}

	/**
	* Delete news items.
	*/
	function deleteItemsObject()
	{
		global $ilCtrl;
		
		// delete all selected news items
		foreach($_POST["item_id"] as $item_id)
		{
			$mc_item = new ilNewsItem($item_id);
			$mob = $mc_item->getMobId();
			$mc_item->delete();
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$mob = new ilObjMediaObject($mob);
			$mob->delete();
		}
		
		$ilCtrl->redirect($this, "listItems");
	}
	
	/**
	* Delete news items.
	*/
	function downloadItemObject()
	{
		$mc_item = new ilNewsItem($_GET["item_id"]);
		$mob = $mc_item->getMobId();
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob = new ilObjMediaObject($mob);
		$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
		$m_item = $mob->getMediaItem("Standard");
		$file = $mob_dir."/".$m_item->getLocation();
		ilUtil::deliverFile($file, $m_item->getLocation());
		exit;
	}
	
	/**
	* Delete news items.
	*/
	function determinePlaytimeObject()
	{
		global $ilCtrl;
		
		$mc_item = new ilNewsItem($_GET["item_id"]);
		$mob = $mc_item->getMobId();
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mob = new ilObjMediaObject($mob);
		$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());
		$m_item = $mob->getMediaItem("Standard");
		$file = $mob_dir."/".$m_item->getLocation();
		
		include_once("./Services/MediaObjects/classes/class.ilMediaAnalyzer.php");
		$ana = new ilMediaAnalyzer();
		$ana->setFile($file);
		$ana->analyzeFile();
		$dur = $ana->getPlaytimeString();
		$dur = explode(":", $dur);
		$duration["hh"] = "00";
		$duration["mm"] = $dur[0];
		$duration["ss"] = $dur[1];

		$duration = 
			str_pad($duration["hh"], 2 , "0", STR_PAD_LEFT).":".
			str_pad($duration["mm"], 2 , "0", STR_PAD_LEFT).":".
			str_pad($duration["ss"], 2 , "0", STR_PAD_LEFT);

		$mc_item->setPlaytime($duration);
		$mc_item->update();

		$ilCtrl->redirect($this, "listItems");
	}

	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser;

		if (!$ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		$info->enablePrivateNotes();
		
		/*
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			//$info->enableNewsEditing();
			$info->setBlockProperty("news", "settings", true);
		}*/
		
		// general information
		$this->lng->loadLanguageModule("meta");
		$this->lng->loadLanguageModule("mcst");
		$med_items = $this->object->getItemsArray();
		$info->addSection($this->lng->txt("meta_general"));
		$info->addProperty($this->lng->txt("mcst_nr_items"),
			(int) count($med_items));
			
		if (count($med_items) > 0)
		{
			$cur = current($med_items);
			$last = $cur["creation_date"];
		}
		else
		{
			$last = "-";
		}

		$info->addProperty($this->lng->txt("mcst_last_submission"), $last);

		// forward the command
		$this->ctrl->forwardCommand($info);
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilCtrl, $ilAccess;
		
		// list items
		if ($ilAccess->checkAccess('read', "", $this->object->getRefId()))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, "listItems"), array("", "listItems"),
				array(strtolower(get_class($this)), ""));
		}

		// info screen
		if ($ilAccess->checkAccess('visible', "", $this->object->getRefId()))
		{
			$force_active = ($ilCtrl->getNextClass() == "ilinfoscreengui"
				|| $_GET["cmd"] == "infoScreen")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
				$this->ctrl->getLinkTargetByClass(
				"ilinfoscreengui", "showSummary"),
				"showSummary",
				"", "", $force_active);
		}

		// settings
		if ($ilAccess->checkAccess('write', "", $this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "editSettings"), array("editSettings"),
				array(strtolower(get_class($this)), ""));
		}

		// edit permissions
		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	* Edit settings
	*/
	function editSettingsObject()
	{
		global $tpl;
		
		$this->initSettingsForm();
		$tpl->setContent($this->form_gui->getHtml());
	}
	
	/**
	* Init Settings Form
	*/
	function initSettingsForm()
	{
		global $tpl, $lng, $ilCtrl;
		
		$lng->loadLanguageModule("mcst");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setTitle($lng->txt("mcst_settings"));
		
		// Title
		$tit = new ilTextInputGUI($lng->txt("title"), "title");
		$tit->setValue($this->object->getTitle());
		$tit->setRequired(true);
		$this->form_gui->addItem($tit);

		// description
		$des = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$des->setValue($this->object->getDescription());
		$this->form_gui->addItem($des);

		// Online
		$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
		$online->setChecked($this->object->getOnline());
		$this->form_gui->addItem($online);
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");

		// if rss is globally activated...
		if ($enable_internal_rss)
		{
		
			// Extra Feed
			include_once("./Services/Block/classes/class.ilBlockSetting.php");
			$public_feed = ilBlockSetting::_lookup("news", "public_feed",
				0, $this->object->getId());
			$ch = new ilCheckboxInputGUI($lng->txt("news_public_feed"),
				"extra_feed");
			$ch->setInfo($lng->txt("news_public_feed_info"));
			$ch->setChecked($public_feed);
			$this->form_gui->addItem($ch);
			
			// Include Files in Pubic Items
			$incl_files = new ilCheckboxInputGUI($lng->txt("mcst_incl_files_in_rss"), "public_files");
			$incl_files->setChecked($this->object->getPublicFiles());
			$incl_files->setInfo($lng->txt("mcst_incl_files_in_rss_info"));
			$this->form_gui->addItem($incl_files);
		}
		
		// Form action and save button
		$this->form_gui->addCommandButton("saveSettings", $lng->txt("save"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveSettings"));
	}
	
	/**
	* Save Settings
	*/
	function saveSettingsObject()
	{
		global $ilCtrl;
		
		$this->initSettingsForm();
		if ($this->form_gui->checkInput())
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			$this->object->setTitle($this->form_gui->getInput("title"));
			$this->object->setDescription($this->form_gui->getInput("description"));
			$this->object->setOnline($this->form_gui->getInput("online"));
			if ($enable_internal_rss)
			{
				$this->object->setPublicFiles($this->form_gui->getInput("public_files"));
			}
			$this->object->update();
			
			if ($enable_internal_rss)
			{
				include_once("./Services/Block/classes/class.ilBlockSetting.php");
				ilBlockSetting::_write("news", "public_feed",
					$this->form_gui->getInput("extra_feed"),
					0, $this->object->getId());
			}
			
			ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
			$ilCtrl->redirect($this, "editSettings");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHTML());
		}
	}

	// add media cast to locator
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
		}
	}

	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "listItems";
			$_GET["ref_id"] = $a_target;
			$_GET["baseClass"] = "ilmediacasthandlergui";
			$_GET["cmdClass"] = "ilobjmediacastgui";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);

	}

}
?>
