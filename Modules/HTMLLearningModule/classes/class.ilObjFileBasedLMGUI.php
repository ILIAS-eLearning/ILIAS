<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* User Interface class for file based learning modules (HTML)
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilShopPurchaseGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilLicenseGUI, ilExportGUI
* @ingroup ModulesHTMLLearningModule
*/

require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
require_once("./Services/Table/classes/class.ilTableGUI.php");
require_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");

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

		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id"));

		$this->type = "htlm";
		$lng->loadLanguageModule("content");

		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
		//$this->actions = $this->objDefinition->getActions("mep");
		$this->output_prepared = $a_prepare_output;

	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilUser, $ilLocator, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
	
		if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
			$this->getCreationMode() == true)
		{
			$this->prepareOutput();
		}
		else
		{
			if (!in_array($cmd, array("", "framset")) || $next_class != "")
			{
				$this->getTemplate();
				$this->setLocator();
				$this->setTabs();				
			}
		}

			
		if(!$this->getCreationMode())
		{
			if(IS_PAYMENT_ENABLED)
			{
				include_once 'Services/Payment/classes/class.ilPaymentObject.php';
				if(ilPaymentObject::_requiresPurchaseToAccess($_GET['ref_id'], $type = (isset($_GET['purchasetype']) ? $_GET['purchasetype'] : NULL) ))
				{
					$this->tpl->getStandardTemplate();

					include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
					$pp = new ilShopPurchaseGUI((int)$_GET['ref_id']);
					$ret = $this->ctrl->forwardCommand($pp);
					return true;
				}
			}
		}

		switch($next_class)
		{
			case 'ilmdeditorgui':
				$this->checkPermission("write");
				$ilTabs->activateTab('id_meta_data');
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			case "ilfilesystemgui":
				$this->checkPermission("write");
				$ilTabs->activateTab('id_list_files');
				$fs_gui = new ilFileSystemGUI($this->object->getDataDirectory());
				$fs_gui->activateLabels(true, $this->lng->txt("cont_purpose"));
				$fs_gui->setUseUploadDirectory(true);
				$fs_gui->setTableId("htlmfs".$this->object->getId());			
				if ($this->object->getStartFile() != "")
				{
					$fs_gui->labelFile($this->object->getStartFile(),
						$this->lng->txt("cont_startfile"));
				}							
				$fs_gui->addCommand($this, "setStartFile", $this->lng->txt("cont_set_start_file"));
				
				$this->ctrl->forwardCommand($fs_gui);
											
				// try to set start file automatically
				require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
				if (!ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId()))
				{					
					$do_update = false;
										
					$pcommand = $fs_gui->getLastPerformedCommand();							
					if (is_array($pcommand))
					{
						$valid = array("index.htm", "index.html", "start.htm", "start.html");						
						if($pcommand["cmd"] == "create_file")
						{
							$file = strtolower(basename($pcommand["name"]));
							if(in_array($file, $valid))
							{
								$this->object->setStartFile($pcommand["name"]);
								$do_update = $pcommand["name"];
							}
						}
						else if($pcommand["cmd"] == "unzip_file")
						{
							$zip_file = strtolower(basename($pcommand["name"]));
							$suffix = strrpos($zip_file, ".");
							if($suffix)
							{
								$zip_file = substr($zip_file, 0, $suffix);
							}							
							foreach($pcommand["added"] as $file)
							{
								$chk_file = null;
								if(stristr($file, ".htm"))
								{
									$chk_file = strtolower(basename($file));
									$suffix = strrpos($chk_file, ".");
									if($suffix)
									{
										$chk_file = substr($chk_file, 0, $suffix);
									}								
								}
								if(in_array(basename($file), $valid) ||
									($zip_file && $chk_file && $chk_file == $zip_file))
								{
									$this->object->setStartFile($file);
									$do_update = $file;
									break;
								}
							}
						}				
					}
					
					if($do_update)
					{
						ilUtil::sendInfo(sprintf($this->lng->txt("cont_start_file_set_to"), $do_update), true);
						
						$this->object->update();
						$this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
					}
				}
				break;

			case "ilinfoscreengui":
				$ret =& $this->outputInfoScreen();
				break;

			case "illearningprogressgui":
				$ilTabs->activateTab('id_learning_progress');
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				break;
				
			case 'ilpermissiongui':
				$ilTabs->activateTab('id_permissions');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'illicensegui':
				$ilTabs->activateTab('id_license');
				include_once("./Services/License/classes/class.ilLicenseGUI.php");
				$license_gui =& new ilLicenseGUI($this);
				$ret =& $this->ctrl->forwardCommand($license_gui);
				break;

			case "ilexportgui":
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$exp_gui->addFormat("html", "", $this, "exportHTML");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
				break;

			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
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
		
		$this->addHeaderAction();
	}

	protected function initCreationForms($a_new_type)
	{
		$forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type));

		return $forms;
	}

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	final function cancelCreationObject($in_rep = false)
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass("ilrepositorygui", "frameset");
	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function properties()
	{
		global $rbacsystem, $tree, $tpl, $ilTabs;
		
		$ilTabs->activateTab("id_settings");

		$this->initSettingsForm();
		$this->getSettingsFormValues();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Init settings form.
	 */
	public function initSettingsForm()
	{
		global $lng, $ilCtrl, $ilAccess;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setSize(min(40, ilObject::TITLE_LENGTH));
		$ti->setMaxLength(ilObject::TITLE_LENGTH);
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$this->form->addItem($ta);

		// online
		$cb = new ilCheckboxInputGUI($lng->txt("cont_online"), "cobj_online");
		$cb->setOptionTitle($lng->txt(""));
		$cb->setValue("y");
		$this->form->addItem($cb);

		// startfile
		require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
		$startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

		$ne = new ilNonEditableValueGUI($lng->txt("cont_startfile"), "");
		if ($startfile != "")
		{
			$ne->setValue(basename($startfile));
		}
		else
		{
			$ne->setValue(basename($this->lng->txt("no_start_file")));
		}
		$this->form->addItem($ne);
		
		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if (ilLicenseAccess::_isEnabled())
		{
			$lic = new ilCheckboxInputGUI($lng->txt("cont_license"), "lic");
			$lic->setInfo($lng->txt("cont_license_info"));
			$this->form->addItem($lic);
			
			if(!$ilAccess->checkAccess('edit_permission', '', $this->ref_id))
			{
				$lic->setDisabled(true);
			}
		}		
		
		$bib = new ilCheckboxInputGUI($lng->txt("cont_biblio"), "bib");
		$bib->setInfo($lng->txt("cont_biblio_info"));
		$this->form->addItem($bib);

		$this->form->addCommandButton("saveProperties", $lng->txt("save"));
		$this->form->addCommandButton("toFilesystem", $lng->txt("cont_set_start_file"));

		$this->form->setTitle($lng->txt("cont_lm_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this, "saveProperties"));
	}

	/**
	 * Get current values for settings from
	 */
	public function getSettingsFormValues()
	{
		require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
		$startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

		$values = array();
		$values["cobj_online"] = $this->object->getOnline();
		if ($startfile != "")
		{
			$startfile = basename($startfile);
		}
		else
		{
			$startfile = $this->lng->txt("no_start_file");
		}

		$values["cobj_online"] = $this->object->getOnline();
		$values["startfile"] = $startfile;
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["lic"] = $this->object->getShowLicense();
		$values["bib"] = $this->object->getShowBibliographicalData();

		$this->form->setValuesByArray($values);
	}

	/**
	 * Set start file
	 *
	 * @param
	 * @return
	 */
	function toFilesystem()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilfilesystemgui", "listFiles");
	}

	/**
	 * Save properties form
	 */
	public function saveProperties()
	{
		global $tpl, $ilAccess, $ilTabs;
		
		$this->initSettingsForm("");
		if ($this->form->checkInput())
		{			
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
			$this->object->setShowBibliographicalData($this->form->getInput("bib"));		
			
			$lic = $this->form->getItemByPostVar("lic");
			if($lic && !$lic->getDisabled())
			{
				$this->object->setShowLicense($this->form->getInput("lic"));
			}						
			
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "properties");
		}

		$ilTabs->activateTab("id_settings");
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
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
		if(!$newObj->getStartFile())
		{
			// try to set start file automatically
			$files = array();		
			include_once "Services/Utilities/classes/class.ilFileUtils.php";
			ilFileUtils::recursive_dirscan($newObj->getDataDirectory(), $files);
			if(is_array($files["file"]))
			{
				$zip_file = null;
				if(stristr($newObj->getTitle(), ".zip"))
				{
					$zip_file = strtolower($newObj->getTitle());
					$suffix = strrpos($zip_file, ".");
					if($suffix)
					{
						$zip_file = substr($zip_file, 0, $suffix);
					}	
				}								
				$valid = array("index.htm", "index.html", "start.htm", "start.html");		
				foreach($files["file"] as $idx => $file)
				{
					$chk_file = null;
					if(stristr($file, ".htm"))
					{
						$chk_file = strtolower($file);
						$suffix = strrpos($chk_file, ".");					
						if($suffix)
						{
							$chk_file = substr($chk_file, 0, $suffix);
						}	
					}
					if(in_array($file, $valid) ||
						($chk_file && $zip_file && $chk_file == $zip_file))
					{						
						$newObj->setStartFile(str_replace($newObj->getDataDirectory()."/", "", $files["path"][$idx]).$file);
						$newObj->update();
						break;
					}
				}
			}
		}
		
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilHTLMEditorGUI&ref_id=".$newObj->getRefId());
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
		global $ilTabs;

		$ilTabs->activateTab("id_bib_data");

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
		global $ilTabs;

		$ilTabs->activateTab("id_bib_data");
		
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
		global $ilTabs;

		$ilTabs->activateTab("id_bib_data");
		
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

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, max(0, $bibItemIndex - 1));
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
		global $ilTabs;
	
		$ilTabs->activateTab("id_bib_data");
	
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
	* Frameset -> Output list of files
	*/
	function frameset()
	{
		global $ilCtrl;

		$ilCtrl->setCmdClass("ilfilesystemgui");
		$ilCtrl->setCmd("listFiles");
		return $this->executeCommand();
	}

	/**
	* output main header (title and locator)
	*/
	function getTemplate()
	{
		global $lng;

		$this->tpl->getStandardTemplate();
	}

	function showLearningModule()
	{
		global $ilUser;
		
		// Note license usage
		include_once "Services/License/classes/class.ilLicense.php";
		ilLicense::_noteAccess($this->object->getId(), $this->object->getType(),
			$this->object->getRefId());
		
		// #9483
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{	
			include_once "Services/Tracking/classes/class.ilLearningProgress.php";
			ilLearningProgress::_tracProgress($ilUser->getId(), $this->object->getId(), 
				$this->object->getRefId(), "htlm");	
		}

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
		global $ilToolbar, $ilAccess, $ilTabs;

		$ilTabs->activateTab('id_info');
		
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
			// #15127
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("view");
			$button->setPrimary(true);			
			$button->setUrl("ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=".$this->object->getRefID());		
			$button->setTarget("ilContObj".$this->object->getId());
			$ilToolbar->addButtonInstance($button);
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
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm.svg"));
		
		$this->getTabs();
		$this->tpl->setTitle($this->object->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*/
	function getTabs()
	{
		global $ilUser, $ilAccess, $ilTabs, $lng, $ilHelp;

		$ilHelp->setScreenIdComponent("htlm");
		
		if($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilTabs->addTab("id_list_files",
				$lng->txt("cont_list_files"),
				$this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));
			
			$ilTabs->addTab("id_info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass(array("ilobjfilebasedlmgui", "ilinfoscreengui"), "showSummary"));
			
			$ilTabs->addTab("id_settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "properties"));
		}
		
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$ilTabs->addTab("id_learning_progress",
				$lng->txt("learning_progress"),
				$this->ctrl->getLinkTargetByClass(array('ilobjfilebasedlmgui','illearningprogressgui'), ''));
		}

		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id) &&
			ilLicenseAccess::_isEnabled() &&
			$this->object->getShowLicense())
		{
			$ilTabs->addTab("id_license",
				$lng->txt("license"),
				$this->ctrl->getLinkTargetByClass('illicensegui', ''));
		}
		
		if($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$ilTabs->addTab("id_meta_data",
				$lng->txt("meta_data"),
				$this->ctrl->getLinkTargetByClass('ilmdeditorgui',''));
			
			if($this->object->getShowBibliographicalData())
			{
				$ilTabs->addTab("id_bib_data",
					$lng->txt("bib_data"),
					$this->ctrl->getLinkTarget($this, "editBibItem"));
			}
		}


		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("export",
				$lng->txt("export"),
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}

		if ($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()))
		{
			$ilTabs->addTab("id_permissions",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"));
		}

		require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
		$startfile = ilObjFileBasedLMAccess::_determineStartUrl($this->object->getId());

		if ($startfile != "")
		{
			$ilTabs->addNonTabbedLink("presentation_view",
				$this->lng->txt("glo_presentation_view"),
				"ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=".$this->object->getRefID(),
				"_blank"
			);
		}

	}
	
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	public static function _goto($a_target)
	{
		global $rbacsystem, $ilErr, $lng, $ilAccess;

		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
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


	/**
	 * Import file
	 *
	 * @param
	 * @return
	 */
	function importFileObject($parent_id = null)
	{
		try
		{
			return parent::importFileObject();
		}
		catch (ilManifestFileNotFoundImportException $e)
		{
			// since there is no manifest xml we assume that this is an HTML export file
			$this->createFromDirectory($e->getTmpDir());
		}
	}
	
	/**
	 * Create new object from a html zip file
	 *
	 * @param
	 * @return
	 */
	function createFromDirectory($a_dir)
	{
		global $ilErr;
		
		if (!$this->checkPermissionBool("create", "", "htlm") || $a_dir == "")
		{
			$ilErr->raiseError($this->lng->txt("no_create_permission"));
		}
		
		// create instance
		$newObj = new ilObjFileBasedLM();
		$filename = ilUtil::stripSlashes($_FILES["importfile"]["name"]);
		$newObj->setTitle($filename);
		$newObj->setDescription("");
		$newObj->create();
		$newObj->populateByDirectoy($a_dir, $filename);
		$this->putObjectInTree($newObj);

		$this->afterSave($newObj);
	}
	
	
	
	
	////
	//// Export to HTML
	////


	/**
	 * create html package
	 */
	function exportHTML()
	{
		$inst_id = IL_INST_ID;

		include_once("./Services/Export/classes/class.ilExport.php");
		
		ilExport::_createExportDirectory($this->object->getId(), "html",
			$this->object->getType());
		$export_dir = ilExport::_getExportDirectory($this->object->getId(), "html",
			$this->object->getType());
		
		$subdir = $this->object->getType()."_".$this->object->getId();
		$filename = $this->subdir.".zip";

		$target_dir = $export_dir."/".$subdir;

		ilUtil::delDir($target_dir);
		ilUtil::makeDir($target_dir);

		$source_dir = $this->object->getDataDirectory();

		ilUtil::rCopy($source_dir, $target_dir);

		// zip it all
		$date = time();
		$zip_file = $export_dir."/".$date."__".IL_INST_ID."__".
			$this->object->getType()."_".$this->object->getId().".zip";
		ilUtil::zip($target_dir, $zip_file);

		ilUtil::delDir($target_dir);
	}

}
?>
