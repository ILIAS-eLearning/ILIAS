<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");
include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
include_once("./Services/Table/classes/class.ilTableGUI.php");
include_once("./Modules/Folder/classes/class.ilObjFolderGUI.php");
include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
include_once("./Services/Clipboard/classes/class.ilEditClipboardGUI.php");


/**
* User Interface class for media pool objects
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ilCtrl_Calls ilObjMediaPoolGUI: ilObjMediaObjectGUI, ilObjFolderGUI, ilEditClipboardGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjMediaPoolGUI: ilInfoScreenGUI, ilMediaPoolPageGUI
*
* @ingroup ModulesMediaPool
*/
class ilObjMediaPoolGUI extends ilObject2GUI
{
	var $output_prepared;

	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = false)
	{
		parent::__construct($a_id, $a_call_by_reference);
	}
	
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		global $lng;
		
		$lng->loadLanguageModule("mep");
		
		if ($this->ctrl->getCmd() == "explorer")
		{
			$this->ctrl->saveParameter($this, array("ref_id"));
		}
		else
		{
			$this->ctrl->saveParameter($this, array("ref_id", "mepitem_id"));
		}
		$this->ctrl->saveParameter($this, array("mep_mode"));
		
		$lng->loadLanguageModule("content");
	}

	/**
	* Get type
	*/
	final function getType()
	{
		return "mep";
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilTabs, $lng, $ilAccess, $tpl, $ilCtrl;
		
		if ($this->ctrl->getRedirectSource() == "ilinternallinkgui")
		{
			$this->explorer();
			return;
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];

		if ($new_type != "" && ($cmd != "confirmRemove" && $cmd != "copyToClipboard"
			&& $cmd != "pasteFromClipboard"))
		{
			$this->setCreationMode(true);
		}

		if (!$this->getCreationMode())
		{
			$tree =& $this->object->getTree();
			if ($_GET["mepitem_id"] == "")
			{
				$_GET["mepitem_id"] = $tree->getRootId();
			}
		}

		if ($cmd == "create")
		{
			switch($_POST["new_type"])
			{
				case "mob":
					$this->ctrl->redirectByClass("ilobjmediaobjectgui", "create");
					break;
					
				case "fold":
// todo
					$this->ctrl->redirectByClass("ilobjfoldergui", "create");
					break;
			}
		}

		switch($next_class)
		{
			case 'ilmediapoolpagegui':
				$this->prepareOutput();
				$this->setMediaPoolPageTabs();
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");
				$mep_page_gui = new ilMediaPoolPageGUI($_GET["mepitem_id"], $_GET["old_nr"]);

				if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
				{
					$mep_page_gui->setEnableEditing(false);
				}
				$ret = $this->ctrl->forwardCommand($mep_page_gui);
				if ($ret != "")
				{
					$tpl->setContent($ret);
				}
				$this->tpl->show();
				break;

			case "ilobjmediaobjectgui":

				//$cmd.="Object";
				if ($cmd == "create" || $cmd == "save" || $cmd == "cancel")
				{
					$ret_obj = $_GET["mepitem_id"];
					$ilObjMediaObjectGUI =& new ilObjMediaObjectGUI("", 0, false, false);
					$ilObjMediaObjectGUI->setWidthPreset($this->object->getDefaultWidth());
					$ilObjMediaObjectGUI->setHeightPreset($this->object->getDefaultHeight());
				}
				else
				{
					$ret_obj = $tree->getParentId($_GET["mepitem_id"]);
					$ilObjMediaObjectGUI =& new ilObjMediaObjectGUI("", ilMediaPoolItem::lookupForeignId($_GET["mepitem_id"]), false, false);
					$this->ctrl->setParameter($this, "mepitem_id", $this->getParentFolderId());
					$ilTabs->setBackTarget($lng->txt("back"),
						$this->ctrl->getLinkTarget($this,
							$_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia"));
				}
				if ($this->ctrl->getCmdClass() == "ilinternallinkgui")
				{
					$this->ctrl->setReturn($this, "explorer");
				}
				else
				{
					$this->ctrl->setParameter($this, "mepitem_id", $ret_obj);
					$this->ctrl->setReturn($this,
						$_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
					$this->ctrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
				}
				$this->getTemplate();
				$ilObjMediaObjectGUI->setTabs();
				$this->setLocator();

				//$ret =& $ilObjMediaObjectGUI->executeCommand();
				$ret =& $this->ctrl->forwardCommand($ilObjMediaObjectGUI);

				switch($cmd)
				{
					case "save":
						$mep_item = new ilMediaPoolItem();
						$mep_item->setTitle($ret->getTitle());
						$mep_item->setType("mob");
						$mep_item->setForeignId($ret->getId());
						$mep_item->create();

						$parent = ($_GET["mepitem_id"] == "")
							? $tree->getRootId()
							: $_GET["mepitem_id"];
						$tree->insertNode($mep_item->getId(), $parent);
						ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&cmd=listMedia&ref_id=".
							$_GET["ref_id"]."&mepitem_id=".$_GET["mepitem_id"]);
						break;

					default:
						$this->tpl->show();
						break;
				}
				break;

			case "ilobjfoldergui":
// todo
				$folder_gui = new ilObjFolderGUI("", 0, false, false);
				$this->ctrl->setReturn($this, "listMedia");
				$cmd.="Object";
				switch($cmd)
				{
					case "createObject":
						$this->prepareOutput();
						$folder_gui =& new ilObjFolderGUI("", 0, false, false);
						$folder_gui->setFormAction("save",
							$this->ctrl->getFormActionByClass("ilobjfoldergui"));
						$folder_gui->createObject();
						$this->tpl->show();
						break;

					case "saveObject":
						//$folder_gui->setReturnLocation("save", $this->ctrl->getLinkTarget($this, "listMedia"));
						$parent = ($_GET["mepitem_id"] == "")
							? $tree->getRootId()
							: $_GET["mepitem_id"];
						$folder_gui->setFolderTree($tree);
						$folder_gui->saveObject($parent);
						//$this->ctrl->redirect($this, "listMedia");
						break;

					case "editObject":
						$this->prepareOutput();
						$folder_gui =& new ilObjFolderGUI("", ilMediaPoolItem::lookupForeignId($_GET["mepitem_id"]), false, false);
						$this->ctrl->setParameter($this, "foldereditmode", "1");
						$folder_gui->setFormAction("update", $this->ctrl->getFormActionByClass("ilobjfoldergui"));
						$folder_gui->editObject();
						$this->tpl->show();
						break;

					case "updateObject":
						$folder_gui =& new ilObjFolderGUI("", ilMediaPoolItem::lookupForeignId($_GET["mepitem_id"]), false, false);
						$this->ctrl->setParameter($this, "mepitem_id", $this->getParentFolderId());
						$this->ctrl->setReturn($this, "listMedia");
						$folder_gui->updateObject(true);		// this returns to parent
						break;

					case "cancelObject":
						if ($_GET["foldereditmode"])
						{
							$this->ctrl->setParameter($this, "mepitem_id", $this->getParentFolderId());
						}
						$this->ctrl->redirect($this, "listMedia");
						break;
				}
				break;

			case "ileditclipboardgui":
				$this->prepareOutput();
				$this->ctrl->setReturn($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
				$clip_gui = new ilEditClipboardGUI();
				$clip_gui->setMultipleSelections(true);
				$clip_gui->setInsertButtonTitle($lng->txt("mep_copy_to_mep"));
				$ilTabs->setTabActive("clipboard");
				//$ret =& $clip_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($clip_gui);
				$this->tpl->show();
				break;
				
			case 'ilinfoscreengui':
				$this->prepareOutput();
				$this->infoScreen();
				$this->tpl->show();
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				$this->tpl->show();
				break;

			default:
				$this->prepareOutput();
				$cmd = $this->ctrl->getCmd("frameset");
				$this->$cmd();
				if (!$this->getCreationMode())
				{
					$this->tpl->show();
				}
				break;
		}
	}

	
	function createMediaObject()
	{
		$this->ctrl->redirectByClass("ilobjmediaobjectgui", "create");
	}
	
	// for admin compatiblity
	function view()
	{
		$this->viewObject();
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
		if ($a_mode != "edit")
		{
			$this->form->setTarget("_top");
		}
	
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
		
		if ($a_mode == "edit")
		{
			// default width
			$ni = new ilNumberInputGUI($this->lng->txt("mep_default_width"), "default_width");
			$ni->setMinValue(0);
			$ni->setMaxLength(5);
			$ni->setSize(5);
			$this->form->addItem($ni);
			
			// default height
			$ni = new ilNumberInputGUI($this->lng->txt("mep_default_height"), "default_height");
			$ni->setMinValue(0);
			$ni->setMaxLength(5);
			$ni->setSize(5);
			$ni->setInfo($this->lng->txt("mep_default_width_height_info"));
			$this->form->addItem($ni);
		}
	
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
			//$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
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
		if ($this->object->getDefaultWidth() > 0)
		{
			$values["default_width"] = $this->object->getDefaultWidth();
		}
		if ($this->object->getDefaultHeight() > 0)
		{
			$values["default_height"] = $this->object->getDefaultHeight();
		}
		$this->form->setValuesByArray($values);
	}

	/**
	* save object
	* @access	public
	*/
	function afterSave($newObj)
	{
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
		ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&ref_id=".$newObj->getRefId()."&cmd=edit");
	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function editObject()
	{
		global $ilAccess, $tree, $tpl;

		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		parent::editObject();
	}

	/**
	* edit properties of object (module form)
	*/
	function edit()
	{
		$this->editObject();
//		$this->tpl->show();
	}

	/**
	* cancel editing
	*/
	protected function cancel()
	{
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}

	/**
	* update properties
	*/
	function update()
	{
		global $ilAccess;

		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->updateObject();
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function updateObject()
	{
		global $lng, $tpl;
		
		$this->initEditForm("edit");
		if ($this->form->checkInput())
		{
			$this->object->setTitle($_POST["title"]);
			$this->object->setDescription($_POST["desc"]);
			$this->object->setDefaultWidth($_POST["default_width"]);
			$this->object->setDefaultHeight($_POST["default_height"]);
			$this->update = $this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->afterUpdate();
			return;
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	function afterUpdate()
	{
		$this->ctrl->redirect($this, "edit");
	}

	/**
	* list media objects
	*/
	function listMedia()
	{
		global $tree, $ilAccess, $tpl, $ilTabs, $ilCtrl;
		
		$ilCtrl->setParameter($this, "mep_mode", "listMedia");

		if (!$ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		$ilTabs->setTabActive("objs_fold");
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
		$mep_table_gui = new ilMediaPoolTableGUI($this, "listMedia", $this->object, "mepitem_id");
		$tpl->setContent($mep_table_gui->getHTML());
//		$this->tpl->show();
	}

	/**
	* list all objects
	*/
	function allMedia()
	{
		global $tree, $ilAccess, $tpl, $ilTabs, $ilCtrl;

		$ilCtrl->setParameter($this, "mep_mode", "allMedia");
		
		if (!$ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		$ilTabs->setTabActive("mep_all_mobs");
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
		$mep_table_gui = new ilMediaPoolTableGUI($this, "allMedia", $this->object,
			"mepitem_id", ilMediaPoolTableGUI::IL_MEP_EDIT, true);
		$tpl->setContent($mep_table_gui->getHTML());
//		$this->tpl->show();
	}

	/**
	* Apply filter
	*/
	function applyFilter()
	{
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
		$mtab = new ilMediaPoolTableGUI($this, "allMedia", $this->object,
			"mepitem_id", ilMediaPoolTableGUI::IL_MEP_EDIT, true);
		$mtab->writeFilterToSession();
		$mtab->resetOffset();
		$this->allMedia();
	}

	/**
	* Get standard template
	*/
	function getTemplate()
	{
		$this->tpl->getStandardTemplate();
	}


	/**
	* Get folder parent ID
	*/
	function getParentFolderId()
	{
		if ($_GET["mepitem_id"] == "")
		{
			return "";
		}
		$par_id = $this->object->tree->getParentId($_GET["mepitem_id"]);
		if ($par_id != $this->object->tree->getRootId())
		{
			return $par_id;
		}
		else
		{
			return "";
		}
	}
	
	/**
	* show upper icon (standard procedure will work, if no
	* obj_id is given)
	*/
/*
	function showUpperIcon()
	{
		global $tpl;
		
		if ($this->ctrl->getCmd() == "explorer")
		{
			return;
		}
		
		parent::showUpperIcon();
		
		$mep_tree =& $this->object->getTree();
		if ($_GET["obj_id"] != "" && $_GET["obj_id"] != $mep_tree->getRootId())
		{
			$this->ctrl->setParameter($this, "obj_id",
				$this->getParentFolderId());
			$tpl->setUpperIcon($this->ctrl->getLinkTarget($this, "listMedia"));
			$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		}
	}
*/

	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();
		$fs_gui->setMainFrameName("content");
		$fs_gui->setSideFrameName("tree");
		$fs_gui->setMainFrameSource(
			$this->ctrl->getLinkTarget($this, "listMedia"));
		$this->ctrl->setParameter($this, "expand", "1");
		$fs_gui->setSideFrameSource(
			$this->ctrl->getLinkTarget($this, "explorer"));
		$fs_gui->setFramesetTitle($this->object->getTitle());
		$fs_gui->show();
		exit;
	}

	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function infoScreenFrameset()
	{
		include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();
		$fs_gui->setMainFrameName("content");
		$fs_gui->setSideFrameName("tree");
		$fs_gui->setMainFrameSource(
			$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		$this->ctrl->setParameter($this, "expand", "1");
		$fs_gui->setSideFrameSource(
			$this->ctrl->getLinkTarget($this, "explorer"));
		$fs_gui->setFramesetTitle($this->object->getTitle());
		$fs_gui->show();
		exit;
	}

	/**
	* output explorer tree
	*/
	function explorer()
	{
		global $ilAccess, $ilCtrl;
		
		$ilCtrl->setParameter($this, "obj_id", "");
		$ilCtrl->setParameter($this, "mepitem_id", "");
		
		if (!$ilAccess->checkAccess("read", "", $this->object->getRefId()) ||
			!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			return;
		}
		
		require_once ("./Modules/MediaPool/classes/class.ilMediaPoolExplorer.php");
		$exp = new ilMediaPoolExplorer($this->ctrl->getLinkTarget($this, "listMedia"), $this->object);
		$exp->setUseStandardFrame(true);
		$exp->setTargetGet("mepitem_id");
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, "explorer"));
		$exp->setTitle($this->lng->txt("cont_mep_structure"));

		$exp->addFilter("dummy");
		$exp->addFilter("fold");
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);


		if ($_GET["mepexpand"] == "")
		{
			$mep_tree =& $this->object->getTree();
			$expanded = $mep_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["mepexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		echo $output;
		exit;
/*		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_mep_structure"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->ctrl->setParameter($this, "mepexpand", $_GET["mepexpand"]);
		$this->tpl->setVariable("ACTION",
			$this->ctrl->getLinkTarget($this, "explorer"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);
*/
	}
	
	/**
	* show media object
	*/
	function showMedia()
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tpl =& new ilTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
		include_once("Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));

		//$int_links = $page_object->getInternalLinks();
		$med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);
		
		// later
		//$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());
		
		$link_xlm = "";

		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$media_obj =& new ilObjMediaObject($_GET["mob_id"]);
		
		$xml = "<dummy>";
		// todo: we get always the first alias now (problem if mob is used multiple
		// times in page)
		$xml.= $media_obj->getXML(IL_MODE_ALIAS);
		$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
		$xml.= $link_xml;
		$xml.="</dummy>";

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

		$wb_path = ilUtil::getWebspaceDir("output");

		$mode = ($_GET["cmd"] != "showMedia")
			? "fullscreen"
			: "media";
		$enlarge_path = ilUtil::getImagePath("enlarge.gif", false, "output");
		$fullscreen_link =
			$this->ctrl->getLinkTarget($this, "showFullscreen");
		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => $fullscreen_link,
			'ref_id' => $_GET["ref_id"], 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);

		$this->tpl->parseCurrentBlock();
//		$this->tpl->show();
	}

	/**
	* show fullscreen 
	*/
	function showFullscreen()
	{
		$this->showMedia();
	}
	
	/**
	* confirm remove of mobs
	*/
	function confirmRemove()
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		//$this->prepareOutput();

		// SAVE POST VALUES
		$_SESSION["ilMepRemove"] = $_POST["id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/MediaPool");

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["id"] as $obj_id)
		{
			$type = ilMediaPoolItem::lookupType($obj_id);
			$title = ilMediaPoolItem::lookupTitle($obj_id);
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->setVariable("TEXT_CONTENT", $title);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_".$type.".gif"));
			$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelRemove"  => $this->lng->txt("cancel"),
			"remove"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
//		$this->tpl->show();
	}
	
	/**
	* paste from clipboard
	*/
	function openClipboard()
	{
		global $ilCtrl, $ilAccess;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$ilCtrl->setParameterByClass("ileditclipboardgui", "returnCommand",
			rawurlencode($ilCtrl->getLinkTarget($this,
			"insertFromClipboard")));
		$ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
	}
	
	
	/**
	* insert media object from clipboard
	*/
	function insertFromClipboard()
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/Clipboard/classes/class.ilEditClipboardGUI.php");
		$ids = ilEditClipboardGUI::_getSelectedIDs();
		$not_inserted = array();
		if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				if (ilObjMEdiaPool::isForeignIdInTree($this->object->getId(), $id))
				{
					$not_inserted[] = ilObject::_lookupTitle($id)." [".
						$id."]";
				}
				else
				{
					$item = new ilMediaPoolItem();
					$item->setType("mob");
					$item->setForeignId($id);
					$item->setTitle(ilObject::_lookupTitle($id));
					$item->create();
					if ($item->getId() > 0)
					{
						$this->object->insertInTree($item->getId(), $_GET["mepitem_id"]);
					}
				}
			}
		}
		if (count($not_inserted) > 0)
		{
			ilUtil::sendInfo($this->lng->txt("mep_not_insert_already_exist")."<br>".
				implode($not_inserted,"<br>"), true);
		}
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}


	/**
	* cancel deletion of media objects/folders
	*/
	function cancelRemove()
	{
		session_unregister("ilMepRemove");
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}

	/**
	* confirm deletion of
	*/
	function remove()
	{
		global $ilAccess;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		foreach($_SESSION["ilMepRemove"] as $obj_id)
		{
			$this->object->deleteChild($obj_id);
		}

		ilUtil::sendSuccess($this->lng->txt("cont_obj_removed"),true);
		session_unregister("ilMepRemove");
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}


	/**
	* copy media objects to clipboard
	*/
	function copyToClipboard()
	{
		global $ilUser, $ilAccess;

		$this->checkPermission("write");		

		if(!isset($_POST["id"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$type = ilMediaPoolItem::lookupType($obj_id);
			if ($type == "fold")
			{
				ilUtil::sendFailure($this->lng->txt("cont_cant_copy_folders"), true);
				$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
			}
		}
		foreach ($_POST["id"] as $obj_id)
		{
			$fid = ilMediaPoolItem::lookupForeignId($obj_id);
			$type = ilMediaPoolItem::lookupType($obj_id);
			if ($type == "mob")
			{
				$ilUser->addObjectToClipboard($fid, "mob", "");
			}
		}

		ilUtil::sendSuccess($this->lng->txt("copied_to_clipboard"),true);
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}

	/**
	* add locator items for media pool
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (!$this->getCreationMode() && $this->ctrl->getCmd() != "explorer")
		{
			$tree =& $this->object->getTree();
			$obj_id = ($_GET["mepitem_id"] == "")
				? $tree->getRootId()
				: $_GET["mepitem_id"];
			$path = $tree->getPathFull($obj_id);
			foreach($path as $node)
			{
				if ($node["child"] == $tree->getRootId())
				{
					$this->ctrl->setParameter($this, "mepitem_id", "");
					$link = $this->ctrl->getLinkTarget($this, "listMedia");
					$title = $this->object->getTitle();
					$this->ctrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
					$ilLocator->addItem($title, $link, "", $_GET["ref_id"]);
				}
				else
				{
					$this->ctrl->setParameter($this, "mepitem_id", $node["child"]);
					$link = $this->ctrl->getLinkTarget($this, "listMedia");
					$title = $node["title"];
					$this->ctrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
					$ilLocator->addItem($title, $link);
				}
			}
		}
	}
	
	////
	//// FOLDER Handling
	////
	
	/**
	* create folder form
	*/
	function createFolderForm()
	{
		global $ilAccess, $tpl;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initFolderForm("create");
		$tpl->setContent($this->form->getHTML());

/*		$folder_gui =& new ilObjFolderGUI("", 0, false, false);
		$this->ctrl->setParameterByClass("ilobjfoldergui", "obj_id", $_GET["obj_id"]);
		$folder_gui->setFormAction("save",
			$this->ctrl->getFormActionByClass("ilobjfoldergui"));
		$folder_gui->createObject();*/
//		$this->tpl->show();
	}

	/**
	 * Edit folder
	 *
	 * @param
	 * @return
	 */
	function editFolder()
	{
		global $tpl;
	
		$this->initFolderForm();
		$this->getFolderValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Get current values for folder from 
	 */
	public function getFolderValues()
	{
		$values = array();
	
		$values["title"] = ilMediaPoolItem::lookupTitle($_GET["mepitem_id"]);
	
		$this->form->setValuesByArray($values);
	}

	/**
	 * Save folder form
	 */
	public function saveFolder()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initFolderForm("create");
		if ($this->form->checkInput())
		{
			// perform save
			$mep_item = new ilMediaPoolItem();
			$mep_item->setTitle($_POST["title"]);
			$mep_item->setType("fold");
			$mep_item->create();
			if ($mep_item->getId() > 0)
			{
				$tree = $this->object->getTree();
				$parent = $_GET["mepitem_id"] > 0
					? $_GET["mepitem_id"]
					: $tree->getRootId();
				$this->object->insertInTree($mep_item->getId(), $parent);
				ilUtil::sendSuccess($lng->txt("mep_folder_created"), true);
			}
			
			$ilCtrl->redirect($this, "listMedia");
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	/**
	 * Update folder
	 */
	function updateFolder()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->initFolderForm("edit");
		if ($this->form->checkInput())
		{
			$item = new ilMediaPoolItem($_GET["mepitem_id"]);
			$item->setTitle($_POST["title"]);
			$item->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->setParameter($this, "mepitem_id",
				$this->object->getTree()->getParentId($_GET["mepitem_id"]));
			$ilCtrl->redirect($this, "listMedia");
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * Init folder form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initFolderForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// desc
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("saveFolder", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("mep_new_folder"));
		}
		else
		{
			$this->form->addCommandButton("updateFolder", $lng->txt("save"));
			$this->form->addCommandButton("cancelFolderUpdate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("mep_edit_folder"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Cancel save
	 */
	function cancelFolderUpdate()
	{
		global $ilCtrl;
		$ilCtrl->setParameter($this, "mepitem_id",
			$this->object->getTree()->getParentId($_GET["mepitem_id"]));
		$ilCtrl->redirect($this, "listMedia");
	}

	/**
	 * Cancel save
	 */
	function cancelSave()
	{
		global $ilCtrl;
		$ilCtrl->redirect($this, "listMedia");
	}

	////
	//// CONTENT SNIPPETS Handling
	////

	/**
	 * Create new content snippet
	 */
	function createMediaPoolPage()
	{
		global $tpl;
		$this->initMediaPoolPageForm("create");
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Edit media pool page
	 *
	 * @param
	 * @return
	 */
	function editMediaPoolPage()
	{
		global $tpl;
		
		$this->setMediaPoolPageTabs();
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");
		$mep_page_gui = new ilMediaPoolPageGUI($_GET["mepitem_id"], $_GET["old_nr"]);
		$mep_page_gui->getTabs();

		$this->initMediaPoolPageForm("edit");
		$this->getMediaPoolPageValues();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Save media pool page
	 */
	public function saveMediaPoolPage()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initMediaPoolPageForm("create");
		if ($this->form->checkInput())
		{
			// create media pool item
			include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
			$item = new ilMediaPoolItem();
			$item->setTitle($_POST["title"]);
			$item->setType("pg");
			$item->create();
			
			if ($item->getId() > 0)
			{
				// put in tree
				$tree = $this->object->getTree();
				$parent = $_GET["mepitem_id"] > 0
					? $_GET["mepitem_id"]
					: $tree->getRootId();
				$this->object->insertInTree($item->getId(), $parent);
				
				// create page
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
				$page = new ilMediaPoolPage();
				$page->setId($item->getId());
				$page->create();
				
				ilUtil::sendSuccess($lng->txt("mep_page_created"), true);
			}
			$ilCtrl->redirect($this, "listMedia");
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * Update media pool page
	 */
	function updateMediaPoolPage()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->initMediaPoolPageForm("edit");
		if ($this->form->checkInput())
		{
			$item = new ilMediaPoolItem($_GET["mepitem_id"]);
			$item->setTitle($_POST["title"]);			
			$item->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editMediaPoolPage");
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	/**
	 * Init page form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initMediaPoolPageForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$this->form->addItem($ti);
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("saveMediaPoolPage", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("mep_new_content_snippet"));
		}
		else
		{
			$this->form->addCommandButton("updateMediaPoolPage", $lng->txt("save"));
			$this->form->setTitle($lng->txt("mep_edit_content_snippet"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Get current values for media pool page from 
	 */
	public function getMediaPoolPageValues()
	{
		$values = array();
	
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		$values["title"] = ilMediaPoolItem::lookupTitle($_GET["mepitem_id"]);
	
		$this->form->setValuesByArray($values);
	}

	/**
	 * Set media pool page tabs
	 *
	 * @param
	 * @return
	 */
	function setMediaPoolPageTabs()
	{
		global $ilTabs, $ilCtrl, $lng;
	
		$ilTabs->clearTargets();
		//$ilTabs->addTab("mep_pg_prop", $lng->txt("mep_page_properties"),
		//	$ilCtrl->getLinkTarget($this, "editMediaPoolPage"));
		$ilTabs->addTarget("mep_page_properties", $ilCtrl->getLinkTarget($this, "editMediaPoolPage"),
			"editMediaPoolPage", get_class($this));
		$ilCtrl->setParameter($this, "mepitem_id", $this->object->tree->getParentId($_GET["mepitem_id"]));
		$ilTabs->setBackTarget($lng->txt("mep_folder"), $ilCtrl->getLinkTarget($this, "listMedia"));
		$ilCtrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
	}

	////
	//// OTHER Functions...
	////

	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilAccess, $ilTabs, $ilCtrl;
		
		if ($ilAccess->checkAccess('read', '', $this->ref_id) ||
			$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilTabs->addTarget("objs_fold", $this->ctrl->getLinkTarget($this, ""),
				"listMedia", "", "_top");

			$ilCtrl->setParameter($this, "mepitem_id", "");
			$ilTabs->addTarget("mep_all_mobs", $this->ctrl->getLinkTarget($this, "allMedia"),
				"allMedia", "", "_top");
			$ilCtrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
		}

		// info tab
		if ($ilAccess->checkAccess('visible', '', $this->ref_id))
		{
			$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
	//echo "-$force_active-";
			$ilTabs->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass(
				 array("ilobjmediapoolgui", "ilinfoscreengui"), "showSummary"),
				 array("showSummary", "infoScreen"),
				 "", "", $force_active);
		}

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("edit_properties", $this->ctrl->getLinkTarget($this, "edit"),
				"edit", array("", "ilobjmediapoolgui"));
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("clipboard", $this->ctrl->getLinkTarget($this, "openClipboard"),
				"view", "ileditclipboardgui");
		}
	}


	/**
	* goto target media pool
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "frameset";
			$_GET["baseClass"] = "ilMediaPoolPresentationGUI";
			$_GET["ref_id"] = $a_target;
			include("ilias.php");
			exit;
		} else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
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
		global $ilAccess;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();
		
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			//$info->enableNews();
		}

		// no news editing for files, just notifications
//		$info->enableNewsEditing(false);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
//			$news_set = new ilSetting("news");
//			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
//			if ($enable_internal_rss)
//			{
//				$info->setBlockProperty("news", "settings", true);
//				$info->setBlockProperty("news", "public_notifications_option", true);
//			}
		}

		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// forward the command
		$this->ctrl->forwardCommand($info);
		
//		$this->tpl->show();
	}

}
?>