<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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


/**
* User Interface class for file based learning modules (HTML)
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilShopPurchaseGUI
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilLicenseGUI
* @ingroup ModulesHTMLLearningModule
*/

require_once("classes/class.ilObjectGUI.php");
require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
require_once("./Services/Table/classes/class.ilTableGUI.php");
require_once("classes/class.ilFileSystemGUI.php");

class ilObjFileBasedLMGUI extends ilObjectGUI
{
	var $output_prepared;

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjFileBasedLMGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng, $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id"));
		
		#include_once("classes/class.ilTabsGUI.php");
		#$this->tabs_gui =& new ilTabsGUI();

		$this->type = "htlm";
		$lng->loadLanguageModule("content");

		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
		//$this->actions = $this->objDefinition->getActions("mep");
		$this->output_prepared = $a_prepare_output;

	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser, $ilLocator;		
	
		if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
			$this->getCreationMode() == true)
		{
			$this->prepareOutput();
		}
		else
		{
			$this->getTemplate();
			$this->setLocator();
			$this->setTabs();
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
			
		if(!$this->getCreationMode())
		{
			include_once 'payment/classes/class.ilPaymentObject.php';				
			if(ilPaymentObject::_isBuyable($_GET['ref_id']) &&
			   !ilPaymentObject::_hasAccess($_GET['ref_id']))
			{
				$this->tpl->getStandardTemplate();				
				
				include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
				$pp = new ilShopPurchaseGUI((int)$_GET['ref_id']);				
				$ret = $this->ctrl->forwardCommand($pp);
				return true;
			}
		}

		switch($next_class)
		{
			case 'ilmdeditorgui':
				$this->checkPermission("write");
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			case "ilfilesystemgui":
				$this->checkPermission("write");
				$fs_gui =& new ilFileSystemGUI($this->object->getDataDirectory());
				$fs_gui->activateLabels(true, $this->lng->txt("cont_purpose"));
				$fs_gui->setTableId("htlmfs".$this->object->getId());
				if ($this->object->getStartFile() != "")
				{
					$fs_gui->labelFile($this->object->getStartFile(),
						$this->lng->txt("cont_startfile"));
				}
				$fs_gui->addCommand($this, "setStartFile", $this->lng->txt("cont_set_start_file"));
				$ret =& $this->ctrl->forwardCommand($fs_gui);
				break;

			case "ilinfoscreengui":
				$ret =& $this->outputInfoScreen();
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				
				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;
				
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'illicensegui':
				include_once("./Services/License/classes/class.ilLicenseGUI.php");
				$license_gui =& new ilLicenseGUI($this);
				$ret =& $this->ctrl->forwardCommand($license_gui);
				break;

			default:				
				$cmd = $this->ctrl->getCmd("frameset");
				if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
					$this->getCreationMode() == true)
				{
					$cmd.= "Object";
				}
				$ret =& $this->$cmd();
				break;
		}
		//$this->tpl->show();
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem, $tpl;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->ctrl->setParameter($this, "new_type", $new_type);
			$this->initEditForm("create", $new_type);
			$tpl->setContent($this->form->getHTML());

			$tpl->setContent($this->form->getHTML().$clone_html);
		}
	}
	
	
	/**
	* save object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $objDefinition, $tpl, $lng;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}
		$this->ctrl->setParameter($this, "new_type", $new_type);
		$this->initEditForm("create", $new_type);
		if ($this->form->checkInput())
		{
			
			$location = $objDefinition->getLocation($new_type);
	
				// create and insert object in objecttree
			$class_name = "ilObj".$objDefinition->getClassName($new_type);
			include_once($location."/class.".$class_name.".php");
			$newObj = new $class_name();
			$newObj->setType($new_type);
			$newObj->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$newObj->setDescription(ilUtil::stripSlashes($_POST["desc"]));
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);
			$newObj->setPermissions($_GET["ref_id"]);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->afterSave($newObj);
			return;
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	* Init object creation form
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initEditForm($a_mode = "edit", $a_new_type = "")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTarget("_top");
	
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$this->form->addItem($ta);
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $lng->txt($a_new_type."_add"));
			$this->form->addCommandButton("cancelCreation", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt($a_new_type."_new"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("edit"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}

	/**
	* Get values for edit form
	*/
	function getEditFormValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	final function cancelCreationObject($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function properties()
	{
		global $rbacsystem, $tree, $tpl;

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view link
		require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
		$startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

		if ($startfile != "")
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",
				"ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
			$this->tpl->parseCurrentBlock();
		}

		// lm properties
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.fblm_properties.html",
			'Modules/HTMLLearningModule');
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_PROPERTIES", $this->lng->txt("cont_lm_properties"));

		// online
		$this->tpl->setVariable("TXT_ONLINE", $this->lng->txt("cont_online"));
		$this->tpl->setVariable("CBOX_ONLINE", "cobj_online");
		$this->tpl->setVariable("VAL_ONLINE", "y");
		if ($this->object->getOnline())
		{
			$this->tpl->setVariable("CHK_ONLINE", "checked");
		}

		// start file
		$this->tpl->setVariable("TXT_START_FILE", $this->lng->txt("cont_startfile"));
		if ($startfile != "")
		{
			$this->tpl->setVariable("VAL_START_FILE", basename($startfile));
		}
		else
		{
			$this->tpl->setVariable("VAL_START_FILE", $this->lng->txt("no_start_file"));
		}
		$this->tpl->setVariable("TXT_SET_START_FILE", $this->lng->txt("cont_set_start_file"));
		$this->tpl->setVariable("LINK_SET_START_FILE",
			$this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));

		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* save properties
	*/
	function saveProperties()
	{
		$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
		$this->object->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $tree, $tpl;

		if (!$rbacsystem->checkAccess("visible,write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

	}

	/**
	* edit properties of object (module form)
	*/
	function edit()
	{
		$this->prepareOutput();
		$this->editObject();
	}

	/**
	* cancel editing
	*/
	function cancel()
	{
		//$this->setReturnLocation("cancel","fblm_edit.php?cmd=listFiles&ref_id=".$_GET["ref_id"]);
		$this->cancelObject();
	}
	
	/**
	* save object
	* @access	public
	*/
	function afterSave($newObj)
	{
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		ilUtil::redirect("ilias.php?baseClass=ilHTLMEditorGUI&ref_id=".$newObj->getRefId());
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
		//$this->ctrl->redirectByClass("ilrepositorygui", "frameset");
	}


	/**
	* update properties
	*/
	function update()
	{
		//$this->setReturnLocation("update", "fblm_edit.php?cmd=listFiles&ref_id=".$_GET["ref_id"].
		//	"&obj_id=".$_GET["obj_id"]);
		$this->updateObject();
	}


	function setStartFile($a_file)
	{
		$this->object->setStartFile($a_file);
		$this->object->update();
		$this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
	}

	/**
	* permission form
	*/
	function perm()
	{
		$this->setFormAction("permSave", "fblm_edit.php?cmd=permSave&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->setFormAction("addRole", "fblm_edit.php?ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]."&cmd=addRole");
		$this->permObject();
	}
	
	/**
	* save bib item (admin call)
	*/
	function saveBibItemObject($a_target = "")
	{
		include_once "./Modules/LearningModule/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		$bibItemIndex *= 1;
		if ($bibItemIndex < 0)
		{
			$bibItemIndex = 0;
		}
		$bibItemIndex = $bib_gui->save($bibItemIndex);

		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* save bib item (module call)
	*/
	function saveBibItem()
	{
		// questionable workaround to make this old stuff work
		$this->ctrl->setParameter($this, ilCtrl::IL_RTOKEN_NAME, $this->ctrl->getRequestToken());

		//$this->setTabs();
		$this->saveBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* edit bib items (admin call)
	*/
	function editBibItemObject($a_target = "")
	{
		include_once "./Modules/LearningModule/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		$bibItemIndex *= 1;
		if ($bibItemIndex < 0)
		{
			$bibItemIndex = 0;
		}
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* edit bib items (module call)
	*/
	function editBibItem()
	{
		// questionable workaround to make this old stuff work
		$this->ctrl->setParameter($this, ilCtrl::IL_RTOKEN_NAME, $this->ctrl->getRequestToken());

		//$this->setTabs();
		$this->editBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* delete bib item (admin call)
	*/
	function deleteBibItemObject($a_target = "")
	{
		include_once "./Modules/LearningModule/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		$bib_gui->bib_obj->delete($_GET["bibItemName"], $_GET["bibItemPath"], $bibItemIndex);
		if (strpos($bibItemIndex, ",") > 0)
		{
			$bibItemIndex = substr($bibItemIndex, 0, strpos($bibItemIndex, ","));
		}
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* delete bib item (module call)
	*/
	function deleteBibItem()
	{
		// questionable workaround to make this old stuff work
		$this->ctrl->setParameter($this, ilCtrl::IL_RTOKEN_NAME, $this->ctrl->getRequestToken());

		//$this->setTabs();
		$this->deleteBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* add bib item (admin call)
	*/
	function addBibItemObject($a_target = "")
	{
		$bibItemName = $_POST["bibItemName"] ? $_POST["bibItemName"] : $_GET["bibItemName"];
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		if ($bibItemName == "BibItem")
		{
			include_once "./Modules/LearningModule/classes/class.ilBibItem.php";
			$bib_item =& new ilBibItem();
			$bib_item->setId($this->object->getId());
			$bib_item->setType($this->object->getType());
			$bib_item->read();
		}

		include_once "./Modules/LearningModule/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		if ($bibItemIndex == "")
			$bibItemIndex = 0;
		$bibItemPath = $_POST["bibItemPath"] ? $_POST["bibItemPath"] : $_GET["bibItemPath"];

		//if ($bibItemName != "" && $bibItemName != "BibItem")
		if ($bibItemName != "")
		{
			$bib_gui->bib_obj->add($bibItemName, $bibItemPath, $bibItemIndex);
			$data = $bib_gui->bib_obj->getElement("BibItem");
			$bibItemIndex = (count($data) - 1);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("bibitem_choose_element"), true);
		}
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* add bib item (module call)
	*/
	function addBibItem()
	{
		// questionable workaround to make this old stuff work
		$this->ctrl->setParameter($this, ilCtrl::IL_RTOKEN_NAME, $this->ctrl->getRequestToken());

		//$this->setTabs();
		$this->addBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.fblm_edit_frameset.html", false, false,
			"Modules/HTMLLearningModule");
		$this->tpl->setVariable("HREF_FILES",$this->ctrl->getLinkTargetByClass(
			"ilfilesystemgui", "listFiles"));
		$this->tpl->show();
		exit;
	}

	/**
	* directory explorer
	*/
	function explorer()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		require_once ("./Modules/HTMLLearningModule/classes/class.ilFileExplorer.php");
		$exp = new ilFileExplorer($this->lm->getDataDirectory());

	}

	/**
	* output main header (title and locator)
	*/
	function getTemplate()
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		//$this->tpl->setVariable("HEADER", $a_header_title);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		//$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
	}

	function showLearningModule()
	{
		// Note license usage
		include_once "Services/License/classes/class.ilLicense.php";
		ilLicense::_noteAccess($this->object->getId());

		// Track access
		include_once "Services/Tracking/classes/class.ilTracking.php";
		ilTracking::_trackAccess($this->object->getId(),'htlm');
		
		require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
		$startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());
		if ($startfile != "")
		{
			ilUtil::redirect($startfile);
		}
	}

	// InfoScreen methods
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreen()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->outputInfoScreen();
	}

	/**
	* info screen call from inside learning module
	*/
	function showInfoScreen()
	{
		$this->outputInfoScreen(true);
	}

	/**
	* info screen
	*/
	function outputInfoScreen($a_standard_locator = true)
	{
		global $ilBench, $ilLocator, $ilAccess;


		$this->tabs_gui->setTabActive('info_short');
		
		$this->lng->loadLanguageModule("meta");
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
		$info->enableLearningProgress();
		
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
			
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
			}
		}

		// add read / back button
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$info->addButton($this->lng->txt("view"),
				"ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=".$this->object->getRefID(),
				' target="ilContObj'.$this->object->getId().'" ');
		}
		
		// show standard meta data section
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());

		// forward the command
		$this->ctrl->forwardCommand($info);
	}



	/**
	* output tabs
	*/
	function setTabs()
	{
		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_lm_b.gif"));
		$this->tpl->parseCurrentBlock();
		
		$this->getTabs($this->tabs_gui);
		#$this->tpl->setVariable("TABS", $this->tabs_gui->getHTML());
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilUser;
		

		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			// properties
			$tabs_gui->addTarget("cont_list_files",
								 $this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"), "",
								 "ilfilesystemgui");
			
			// info screen
			$force_active = (strtolower($_GET["cmdClass"]) == "ilinfoscreengui"
							 || strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(array("ilobjfilebasedlmgui",
																		 "ilinfoscreengui"), 
																   "showSummary"),
								 "infoScreen",
								 "", 
								 "",
								 $force_active);
			
			// properties
			$tabs_gui->addTarget("properties",
								 $this->ctrl->getLinkTarget($this, "properties"), "properties",
								 get_class($this));
			
			$tabs_gui->addTarget("meta_data",
								 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
								 "", "ilmdeditorgui");
			
			// edit bib item information
			$tabs_gui->addTarget("bib_data",
								 $this->ctrl->getLinkTarget($this, "editBibItem"),
								 array("editBibItem", "saveBibItem", "deleteBibItem", "addBibItem"),
								 get_class($this));
		}

		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjfilebasedlmgui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}

		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id)
		and ilLicenseAccess::_isEnabled())
		{
			$tabs_gui->addTarget("license",
				$this->ctrl->getLinkTargetByClass('illicensegui', ''),
			"", "illicensegui");
		}

		// perm
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target)
	{
		global $rbacsystem, $ilErr, $lng, $ilAccess;

		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "infoScreen";
			include("repository.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{		
			$ilLocator->addItem($this->object->getTitle(),
				$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "", $_GET["ref_id"]);
		}
	}
}
?>
