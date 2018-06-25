<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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
* @ilCtrl_Calls ilObjMediaPoolGUI: ilObjMediaObjectGUI, ilObjFolderGUI, ilEditClipboardGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjMediaPoolGUI: ilInfoScreenGUI, ilMediaPoolPageGUI, ilExportGUI, ilFileSystemGUI
* @ilCtrl_Calls ilObjMediaPoolGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI, ilObjectTranslationGUI, ilMediaPoolImportGUI
* @ilCtrl_Calls ilObjMediaPoolGUI: ilMobMultiSrtUploadGUI
*
* @ingroup ModulesMediaPool
*/
class ilObjMediaPoolGUI extends ilObject2GUI
{
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilErrorHandling
	 */
	protected $error;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;


	/**
	 * Constructor
	 */
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
		global $DIC;

		$this->tabs = $DIC->tabs();
		$this->error = $DIC["ilErr"];
		$this->locator = $DIC["ilLocator"];
		$this->help = $DIC["ilHelp"];
	}

	var $output_prepared;

	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		$lng = $this->lng;
		
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
	function executeCommand()
	{
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		$ilAccess = $this->access;
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;
		
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
			$tree = $this->object->getTree();
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
					$this->ctrl->redirectByClass("ilobjfoldergui", "create");
					break;
			}
		}

		switch($next_class)
		{
			case 'ilmediapoolpagegui':
				$this->checkPermission("write");
				$this->prepareOutput();
				$this->addHeaderAction();
				$ilTabs->clearTargets();
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
				$this->setMediaPoolPageTabs();
				$this->tpl->show();
				break;

			case "ilobjmediaobjectgui":
				$this->checkPermission("write");
				//$cmd.="Object";
				if ($cmd == "create" || $cmd == "save" || $cmd == "cancel")
				{
					$ret_obj = $_GET["mepitem_id"];
					$ilObjMediaObjectGUI = new ilObjMediaObjectGUI("", 0, false, false);
					$ilObjMediaObjectGUI->setWidthPreset($this->object->getDefaultWidth());
					$ilObjMediaObjectGUI->setHeightPreset($this->object->getDefaultHeight());
				}
				else
				{
					$ret_obj = $tree->getParentId($_GET["mepitem_id"]);
					$ilObjMediaObjectGUI = new ilObjMediaObjectGUI("", ilMediaPoolItem::lookupForeignId($_GET["mepitem_id"]), false, false);
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

				$ret = $this->ctrl->forwardCommand($ilObjMediaObjectGUI);

				if ($cmd == "save" && $ret != false)
				{
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
				}
				else
				{
						$this->tpl->show();
				}
				break;

			case "ilobjfoldergui":
				$this->checkPermission("write");
				$this->addHeaderAction();
				$folder_gui = new ilObjFolderGUI("", 0, false, false);
				$this->ctrl->setReturn($this, "listMedia");
				$cmd.="Object";
				switch($cmd)
				{
					case "createObject":
						$this->prepareOutput();
						$folder_gui = new ilObjFolderGUI("", 0, false, false);
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
						$folder_gui = new ilObjFolderGUI("", ilMediaPoolItem::lookupForeignId($_GET["mepitem_id"]), false, false);
						$this->ctrl->setParameter($this, "foldereditmode", "1");
						$folder_gui->setFormAction("update", $this->ctrl->getFormActionByClass("ilobjfoldergui"));
						$folder_gui->editObject();
						$this->tpl->show();
						break;

					case "updateObject":
						$folder_gui = new ilObjFolderGUI("", ilMediaPoolItem::lookupForeignId($_GET["mepitem_id"]), false, false);
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
				$this->addHeaderAction();
				$this->ctrl->setReturn($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
				$clip_gui = new ilEditClipboardGUI();
				$clip_gui->setMultipleSelections(true);
				$clip_gui->setInsertButtonTitle($lng->txt("mep_copy_to_mep"));
				$ilTabs->setTabActive("clipboard");
				$this->ctrl->forwardCommand($clip_gui);
				$this->tpl->show();
				break;
				
			case 'ilinfoscreengui':
				$this->prepareOutput();
				$this->addHeaderAction();
				$this->infoScreen();
				$this->tpl->show();
				break;

			case 'ilpermissiongui':
				$this->checkPermission("edit_permission");
				$this->prepareOutput();
				$this->addHeaderAction();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				$this->tpl->show();
				break;
				
			case "ilexportgui":
				$this->checkPermission("write");
				$this->prepareOutput();
				$this->addHeaderAction();
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				include_once("./Services/Object/classes/class.ilObjectTranslation.php");
				$ot = ilObjectTranslation::getInstance($this->object->getId());
				if ($ot->getContentActivated())
				{
					$exp_gui->addFormat("xml_master", "XML (".$lng->txt("mep_master_language_only").")", $this, "export");
					$exp_gui->addFormat("xml_masternomedia", "XML (".$lng->txt("mep_master_language_only_no_media").")", $this, "export");
				}
				$this->ctrl->forwardCommand($exp_gui);
				$this->tpl->show();
				break;

			case "ilfilesystemgui":
				$this->checkPermission("write");
				$this->prepareOutput();
				$this->addHeaderAction();
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "listMedia"));
				$mset = new ilSetting("mobs");
				if (trim($mset->get("upload_dir")) != "")
				{
					include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
					$fs_gui = new ilFileSystemGUI($mset->get("upload_dir"));
					$fs_gui->setPostDirPath(true);
					$fs_gui->setTableId("mepud".$this->object->getId());
					$fs_gui->setAllowFileCreation(false);
					$fs_gui->setAllowDirectoryCreation(false);
					$fs_gui->clearCommands();
					$fs_gui->addCommand($this, "selectUploadDirFiles", $this->lng->txt("mep_sel_upload_dir_files"),
						false, true);
					$this->ctrl->forwardCommand($fs_gui);
				}
				$this->tpl->show();
				break;
				
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjecttranslationgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				//$this->setTabs("settings");
				$ilTabs->activateTab("settings");
				$this->setSettingsSubTabs("obj_multilinguality");
				include_once("./Services/Object/classes/class.ilObjectTranslationGUI.php");
				$transgui = new ilObjectTranslationGUI($this);
				$transgui->setTitleDescrOnlyMode(false);
				$this->ctrl->forwardCommand($transgui);
				$this->tpl->show();
				break;

			case "ilmediapoolimportgui":
				$this->prepareOutput();
				$this->addHeaderAction();
				$ilTabs->activateTab("import");
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolImportGUI.php");
				$gui = new ilMediaPoolImportGUI($this->object);
				$this->ctrl->forwardCommand($gui);
				$this->tpl->show();
				break;

			case "ilmobmultisrtuploadgui":
				$this->prepareOutput();
				$this->addHeaderAction();
				$this->setTabs("content");
				$this->setContentSubTabs("srt_files");
				include_once("./Services/MediaObjects/classes/class.ilMobMultiSrtUploadGUI.php");
				include_once("./Modules/MediaPool/classes/class.ilMepMultiSrt.php");
				$gui = new ilMobMultiSrtUploadGUI(new ilMepMultiSrt($this->object));
				$this->ctrl->forwardCommand($gui);
				$this->tpl->show();
				break;


			default:
				$this->prepareOutput();
				$this->addHeaderAction();
				$cmd = $this->ctrl->getCmd("listMedia");
				$this->$cmd();
				if (!$this->getCreationMode())
				{
					$this->tpl->show();
				}
				break;
		}
	}

	/**
	 * obsolete?
	 */
	function createMediaObject()
	{
		$this->ctrl->redirectByClass("ilobjmediaobjectgui", "create");
	}

	protected function initCreationForms($a_new_type)
	{
		$forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type));

		return $forms;
	}

	/**
	 * save object
	 */
	function afterSave(ilObject $newObj)
	{
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
		ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&ref_id=".$newObj->getRefId()."&cmd=listMedia");
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		// default width
		$ni = new ilNumberInputGUI($this->lng->txt("mep_default_width"), "default_width");
		$ni->setMinValue(0);
		$ni->setSuffix("px");
		$ni->setMaxLength(5);
		$ni->setSize(5);
		$a_form->addItem($ni);

		// default height
		$ni = new ilNumberInputGUI($this->lng->txt("mep_default_height"), "default_height");
		$ni->setSuffix("px");
		$ni->setMinValue(0);
		$ni->setMaxLength(5);
		$ni->setSize(5);
		$ni->setInfo($this->lng->txt("mep_default_width_height_info"));
		$a_form->addItem($ni);
	}

	/**
	 * Edit
	 *
	 * @param
	 * @return
	 */
	function edit()
	{
		$this->setSettingsSubTabs("settings");
		parent::edit();
	}


	protected function getEditFormCustomValues(array &$a_values)
	{
		if ($this->object->getDefaultWidth() > 0)
		{
			$a_values["default_width"] = $this->object->getDefaultWidth();
		}
		if ($this->object->getDefaultHeight() > 0)
		{
			$a_values["default_height"] = $this->object->getDefaultHeight();
		}
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->object->setDefaultWidth($a_form->getInput("default_width"));
		$this->object->setDefaultHeight($a_form->getInput("default_height"));
	}

	/**
	* list media objects
	*/
	function listMedia()
	{
		$ilAccess = $this->access;
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$ilToolbar = $this->toolbar;
		$lng = $this->lng;

		$ilCtrl->setParameter($this, "mep_mode", "listMedia");

		$this->checkPermission("read");

		$ilTabs->setTabActive("content");
		$this->setContentSubTabs("content");
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilToolbar->addButton($lng->txt("mep_create_mob"),
				$ilCtrl->getLinkTarget($this, "createMediaObject"));
			
			$mset = new ilSetting("mobs");
			if ($mset->get("mep_activate_pages"))
			{
				$ilToolbar->addButton($lng->txt("mep_create_content_snippet"),
					$ilCtrl->getLinkTarget($this, "createMediaPoolPage"));
			}
	
			$ilToolbar->addButton($lng->txt("mep_create_folder"),
				$ilCtrl->getLinkTarget($this, "createFolderForm"));
						
			if (trim($mset->get("upload_dir")) != "" && ilMainMenuGUI::_checkAdministrationPermission())
			{
				$ilToolbar->addButton($lng->txt("mep_create_from_upload_dir"),
					$ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));
			}		
		}

		// tree
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolExplorerGUI.php");
		$exp = new ilMediaPoolExplorerGUI($this, "listMedia", $this->object);
		if (!$exp->handleCommand())
		{
			$this->tpl->setLeftNavContent($exp->getHTML());
		}
		else
		{
			return;
		}

		include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
		$mep_table_gui = new ilMediaPoolTableGUI($this, "listMedia", $this->object, "mepitem_id");
		$tpl->setContent($mep_table_gui->getHTML());
	}

	/**
	* list all objects
	*/
	function allMedia()
	{
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$ilUser = $this->user;

		$ilCtrl->setParameter($this, "mep_mode", "allMedia");

		$this->checkPermission("read");
		$ilTabs->setTabActive("content");
		$this->setContentSubTabs("mep_all_mobs");
		
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
		$mep_table_gui = new ilMediaPoolTableGUI($this, "allMedia", $this->object,
			"mepitem_id", ilMediaPoolTableGUI::IL_MEP_EDIT, true);
			
			
		if(isset($_GET['force_filter']) and $_GET['force_filter'])
		{
			$_POST['title'] = ilMediaPoolItem::lookupTitle((int) $_GET['force_filter']);
			
			include_once("./Services/Table/classes/class.ilTablePropertiesStorage.php");
			$tprop = new ilTablePropertiesStorage();
			$tprop->storeProperty(
				$mep_table_gui->getId(), 
				$ilUser->getId(), 
				'filter', 
				1
			);
			$mep_table_gui->resetFilter();
			$mep_table_gui->resetOffset();
			$mep_table_gui->writeFilterToSession();

			// Read again
			$mep_table_gui = new ilMediaPoolTableGUI($this, "allMedia", $this->object,
			"mepitem_id", ilMediaPoolTableGUI::IL_MEP_EDIT, true);
		}

		$tpl->setContent($mep_table_gui->getHTML());
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
	* Reset filter
	*/
	function resetFilter()
	{
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolTableGUI.php");
		$mtab = new ilMediaPoolTableGUI($this, "allMedia", $this->object,
			"mepitem_id", ilMediaPoolTableGUI::IL_MEP_EDIT, true);
		$mtab->resetFilter();
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
		$par_id = $this->object->getPoolTree()->getParentId($_GET["mepitem_id"]);
		if ($par_id != $this->object->getPoolTree()->getRootId())
		{
			return $par_id;
		}
		else
		{
			return "";
		}
	}
	
	/**
	 * show media object
	 */
	protected function showMedia()
	{
		$this->checkPermission("read");

		$item = new ilMediaPoolItem((int) $_GET["mepitem_id"]);
		$mob_id = $item->getForeignId();

		$this->tpl = new ilTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
		include_once("Services/Style/Content/classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));


		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
		ilObjMediaObjectGUI::includePresentationJS($this->tpl);
		$media_obj = new ilObjMediaObject((int) $mob_id);


		$this->tpl->setVariable("TITLE", " - ".$media_obj->getTitle());

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

		$wb_path = ilUtil::getWebspaceDir("output")."/";

		$mode = ($_GET["cmd"] != "showPreview")
			? "fullscreen"
			: "media";
		$enlarge_path = ilUtil::getImagePath("enlarge.svg", false, "output");
		$fullscreen_link =
			$this->ctrl->getLinkTarget($this, "showFullscreen", "", false, false);
		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => $fullscreen_link,
			'ref_id' => $_GET["ref_id"], 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);
		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);
	}
	
	/**
	 * Show page
	 *
	 * @param
	 * @return
	 */
	function showPage()
	{
		$tpl = $this->tpl;
		
		$tpl = new ilTemplate("tpl.main.html", true, true);

		include_once("./Services/Container/classes/class.ilContainerPage.php");
		include_once("./Services/Container/classes/class.ilContainerPageGUI.php");

		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();

		// get page object
		//include_once("./Services/Object/classes/class.ilObjectTranslation.php");
		//$ot = ilObjectTranslation::getInstance($this->object->getId());
		//$lang = $ot->getEffectiveContentLang($ilUser->getCurrentLanguage(), "cont");
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");
		$page_gui = new ilMediaPoolPageGUI((int) $_GET["mepitem_id"]);
		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
		//$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
		//	$this->object->getStyleSheetId(), $this->object->getType()));

		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		$ret = $page_gui->showPage(true);

		$tpl->setBodyClass("ilMediaPoolPagePreviewBody");
		$tpl->setVariable("CONTENT", $ret);
		//$ret = "<div style='background-color: white; padding:5px; margin-bottom: 30px;'>".$ret."</div>";


		$tpl->show();
		exit;
	}
	

	/**
	 * Show content snippet
	 */
	function showPreview()
	{
		$this->checkPermission("read");

		$item = new ilMediaPoolItem((int) $_GET["mepitem_id"]);

		switch ($item->getType())
		{
			case "mob":
				$this->showMedia();
				break;

			case "pg":
				$this->showPage();
				break;
		}
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
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilErr = $this->error;

		$this->checkPermission("write");

		if(!isset($_POST["id"]))
		{
			$ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
		}
		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("info_remove_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelRemove");
		$cgui->setConfirm($this->lng->txt("confirm"), "remove");
			
		foreach($_POST["id"] as $obj_id)
		{
			$type = ilMediaPoolItem::lookupType($obj_id);
			$title = ilMediaPoolItem::lookupTitle($obj_id);
			
			// check whether page can be removed
			$add = "";
			if ($type == "pg")
			{
				include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
				$usages = ilPageContentUsage::getUsages("incl", $obj_id, false);
				if (count($usages) > 0)
				{
					ilUtil::sendFailure(sprintf($lng->txt("mep_content_snippet_in_use"), $title), true);
					$ilCtrl->redirect($this, "listMedia");
				}
				else
				{
					// check whether the snippet is used in older versions of pages
					$usages = ilPageContentUsage::getUsages("incl", $obj_id, true);
					if (count($usages) > 0)
					{
						$add = "<div class='small'>".$lng->txt("mep_content_snippet_used_in_older_versions")."</div>";
					}
				}
			}
			
			$caption = ilUtil::getImageTagByType($type, $this->tpl->tplPath).
				" ".$title.$add;
			
			$cgui->addItem("id[]", $obj_id, $caption);
		}

		$this->tpl->setContent($cgui->getHTML());
	}
	
	/**
	* paste from clipboard
	*/
	function openClipboard()
	{
		$ilCtrl = $this->ctrl;
		$ilAccess = $this->access;

		$this->checkPermission("write");

		$ilCtrl->setParameterByClass("ileditclipboardgui", "returnCommand",
			rawurlencode($ilCtrl->getLinkTarget($this,
			"insertFromClipboard", "", false, false)));
		$ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
	}
	

	/**
	* insert media object from clipboard
	*/
	function insertFromClipboard()
	{
		$ilAccess = $this->access;

		$this->checkPermission("write");

		include_once("./Services/Clipboard/classes/class.ilEditClipboardGUI.php");
		$ids = ilEditClipboardGUI::_getSelectedIDs();
		$not_inserted = array();
		if (is_array($ids))
		{
			foreach ($ids as $id2)
			{
				$id = explode(":", $id2);
				$type = $id[0];
				$id = $id[1];
				
				if ($type == "mob")		// media object
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
				if ($type == "incl")		// content snippet
				{
					include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
					include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
					if (ilObjMEdiaPool::isItemIdInTree($this->object->getId(), $id))
					{
						$not_inserted[] = ilMediaPoolPage::lookupTitle($id)." [".
							$id."]";
					}
					else
					{
						$original = new ilMediaPoolPage($id);
						
						// copy the page into the pool
						$item = new ilMediaPoolItem();
						$item->setType("pg");
						$item->setTitle(ilMediaPoolItem::lookupTitle($id));
						$item->create();
						if ($item->getId() > 0)
						{
							$this->object->insertInTree($item->getId(), $_GET["mepitem_id"]);
							
							// create page
							include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
							$page = new ilMediaPoolPage();
							$page->setId($item->getId());
							$page->setParentId($this->object->getId());
							$page->create();
							
							// copy content
							$original->copy($page->getId(), $page->getParentType(), $page->getParentId(), true);

						}
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
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}

	/**
	* confirm deletion of
	*/
	function remove()
	{
		$ilAccess = $this->access;

		$this->checkPermission("write");

		foreach($_POST["id"] as $obj_id)
		{
			$this->object->deleteChild($obj_id);
		}

		ilUtil::sendSuccess($this->lng->txt("cont_obj_removed"),true);
		$this->ctrl->redirect($this, $_GET["mep_mode"] ? $_GET["mep_mode"] : "listMedia");
	}


	/**
	* copy media objects to clipboard
	*/
	function copyToClipboard()
	{
		$ilUser = $this->user;
		$ilAccess = $this->access;

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
			if ($type == "pg")
			{
				$ilUser->addObjectToClipboard($obj_id, "incl", "");
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
		$ilLocator = $this->locator;
		$ilAccess = $this->access;
		
		if (!$this->getCreationMode() && $this->ctrl->getCmd() != "explorer")
		{
			$tree = $this->object->getTree();
			$obj_id = ($_GET["mepitem_id"] == "")
				? $tree->getRootId()
				: $_GET["mepitem_id"];
			$path = $tree->getPathFull($obj_id);
			foreach($path as $node)
			{
				if ($node["child"] == $tree->getRootId())
				{
					$this->ctrl->setParameter($this, "mepitem_id", "");
					if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
					{
						$link = $this->ctrl->getLinkTarget($this, "listMedia");
					}
					else if ($ilAccess->checkAccess("visible", "", $this->object->getRefId()))
					{
						$link = $this->ctrl->getLinkTarget($this, "infoScreen");
					}
					$title = $this->object->getTitle();
					$this->ctrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
					$ilLocator->addItem($title, $link, "", $_GET["ref_id"]);
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
		$ilAccess = $this->access;
		$tpl = $this->tpl;

		$this->checkPermission("write");

		$this->initFolderForm("create");
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Edit folder
	 *
	 * @param
	 * @return
	 */
	function editFolder()
	{
		$tpl = $this->tpl;

		$this->checkPermission("write");

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
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		$this->checkPermission("write");

		$this->initFolderForm("create");
		if ($this->form->checkInput())
		{
			if ($this->object->createFolder($_POST["title"], (int) $_GET["mepitem_id"]))
			{
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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;

		$this->checkPermission("write");

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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
	
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
		$ilCtrl = $this->ctrl;
		$ilCtrl->setParameter($this, "mepitem_id",
			$this->object->getTree()->getParentId($_GET["mepitem_id"]));
		$ilCtrl->redirect($this, "listMedia");
	}

	/**
	 * Cancel save
	 */
	function cancelSave()
	{
		$ilCtrl = $this->ctrl;
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
		$tpl = $this->tpl;

		$this->checkPermission("write");

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
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;

		$this->checkPermission("write");

		$ilTabs->clearTargets();
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");
		$mep_page_gui = new ilMediaPoolPageGUI($_GET["mepitem_id"], $_GET["old_nr"]);
		$mep_page_gui->getTabs();

		$this->setMediaPoolPageTabs();

		$this->initMediaPoolPageForm("edit");
		$this->getMediaPoolPageValues();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Save media pool page
	 */
	public function saveMediaPoolPage()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		$this->checkPermission("write");

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
				$page->setParentId($this->object->getId());
				$page->create();
				
				$ilCtrl->setParameterByClass("ilmediapoolpagegui", "mepitem_id", $item->getId());
				$ilCtrl->redirectByClass("ilmediapoolpagegui", "edit");

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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;

		$this->checkPermission("write");

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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setRequired(true);
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
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
	
//		$ilTabs->clearTargets();
		//$ilTabs->addTab("mep_pg_prop", $lng->txt("mep_page_properties"),
		//	$ilCtrl->getLinkTarget($this, "editMediaPoolPage"));
		$ilTabs->addTarget("cont_usage", $ilCtrl->getLinkTarget($this, "showMediaPoolPageUsages"),
			array("showMediaPoolPageUsages", "showAllMediaPoolPageUsages"), get_class($this));
		$ilTabs->addTarget("settings", $ilCtrl->getLinkTarget($this, "editMediaPoolPage"),
			"editMediaPoolPage", get_class($this));
		$ilCtrl->setParameter($this, "mepitem_id", $this->object->getPoolTree()->getParentId($_GET["mepitem_id"]));
		$ilTabs->setBackTarget($lng->txt("mep_folder"), $ilCtrl->getLinkTarget($this, "listMedia"));
		$ilCtrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
	}

	/**
	 * List usages of the contnet snippet
	 */
	function showAllMediaPoolPageUsages()
	{
		$this->showMediaPoolPageUsages(true);
	}

	
	/**
	 * List usages of the contnet snippet
	 */
	function showMediaPoolPageUsages($a_all = false)
	{
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$tpl = $this->tpl;

		$this->checkPermission("write");

		$ilTabs->clearTargets();
		
		$ilTabs->addSubTab("current_usages", $lng->txt("cont_current_usages"),
			$ilCtrl->getLinkTarget($this, "showMediaPoolPageUsages"));
		
		$ilTabs->addSubTab("all_usages", $lng->txt("cont_all_usages"),
			$ilCtrl->getLinkTarget($this, "showAllMediaPoolPageUsages"));
		
		if ($a_all)
		{
			$ilTabs->activateSubTab("all_usages");
			$cmd = "showAllMediaPoolPageUsages";
		}
		else
		{
			$ilTabs->activateSubTab("current_usages");
			$cmd = "showMediaPoolPageUsages";
		}

		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");
		$mep_page_gui = new ilMediaPoolPageGUI($_GET["mepitem_id"], $_GET["old_nr"]);
		$mep_page_gui->getTabs();

		$this->setMediaPoolPageTabs();
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
		$page = new ilMediaPoolPage((int) $_GET["mepitem_id"]);

		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageUsagesTableGUI.php");
		$table = new ilMediaPoolPageUsagesTableGUI($this, $cmd, $page, $a_all);

		$tpl->setContent($table->getHTML());
		
	}
	
	
	////
	//// OTHER Functions...
	////

	/**
	 * Set sub tabs for content tab
	 *
	 * @param string
	 */
	function setContentSubTabs($a_active)
	{
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;

		$ilTabs->addSubTab("content", $this->lng->txt("objs_fold"), $this->ctrl->getLinkTarget($this, ""));

		$ilCtrl->setParameter($this, "mepitem_id", "");
		$ilTabs->addSubTab("mep_all_mobs", $this->lng->txt("mep_all_mobs"), $this->ctrl->getLinkTarget($this, "allMedia"));
		$ilCtrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);

		$ilTabs->addSubtab("srt_files",
			$this->lng->txt("mep_media_subtitles"),
			$ilCtrl->getLinkTargetByClass("ilmobmultisrtuploadgui", ""));

		$ilTabs->activateSubTab($a_active);
	}


	/**
	* Set tabs
	*/
	function setTabs()
	{
		$ilAccess = $this->access;
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$ilHelp = $this->help;

		$ilHelp->setScreenIdComponent("mep");
		
		if ($ilAccess->checkAccess('read', '', $this->ref_id) ||
			$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilTabs->addTab("content", $this->lng->txt("mep_content"), $this->ctrl->getLinkTarget($this, ""));
			//$ilTabs->addTarget("objs_fold", $this->ctrl->getLinkTarget($this, ""),
			//	"listMedia", "", "_top");

			//$ilCtrl->setParameter($this, "mepitem_id", "");
			//$ilTabs->addTarget("mep_all_mobs", $this->ctrl->getLinkTarget($this, "allMedia"),
			//	"allMedia", "", "_top");
			//$ilCtrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
		}

		// info tab
		if ($ilAccess->checkAccess('visible', '', $this->ref_id) ||
			$ilAccess->checkAccess('read', '', $this->ref_id) ||
			$ilAccess->checkAccess('write', '', $this->ref_id))
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
			$ilTabs->addTarget("settings", $this->ctrl->getLinkTarget($this, "edit"),
				"edit", array("", "ilobjmediapoolgui"));
		}
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("clipboard", $this->ctrl->getLinkTarget($this, "openClipboard"),
				"view", "ileditclipboardgui");
		}

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("export", $this->ctrl->getLinkTargetByClass("ilexportgui", ""),
				"", "ilexportgui");

			$ilTabs->addTarget("import", $this->ctrl->getLinkTargetByClass("ilmediapoolimportgui", ""),
				"", "ilmediapoolimportgui");
		}
		
		if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

	}

	/**
	 * Set setting sub tabs
	 *
	 * @param
	 * @return
	 */
	function setSettingsSubTabs($a_active)
	{
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		$ilAccess = $this->access;

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addSubTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));

			$mset = new ilSetting("mobs");
			if ($mset->get("mep_activate_pages"))
			{
				$ilTabs->addSubTabTarget("obj_multilinguality",
				$this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", ""));
			}
		}

		$ilTabs->setSubTabActive($a_active);
	}


	/**
	* goto target media pool
	*/
	public static function _goto($a_target)
	{
		global $DIC;

		$ilAccess = $DIC->access();
		$ilErr = $DIC["ilErr"];
		$lng = $DIC->language();
		
		$targets = explode('_',$a_target);
		if(count((array) $targets) > 1)
		{
			$ref_id = $targets[0];
			$subitem_id = $targets[1];
		}
		else
		{
			$ref_id = $targets[0];
		}

		if ($ilAccess->checkAccess("read", "", $ref_id))
		{
			$_GET["baseClass"] = "ilMediaPoolPresentationGUI";
			$_GET["ref_id"] = $ref_id;
			$_GET['mepitem_id'] = $subitem_id;
			include("ilias.php");
			exit;
		} else if ($ilAccess->checkAccess("visible", "", $ref_id))
		{
			$_GET["baseClass"] = "ilMediaPoolPresentationGUI";
			$_GET["ref_id"] = $ref_id;
			$_GET["cmd"] = "infoScreen";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
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
		$ilAccess = $this->access;
		$ilErr = $this->error;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id) &&
			!$ilAccess->checkAccess("read", "", $this->ref_id) &&
			!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
		}
		
		if ($this->ctrl->getCmd() == "infoScreen")
		{
			$this->ctrl->setCmd("showSummary");
			$this->ctrl->setCmdClass("ilinfoscreengui");
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();


		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// forward the command
		$this->ctrl->forwardCommand($info);
	}


	////
	//// Upload directory handling
	////

	/**
	 * Select files from upload directory
	 */
	function selectUploadDirFiles($a_files = null)
	{
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilToolbar = $this->toolbar;
		
		if(!$a_files)
		{
			$a_files = $_POST["file"];
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listMedia"));

		$this->checkPermission("write");

		if (ilMainMenuGUI::_checkAdministrationPermission())
		{

			// action type
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$options = array(
				"rename" => $lng->txt("mep_up_dir_move"),
				"copy" => $lng->txt("mep_up_dir_copy"),
				);
			$si = new ilSelectInputGUI("", "action");
			$si->setOptions($options);
			$ilToolbar->addInputItem($si);
			$ilToolbar->setCloseFormTag(false);
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
			$ilToolbar->setFormName("mep_up_form");

			include_once("./Modules/MediaPool/classes/class.ilUploadDirFilesTableGUI.php");
			$tab = new ilUploadDirFilesTableGUI($this, "selectUploadDirFiles",
				$a_files);
			$tab->setFormName("mep_up_form");
			$tpl->setContent($tab->getHTML());
		}
	}

	/**
	 * Create media object from upload directory
	 */
	function createMediaFromUploadDir()
	{
		$this->checkPermission("write");

		$mset = new ilSetting("mobs");
		$upload_dir = trim($mset->get("upload_dir"));

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		if (is_array($_POST["file"]) && ilMainMenuGUI::_checkAdministrationPermission())
		{
			foreach ($_POST["file"] as $f)
			{
				$f  = str_replace("..", "", $f);
				$fullpath = $upload_dir."/".$f;
				$mob = new ilObjMediaObject();
					$mob->setTitle(basename($fullpath));
				$mob->setDescription("");
				$mob->create();

				// determine and create mob directory, move uploaded file to directory
				//$mob_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob->getId();
				$mob->createDirectory();
				$mob_dir = ilObjMediaObject::_getDirectory($mob->getId());

				$media_item = new ilMediaItem();
				$mob->addMediaItem($media_item);
				$media_item->setPurpose("Standard");

				$file = $mob_dir."/".basename($fullpath);

				// virus handling
				$vir = ilUtil::virusHandling($fullpath, basename($fullpath));
				if (!$vir[0])
				{
					ilUtil::sendFailure($this->lng->txt("file_is_infected")."<br />".$vir[1], true);
					ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&cmd=listMedia&ref_id=".
						$_GET["ref_id"]."&mepitem_id=".$_GET["mepitem_id"]);
				}

				switch ($_POST["action"])
				{
					case "rename":
						rename($fullpath, $file);
						break;

					case "copy":
						copy($fullpath, $file);
						break;
				}

				// get mime type
				$format = ilObjMediaObject::getMimeType($file);
				$location = basename($fullpath);

				// set real meta and object data
				$media_item->setFormat($format);
				$media_item->setLocation($location);
				$media_item->setLocationType("LocalFile");

				$mob->setDescription($format);

				// determine width and height of known image types
				$wh = ilObjMediaObject::_determineWidthHeight($format,
					"File", $mob_dir."/".$location, $media_item->getLocation(),
					true, true, "", "");
				$media_item->setWidth($wh["width"]);
				$media_item->setHeight($wh["height"]);
				if ($wh["info"] != "")
				{
	//				ilUtil::sendInfo($wh["info"], true);
				}

				$media_item->setHAlign("Left");
				ilUtil::renameExecutables($mob_dir);
				$mob->update();


				// put it into current folder
				$mep_item = new ilMediaPoolItem();
				$mep_item->setTitle($mob->getTitle());
				$mep_item->setType("mob");
				$mep_item->setForeignId($mob->getId());
				$mep_item->create();

				$tree = $this->object->getTree();
				$parent = ($_GET["mepitem_id"] == "")
					? $tree->getRootId()
					: $_GET["mepitem_id"];
				$tree->insertNode($mep_item->getId(), $parent);
			}
		}
		ilUtil::redirect("ilias.php?baseClass=ilMediaPoolPresentationGUI&cmd=listMedia&ref_id=".
			$_GET["ref_id"]."&mepitem_id=".$_GET["mepitem_id"]);

	}

	/**
	 * Get preview modal html
	 */
	static function getPreviewModalHTML($a_mpool_ref_id, $a_tpl)
	{
		global $DIC;

		$tpl = $DIC["tpl"];
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();

		require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
		ilObjMediaObjectGUI::includePresentationJS($a_tpl);

		$tpl->addJavaScript("./Modules/MediaPool/js/ilMediaPool.js");

		$ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", "");
		$ilCtrl->setParameterByClass("ilobjmediapoolgui", "ref_id", $a_mpool_ref_id);
		$tpl->addOnloadCode("il.MediaPool.setPreviewUrl('".$ilCtrl->getLinkTargetByClass(array("ilmediapoolpresentationgui", "ilobjmediapoolgui"), "showPreview", "", false, false)."');");
		$ilCtrl->setParameterByClass("ilobjmediapoolgui", "mepitem_id", $_GET["mepitem_id"]);
		$ilCtrl->setParameterByClass("ilobjmediapoolgui", "ref_id", $_GET["red_id"]);

		include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
		$modal = ilModalGUI::getInstance();
		$modal->setHeading($lng->txt("preview"));
		$modal->setId("ilMepPreview");
		$modal->setType(ilModalGUI::TYPE_LARGE);
		$modal->setBody("<iframe id='ilMepPreviewContent'></iframe>");

		return $modal->getHTML();
	}

	/**
	 * export content object
	 */
	function export()
	{
		$ot = ilObjectTranslation::getInstance($this->object->getId());
		$opt = "";
		if ($ot->getContentActivated())
		{
			$format = explode("_", $_POST["format"]);
			$opt = ilUtil::stripSlashes($format[1]);
		}

		$this->object->exportXML($opt);
	}

}
?>