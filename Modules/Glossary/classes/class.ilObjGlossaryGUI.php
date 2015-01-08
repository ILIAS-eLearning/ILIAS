<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryTermGUI.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
require_once("./Modules/Glossary/classes/class.ilTermDefinitionEditorGUI.php");
require_once("./Services/COPage/classes/class.ilPCParagraph.php");

/**
* Class ilGlossaryGUI
*
* GUI class for ilGlossary
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjGlossaryGUI: ilGlossaryTermGUI, ilMDEditorGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjGlossaryGUI: ilInfoScreenGUI, ilCommonActionDispatcherGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjGlossaryGUI: ilObjTaxonomyGUI, ilExportGUI, ilObjectCopyGUI
* 
* @ingroup ModulesGlossary
*/
class ilObjGlossaryGUI extends ilObjectGUI
{
	var $admin_tabs;
	var $mode;
	var $term;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjGlossaryGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		global $ilCtrl, $lng;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "offset"));
		
		$lng->loadLanguageModule("content");
		
		$this->type = "glo";
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
		
		// determine term id and check whether it is valid (belongs to
		// current glossary)
		$this->term_id = (int) $_GET["term_id"];
		$term_glo_id = ilGlossaryTerm::_lookGlossaryID($this->term_id);
		if ($this->term_id > 0 && $term_glo_id != $this->object->getId())
		{
			$this->term_id = "";
		}

		$this->tax_id = $this->object->getTaxonomyId();
		if ($this->tax_id > 0)
		{
			$this->ctrl->saveParameter($this, array("show_tax", "tax_node"));
			
			include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
			$this->tax = new ilObjTaxonomy($this->tax_id);
		}
		if ((int) $_GET["tax_node"] > 1 && $this->tax->getTree()->readRootId() != $_GET["tax_node"])
		{
			$this->tax_node = (int) $_GET["tax_node"];
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $ilTabs, $ilErr;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		switch ($next_class)
		{
			case 'ilmdeditorgui':
				$this->checkPermission("write");
				
				$this->getTemplate();
				$this->setTabs();
				$this->setLocator();
				$this->addHeaderAction();

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			case "ilglossarytermgui":
				$this->getTemplate();
//				$this->quickList();
				$this->ctrl->setReturn($this, "listTerms");
				$term_gui =& new ilGlossaryTermGUI($this->term_id);
				$term_gui->setGlossary($this->object);
				//$ret =& $term_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($term_gui);
				break;
				
			case "ilinfoscreengui":
				$this->addHeaderAction();
				$this->showInfoScreen();
				break;
				
			case "ilobjstylesheetgui":
				include_once ("./Services/Style/classes/class.ilObjStyleSheetGUI.php");
				$this->ctrl->setReturn($this, "editStyleProperties");
				$style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
				$style_gui->omitLocator();
				if ($cmd == "create" || $_GET["new_type"]=="sty")
				{
					$style_gui->setCreationMode(true);
				}

				if ($cmd == "confirmedDelete")
				{
					$this->object->setStyleSheetId(0);
					$this->object->update();
				}

				$ret = $this->ctrl->forwardCommand($style_gui);

				if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle")
				{
					$style_id = $ret;
					$this->object->setStyleSheetId($style_id);
					$this->object->update();
					$this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
				}
				break;

				
			case 'ilpermissiongui':
				if (strtolower($_GET["baseClass"]) == "iladministrationgui")
				{
					$this->prepareOutput();
				}
				else
				{
					$this->getTemplate();
					$this->setTabs();
					$this->setLocator();
					$this->addHeaderAction();
				}
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case "ilobjtaxonomygui":
				$this->getTemplate();
				$this->setTabs();
				$this->setLocator();
				$this->addHeaderAction();
				$ilTabs->activateTab("settings");
				$this->setSettingsSubTabs("taxonomy");

				include_once("./Services/Taxonomy/classes/class.ilObjTaxonomyGUI.php");
				$this->ctrl->setReturn($this, "properties");
				$tax_gui = new ilObjTaxonomyGUI();
				$tax_gui->setMultiple(false);
				
				/*include_once("./Modules/Glossary/classes/class.ilTermTaxInfo.php");
				$term_info = new ilTermTaxInfo();*/
				//$tax_gui->activateAssignedItemSorting($term_info, "glo", "term");
				
				$tax_gui->setAssignedObject($this->object->getId());
				$ret = $this->ctrl->forwardCommand($tax_gui);
				break;

			case "ilexportgui":
				$this->getTemplate();
				$this->setTabs();
				$ilTabs->activateTab("export");
				$this->setLocator();
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				//$exp_gui->addFormat("xml", "", $this, "export");
				$exp_gui->addFormat("xml");
				$exp_gui->addFormat("html", "", $this, "exportHTML");
				$exp_gui->addCustomColumn($lng->txt("cont_public_access"),
						$this, "getPublicAccessColValue");
				$exp_gui->addCustomMultiCommand($lng->txt("cont_public_access"),
						$this, "publishExportFile");
				$ret = $this->ctrl->forwardCommand($exp_gui);
				break;

			case 'ilobjectcopygui':
				$this->prepareOutput();
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('glo');
				$this->ctrl->forwardCommand($cp);
				break;

			default:				
				$cmd = $this->ctrl->getCmd("listTerms");

				if (($cmd == "create") && ($_POST["new_type"] == "term"))
				{
					$this->ctrl->setCmd("create");
					$this->ctrl->setCmdClass("ilGlossaryTermGUI");
					$ret =& $this->executeCommand();
					return;
				}
				else
				{
					if (!in_array($cmd, array("frameset", "quickList")))
					{
						if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
							$this->getCreationMode() == true)
						{
							$this->prepareOutput();
							$cmd.= "Object";
						}
						else
						{
							$this->getTemplate();
							$this->setTabs();
							$this->setLocator();
							$this->addHeaderAction();
							
							if($cmd == "redrawHeaderAction")
							{
								$cmd = "redrawHeaderActionObject";
							}
						}
					}
					$ret =& $this->$cmd();
				}
				break;
		}

		if (!in_array($cmd, array("frameset", "quickList")))
		{
			if (strtolower($_GET["baseClass"]) != "iladministrationgui")
			{
				if (!$this->getCreationMode())
				{
					$this->tpl->show();
				}
			}
		}
		else
		{
			$this->tpl->show(false);
		}
	}

	function assignObject()
	{
		include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");

		$this->object =& new ilObjGlossary($this->id, true);
	}

	/*protected function initCreationForms($a_new_type)
	{
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type)
			);

		return $forms;
	}*/

    function initCreateForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($a_new_type."_new"));

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setSize(min(40, ilObject::TITLE_LENGTH));
		$ti->setMaxLength(ilObject::TITLE_LENGTH);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		// mode
		$stati 	= array(
						"none"=>$this->lng->txt("glo_mode_normal"),
						"level"=>$this->lng->txt("glo_mode_level"),
						"subtree"=>$this->lng->txt("glo_mode_subtree")
						);
		$tm = new ilSelectInputGUI($this->lng->txt("glo_mode"), "glo_mode");
		$tm->setOptions($stati);
		$tm->setInfo($this->lng->txt("glo_mode_desc"));
		$tm->setRequired(true);
		$form->addItem($tm);

		$form->addCommandButton("save", $this->lng->txt($a_new_type."_add"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}
	
	function importObject()
	{
		$this->createObject();
	}

	/**
	* save new content object to db
	*/
	function saveObject()
	{
		global $tpl;

		$new_type = $_REQUEST["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->checkPermissionBool("create", "", $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule($new_type);
		$this->ctrl->setParameter($this, "new_type", $new_type);

		$form = $this->initCreateForm($new_type);
		if ($form->checkInput())
		{
			$this->ctrl->setParameter($this, "new_type", "");

			include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
			$newObj = new ilObjGlossary();
			$newObj->setType($new_type);
			$newObj->setTitle($form->getInput("title"));
			$newObj->setDescription($form->getInput("desc"));
			$newObj->setVirtualMode($form->getInput("glo_mode"));
			$newObj->create();
			
			$this->putObjectInTree($newObj);

			// always send a message
			ilUtil::sendSuccess($this->lng->txt("glo_added"),true);
			ilUtil::redirect("ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$newObj->getRefId());
		}

		// display only this form to correct input
		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function importFileObject()
	{
		$new_type = $_REQUEST["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->checkPermissionBool("create", "", $new_type))
		{
			$ilErr->raiseError($this->lng->txt("no_create_permission"));
		}

		$this->lng->loadLanguageModule($new_type);
		$this->ctrl->setParameter($this, "new_type", $new_type);

		$form = $this->initImportForm($new_type);
		if ($form->checkInput())
		{
		    $this->ctrl->setParameter($this, "new_type", "");
			$upload = $_FILES["importfile"];

			// create and insert object in objecttree
			include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
			$newObj = new ilObjGlossary();
			$newObj->setType($new_type);
			$newObj->setTitle($upload["name"]);
			$newObj->create(true);
			
			$this->putObjectInTree($newObj);
			
			// create import directory
			$newObj->createImportDirectory();

			// copy uploaded file to import directory
			$file = pathinfo($upload["name"]);
			$full_path = $newObj->getImportDirectory()."/".$upload["name"];

			ilUtil::moveUploadedFile($upload["tmp_name"], $upload["name"],
				$full_path);

			// unzip file
			ilUtil::unzip($full_path);

			// determine filename of xml file
			$subdir = basename($file["basename"],".".$file["extension"]);
			$xml_file = $newObj->getImportDirectory()."/".$subdir."/".$subdir.".xml";
			
			// check whether this is a new export file.
			// this is the case if manifest.xml exits
//echo "1-".$newObj->getImportDirectory()."/".$subdir."/manifest.xml"."-";
			if (is_file($newObj->getImportDirectory()."/".$subdir."/manifest.xml"))
			{
				include_once("./Services/Export/classes/class.ilImport.php");
				$imp = new ilImport((int) $_GET["ref_id"]);
				$map = $imp->getMapping();
				$map->addMapping("Modules/Glossary", "glo", "new_id", $newObj->getId());
				$imp->importObject($newObj, $full_path, $upload["name"], "glo",
					"Modules/Glossary", true);
				ilUtil::sendSuccess($this->lng->txt("glo_added"),true);
				ilUtil::redirect("ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$newObj->getRefId());
			}

			// check whether subdirectory exists within zip file
			if (!is_dir($newObj->getImportDirectory()."/".$subdir))
			{
				$this->ilias->raiseError(sprintf($this->lng->txt("cont_no_subdir_in_zip"), $subdir),
					$this->ilias->error_obj->MESSAGE);
			}

			// check whether xml file exists within zip file
			if (!is_file($xml_file))
			{
				$this->ilias->raiseError(sprintf($this->lng->txt("cont_zip_file_invalid"), $subdir."/".$subdir.".xml"),
					$this->ilias->error_obj->MESSAGE);
			}

			include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
			$contParser = new ilContObjParser($newObj, $xml_file, $subdir);
			$contParser->startParsing();
			ilObject::_writeImportId($newObj->getId(), $newObj->getImportId());

			// delete import directory
			ilUtil::delDir($newObj->getImportDirectory());

			ilUtil::sendSuccess($this->lng->txt("glo_added"),true);
			ilUtil::redirect("ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$newObj->getRefId());
		}

		// display form to correct errors
		$form->setValuesByPost();
		$tpl->setContent($form->getHtml());
	}

	/**
	 * Show info screen
	 *
	 * @param
	 * @return
	 */
	function showInfoScreen()
	{
		global $ilAccess;
		
		$this->getTemplate();
		$this->setTabs();
		$this->setLocator();
		$this->lng->loadLanguageModule("meta");
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
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
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		ilObjGlossaryGUI::addUsagesToInfo($info, $this->object->getId());
		
		$this->ctrl->forwardCommand($info);
	}
	
	/**
	 * Add usages to info
	 *
	 * @param
	 * @return
	 */
	function addUsagesToInfo($info, $glo_id)
	{
		global $lng, $ilAccess;
	
		$info->addSection($lng->txt("glo_usages"));
		include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
		$sms = ilObjSAHSLearningModule::getScormModulesForGlossary($glo_id);
		foreach ($sms as $sm)
		{
			$link = false;
			$refs = ilObject::_getAllReferences($sm);
			foreach ($refs as $ref)
			{
				if ($link === false)
				{
					if ($ilAccess->checkAccess("write", "", $ref))
					{
						include_once("./Services/Link/classes/class.ilLink.php");
						$link = ilLink::_getLink($ref,'sahs');
					}
				}
			}
			
			$entry = ilObject::_lookupTitle($sm);
			if ($link !== false)
			{
				$entry = "<a href='".$link."' target='_top'>".$entry."</a>";
			}
			
			$info->addProperty($lng->txt("obj_sahs"), $entry);
		}
	}
	
	
	function viewObject()
	{
		global $rbacsystem;
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return;
		}

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function properties()
	{
		global $tpl;
		
		$this->checkPermission("write");

		$this->setSettingsSubTabs("general_settings");
		
		$this->initSettingsForm();
		
		// Edit ecs export settings
		include_once 'Modules/Glossary/classes/class.ilECSGlossarySettings.php';
		$ecs = new ilECSGlossarySettings($this->object);		
		$ecs->addSettingsToForm($this->form, 'glo');		
		
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Init settings form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initSettingsForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$this->form->addItem($title);

		// description
		$desc = new ilTextAreaInputGUI($lng->txt("desc"), "description");
		$this->form->addItem($desc);

		$lng->loadLanguageModule("rep");
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$this->form->addItem($section);

		// online
		$online = new ilCheckboxInputGUI($lng->txt("cont_online"), "cobj_online");
		$online->setValue("y");
		$online->setInfo($lng->txt("glo_online_info"));
		$this->form->addItem($online);

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('glo_content_settings'));
		$this->form->addItem($section);

		// glossary mode
		$glo_mode = new ilRadioGroupInputGUI($lng->txt("glo_mode"), "glo_mode");
		//$glo_mode->setInfo($lng->txt("glo_mode_desc"));
			$op1 = new ilRadioOption($lng->txt("glo_mode_normal"), "none",$lng->txt("glo_mode_normal_info"));
			$glo_mode->addOption($op1);
			$op2 = new ilRadioOption($lng->txt("glo_mode_level"), "level",$lng->txt("glo_mode_level_info"));
			$glo_mode->addOption($op2);
			$op3 = new ilRadioOption($lng->txt("glo_mode_subtree"), "subtree",$lng->txt("glo_mode_subtree_info"));
			$glo_mode->addOption($op3);
		$this->form->addItem($glo_mode);

		// glossary mode
		/*$options = array(
			"none"=>$this->lng->txt("glo_mode_normal"),
			"level"=>$this->lng->txt("glo_mode_level"),
			"subtree"=>$this->lng->txt("glo_mode_subtree")
			);
		$glo_mode = new ilSelectInputGUI($lng->txt("glo_mode"), "glo_mode");
		$glo_mode->setOptions($options);
		$glo_mode->setInfo($lng->txt("glo_mode_desc"));
		$this->form->addItem($glo_mode);*/


		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('cont_presentation'));
		$this->form->addItem($section);

		// presentation mode
		$pres_mode = new ilRadioGroupInputGUI($lng->txt("glo_presentation_mode"), "pres_mode");
		$pres_mode->setValue("table");
		$op1 = new ilRadioOption($lng->txt("glo_table_form"), "table", $lng->txt("glo_table_form_info"));

			// short text length
			$snl = new ilNumberInputGUI($lng->txt("glo_text_snippet_length"), "snippet_length");
			$snl->setMaxValue(3000);
			$snl->setMinValue(100);
			$snl->setMaxLength(4);
			$snl->setSize(4);
			$snl->setInfo($lng->txt("glo_text_snippet_length_info"));
			$snl->setValue(200);
			$op1->addSubItem($snl);

		$pres_mode->addOption($op1);
		$op2 = new ilRadioOption($lng->txt("glo_full_definitions"), "full_def", $lng->txt("glo_full_definitions_info"));
		$pres_mode->addOption($op2);
		$this->form->addItem($pres_mode);

		// show taxonomy
		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		$tax_ids = ilObjTaxonomy::getUsageOfObject($this->object->getId());
		if (count($tax_ids) > 0)
		{ 
			$show_tax = new ilCheckboxInputGUI($this->lng->txt("glo_show_taxonomy"), "show_tax");
			$show_tax->setInfo($this->lng->txt("glo_show_taxonomy_info"));
			$this->form->addItem($show_tax);
		}
		
		// downloads
		$down = new ilCheckboxInputGUI($lng->txt("cont_downloads"), "glo_act_downloads");
		$down->setValue("y");
		$down->setInfo($lng->txt("cont_downloads_desc"));
		$this->form->addItem($down);
		
		if ($a_mode == "edit")
		{
			$title->setValue($this->object->getTitle());
			$desc->setValue($this->object->getDescription());
			$online->setChecked($this->object->getOnline());
			$glo_mode->setValue($this->object->getVirtualMode());
			$pres_mode->setValue($this->object->getPresentationMode());
			$snl->setValue($this->object->getSnippetLength());
			if (count($tax_ids) > 0)
			{
				$show_tax->setChecked($this->object->getShowTaxonomy());
			}
			
			$down->setChecked($this->object->isActiveDownloads());
		}		
		
		// advanced metadata
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_REC_SELECTION,'glo',$this->object->getId(), "term");
		$record_gui->setPropertyForm($this->form);
		$record_gui->parseRecordSelection($this->lng->txt("glo_add_term_properties"));
		
		// sort columns, if adv fields are given
		include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
		$adv_ap = new ilGlossaryAdvMetaDataAdapter($this->object->getId());
		$cols = $adv_ap->getColumnOrder();
		if (count($cols) > 1)
		{
			include_once './Modules/Glossary/classes/class.ilGloAdvColSortInputGUI.php';
			$ti = new ilGloAdvColSortInputGUI($lng->txt("cont_col_ordering"), "field_order");
			$this->form->addItem($ti);
			$ti->setValue($cols);
		}
	
		// save and cancel commands
		$this->form->addCommandButton("saveProperties", $lng->txt("save"));
					
		$this->form->setTitle($lng->txt("cont_glo_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}	


	/**
	* save properties
	*/
	function saveProperties()
	{
		global $tpl;

		$this->initSettingsForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($_POST['title']);
			$this->object->setDescription($_POST['description']);
			$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
			$this->object->setVirtualMode($_POST["glo_mode"]);
//			$this->object->setActiveGlossaryMenu(ilUtil::yn2tf($_POST["glo_act_menu"]));
			$this->object->setActiveDownloads(ilUtil::yn2tf($_POST["glo_act_downloads"]));
			$this->object->setPresentationMode($_POST["pres_mode"]);
			$this->object->setSnippetLength($_POST["snippet_length"]);
			$this->object->setShowTaxonomy($_POST["show_tax"]);
			$this->object->update();

			// field order of advanced metadata
			include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
			$adv_ap = new ilGlossaryAdvMetaDataAdapter($this->object->getId());
			$cols = $adv_ap->getColumnOrder();
			if (count($cols) > 1)
			{
				$adv_ap->saveColumnOrder($_POST["field_order"]);
			}
			
			// set definition short texts dirty
			include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
			ilGlossaryDefinition::setShortTextsDirty($this->object->getId());

			// update metadata record selection
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
			$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_REC_SELECTION,'glo',$this->object->getId(), "term");
			$record_gui->saveSelection();
			
			// Update ecs export settings
			include_once 'Modules/Glossary/classes/class.ilECSGlossarySettings.php';	
			$ecs = new ilECSGlossarySettings($this->object);			
			if($ecs->handleSettingsUpdate())
			{
				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
				$this->ctrl->redirect($this, "properties");
			}
			
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* glossary edit frameset
	*/
	function frameset()
	{
		global $ilCtrl;
		
		include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();
		$fs_gui->setFramesetTitle($this->object->getTitle());
		if ((int) $_GET["edit_term"] > 0)
		{
			$ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", (int) $_GET["edit_term"]);
			$fs_gui->setMainFrameSource($this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listDefinitions"));
		}
		else
		{
			$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "listTerms"));
		}
		$fs_gui->setSideFrameSource($this->ctrl->getLinkTarget($this, "quickList"));
		$fs_gui->setMainFrameName("content");
		$fs_gui->setSideFrameName("tree");
		$fs_gui->show();
		exit;
	}
	
	/**
	* quick term list
	*/
	function quickList()
	{
		global $ilUser, $tpl;

	}
	

	/**
	* list terms
	*/
	function listTerms()
	{
		global $ilUser, $ilToolbar, $lng, $ilCtrl, $tpl;

		if ($_GET["show_tax"])
		{
			$this->showTaxonomy();
		}
		
		// term
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("cont_new_term"), "new_term");
		$ti->setMaxLength(80);
		$ti->setSize(20);
		$ilToolbar->addInputItem($ti, true);
		
		// language
		$this->lng->loadLanguageModule("meta");
		$lang = ilMDLanguageItem::_getLanguages();
		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("language"), "term_language");
		$si->setOptions($lang);
		$si->setValue($s_lang);
		$ilToolbar->addInputItem($si, true);
		
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		$ilToolbar->addFormButton($lng->txt("glo_add_new_term"), "addTerm");
		//$select_language = ilUtil::formSelect ($s_lang, "term_language",$lang,false,true);
		//$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		
		if (is_object($this->tax))
		{
			$ilToolbar->addSeparator();
			if ($_GET["show_tax"])
			{
				$ilToolbar->addButton($lng->txt("glo_hide_taxonomy"),
						$ilCtrl->getLinkTarget($this, "deactTaxonomy"));				
			}
			else
			{
				$ilToolbar->addButton($lng->txt("glo_show_taxonomy"),
						$ilCtrl->getLinkTarget($this, "actTaxonomy"));
			}
		}

		include_once("./Modules/Glossary/classes/class.ilTermListTableGUI.php");
		$tab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
		$tpl->setContent($tab->getHTML());
	}

	/**
	 * Show Taxonomy
	 *
	 * @param
	 * @return
	 */
	function actTaxonomy()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "show_tax", 1);
		$ilCtrl->redirect($this, "listTerms");
	}

	/**
	 * Hide Taxonomy
	 *
	 * @param
	 * @return
	 */
	function deactTaxonomy()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "show_tax", "");
		$ilCtrl->redirect($this, "listTerms");
	}

	
	/**
	 * show possible action (form buttons)
	 *
	 * @access	public
	 */
	function showActions($a_actions)
	{
		foreach ($a_actions as $name => $lng)
		{
			$d[$name] = array("name" => $name, "lng" => $lng);
		}

		$notoperations = array();
		$operations = array();

		$operations = $d;

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $val["name"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.svg"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* add term
	*/
	function addTerm()
	{
		global $lng, $ilCtrl;
		
		if (trim($_POST["new_term"]) == "")
		{
			ilUtil::sendFailure($lng->txt("cont_please_enter_a_term"), true);
			$ilCtrl->redirect($this, "listTerms");
		}
		
		// add term
		include_once ("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$term =& new ilGlossaryTerm();
		$term->setGlossary($this->object);
		$term->setTerm(ilUtil::stripSlashes($_POST["new_term"]));
		$term->setLanguage($_POST["term_language"]);
		$_SESSION["il_text_lang_".$_GET["ref_id"]] = $_POST["term_language"];
		$term->create();

		// add first definition
		$def =& new ilGlossaryDefinition();
		$def->setTermId($term->getId());
		$def->setTitle(ilUtil::stripSlashes($_POST["new_term"]));
		$def->create();

		$this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term->getId());
		$this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", $def->getId());
		$this->ctrl->redirectByClass(array("ilglossarytermgui",
			"iltermdefinitioneditorgui", "ilglossarydefpagegui"), "edit");
	}

	/**
	* move a definiton up
	*/
	function moveDefinitionUp()
	{
		include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");

		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$definition->moveUp();

		$this->ctrl->redirect($this, "listTerms");
	}

	/**
	* move a definiton down
	*/
	function moveDefinitionDown()
	{
		include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");

		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$definition->moveDown();

		$this->ctrl->redirect($this, "listTerms");
	}

	/**
	* deletion confirmation screen
	*/
	function confirmDefinitionDeletion()
	{
		global $ilCtrl, $lng;
		
		//$this->getTemplate();
		//$this->displayLocator();
		//$this->setTabs();

		$term = new ilGlossaryTerm($this->term_id);
		
		$add = "";
		$nr = ilGlossaryTerm::getNumberOfUsages($this->term_id);
		if ($nr > 0)
		{
			$ilCtrl->setParameterByClass("ilglossarytermgui",
				"term_id", $this->term_id);
			$link = "[<a href='".
				$ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listUsages").
				"'>".$lng->txt("glo_list_usages")."</a>]";
			$add = "<br/>".sprintf($lng->txt("glo_term_is_used_n_times"), $nr)." ".$link;
		}
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($this->lng->txt("info_delete_sure").$add);

		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelDefinitionDeletion");
		$cgui->setConfirm($this->lng->txt("confirm"), "deleteDefinition");
		
		// content style
		$this->setContentStyleSheet($this->tpl);

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$definition = new ilGlossaryDefinition($_GET["def"]);
		$page_gui = new ilGlossaryDefPageGUI($definition->getId());
		$page_gui->setTemplateOutput(false);
		$page_gui->setStyleId($this->object->getStyleSheetId());
		$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
		$output = $page_gui->preview();
		
		$cgui->addItem("def", $_GET["def"], $term->getTerm().$output);
		
		$this->tpl->setContent($cgui->getHTML());
	}
	
	function cancelDefinitionDeletion()
	{
		$this->ctrl->redirect($this, "listTerms");
	}


	function deleteDefinition()
	{
		$definition =& new ilGlossaryDefinition($_REQUEST["def"]);
		$definition->delete();
		$this->ctrl->redirect($this, "listTerms");
	}

	/**
	* edit term
	*/
	function editTerm()
	{
		// deprecated
	}


	/**
	* update term
	*/
	function updateTerm()
	{
		$term = new ilGlossaryTerm($this->term_id);

		$term->setTerm(ilUtil::stripSlashes($_POST["term"]));
		$term->setLanguage($_POST["term_language"]);
		$term->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this, "listTerms");
	}



	/**
	* export content object
	*/
	function export()
	{
		$this->checkPermission("write");
		
		require_once("./Modules/Glossary/classes/class.ilGlossaryExport.php");
		$glo_exp = new ilGlossaryExport($this->object);
		$glo_exp->buildExportFile();
		$this->ctrl->redirectByClass("ilexportgui", "");
	}
	
	/**
	* create html package
	*/
	function exportHTML()
	{
		require_once("./Modules/Glossary/classes/class.ilGlossaryExport.php");
		$glo_exp = new ilGlossaryExport($this->object, "html");
		$glo_exp->buildExportFile();
//echo $this->tpl->get();
		$this->ctrl->redirectByClass("ilexportgui", "");
	}

	/**
	* download export file
	*/
	function publishExportFile()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}
		
		$file = explode(":", $_POST["file"][0]);
		$export_dir = $this->object->getExportDirectory($file[0]);
		
		if ($this->object->getPublicExportFile($file[0]) ==
			$file[1])
		{
			$this->object->setPublicExportFile($file[0], "");
		}
		else
		{
			$this->object->setPublicExportFile($file[0], $file[1]);
		}
		$this->object->update();
		$this->ctrl->redirectByClass("ilexportgui", "");
	}

	/*
	* list all export files
	*/
	function viewExportLog()
	{
	}

	/**
	* confirm term deletion
	*/
	function confirmTermDeletion()
	{
		global $ilCtrl, $lng;

		//$this->prepareOutput();
		if (!isset($_POST["id"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listTerms");
		}
		
		// check ids
		include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		foreach ($_POST["id"] as $term_id)
		{
			$term_glo_id = ilGlossaryTerm::_lookGlossaryID((int) $term_id);
			if ($term_glo_id != $this->object->getId())
			{
				ilUtil::sendFailure($this->lng->txt("glo_term_must_belong_to_glo"), true);
				$ilCtrl->redirect($this, "listTerms");
			}
		}
		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("info_delete_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelTermDeletion");
		$cgui->setConfirm($this->lng->txt("confirm"), "deleteTerms");
				
		foreach($_POST["id"] as $id)
		{
			$term = new ilGlossaryTerm($id);

			$add = "";
			$nr = ilGlossaryTerm::getNumberOfUsages($id);
			if ($nr > 0)
			{
				$ilCtrl->setParameterByClass("ilglossarytermgui",
					"term_id", $id);
				$link = "[<a href='".
					$ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listUsages").
					"'>".$lng->txt("glo_list_usages")."</a>]";
				$add = "<div class='small'>".
					sprintf($lng->txt("glo_term_is_used_n_times"), $nr)." ".$link."</div>";
			}
			
			$cgui->addItem("id[]", $id, $term->getTerm().$add);
		}

		$this->tpl->setContent($cgui->getHTML());
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function cancelTermDeletion()
	{
		$this->ctrl->redirect($this, "listTerms");
	}

	/**
	* delete selected terms
	*/
	function deleteTerms()
	{
		foreach($_POST["id"] as $id)
		{
			$term = new ilGlossaryTerm($id);
			$term->delete();
		}
		$this->ctrl->redirect($this, "listTerms");
	}

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "")
	{		
		if(strtolower($_GET["baseClass"]) != "ilglossaryeditorgui")
		{
			parent::setLocator($a_tree, $a_id);
		}
		else
		{
			if(is_object($this->object))
			{
				require_once("./Modules/Glossary/classes/class.ilGlossaryLocatorGUI.php");
				$gloss_loc =& new ilGlossaryLocatorGUI();
				if (is_object($this->term))
				{
					$gloss_loc->setTerm($this->term);
				}
				$gloss_loc->setGlossary($this->object);
				//$gloss_loc->setDefinition($this->definition);
				$gloss_loc->display();
			}
		}

	}

	/**
	* view content
	*/
	function view()
	{
		//$this->prepareOutput();
		$this->viewObject();
	}

	/**
	* create new (subobject) in glossary
	*/
	function create()
	{
		switch($_POST["new_type"])
		{
			case "term":
				$term_gui =& new ilGlossaryTermGUI();
				$term_gui->create();
				break;
		}
	}

	function saveTerm()
	{
		$term_gui =& new ilGlossaryTermGUI();
		$term_gui->setGlossary($this->object);
		$term_gui->save();

		ilUtil::sendSuccess($this->lng->txt("cont_added_term"),true);

		//ilUtil::redirect("glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms");
		$ilCtrl->redirect($this, "listTerms");
	}


	/**
	* add definition
	*/
	function addDefinition()
	{
		global $ilCtrl;

		$term_id = (int) $_GET["term_id"];

		include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$term_glo_id = ilGlossaryTerm::_lookGlossaryID((int) $term_id);
		if ($term_glo_id != $this->object->getId())
		{
			ilUtil::sendFailure($this->lng->txt("glo_term_must_belong_to_glo"), true);
			$ilCtrl->redirect($this, "listTerms");
		}

		// add term
		include_once ("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$term = new ilGlossaryTerm($term_id);

		// add first definition
		$def = new ilGlossaryDefinition();
		$def->setTermId($term->getId());
		$def->setTitle(ilUtil::stripSlashes($term->getTerm()));
		$def->create();

		$this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term->getId());
		$this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", $def->getId());
		$this->ctrl->redirectByClass(array("ilglossarytermgui",
			"iltermdefinitioneditorgui", "ilglossarydefpagegui"), "edit");
		
	}

	function getTemplate()
	{
		$this->tpl->getStandardTemplate();

		$title = $this->object->getTitle();


		if ($this->term_id > 0)
		{
			$this->tpl->setTitle($this->lng->txt("term").": ".
				ilGlossaryTerm::_lookGlossaryTerm($this->term_id));
		}
		else
		{
			parent::setTitleAndDescription();
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
			$this->tpl->setTitle($this->lng->txt("glo").": ".$title);
		}
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		$this->getTabs($this->tabs_gui);
	}

	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilHelp;
		
		$ilHelp->setScreenIdComponent("glo");

		// list terms
		$force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "listTerms")
				? true
				: false;
		$tabs_gui->addTarget("cont_terms",
			$this->ctrl->getLinkTarget($this, "listTerms"), array("listTerms", ""),
			get_class($this), "", $force_active);
			
		$force_active = false;
		if ($this->ctrl->getCmd() == "showSummary" ||
			strtolower($this->ctrl->getNextClass()) == "ilinfoscreengui")
		{
			$force_active = true;
		}
		$tabs_gui->addTarget("info_short",
			$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "",
			"ilInfoScreenGUI", "", $force_active);

		// properties
		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "properties"), "properties",
				get_class($this));

			// meta data
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
				 "", "ilmdeditorgui");

			// export
			/*$tabs_gui->addTarget("export",
				 $this->ctrl->getLinkTarget($this, "exportList"),
				 array("exportList", "viewExportLog"), get_class($this));*/

			// export
			$tabs_gui->addTarget("export",
				 $this->ctrl->getLinkTargetByClass("ilexportgui", ""),
				 "", "ilexportgui");
		}

		// permissions
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			/*$tabs_gui->addTarget("permission_settings",
				$this->ctrl->getLinkTarget($this, "perm"),
				array("perm", "info"),
				get_class($this));
				*/
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');

		}
		
		$tabs_gui->addNonTabbedLink("presentation_view",
			$this->lng->txt("glo_presentation_view"),
			"ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$this->object->getRefID(),
			"_top"
		);
		
	}
	
	/**
	 * Set sub tabs
	 */
	function setSettingsSubTabs($a_active)
	{
		global $ilTabs, $ilCtrl, $lng;

		if (in_array($a_active,
			array("general_settings", "style", "taxonomy")))
		{
			// general properties
			$ilTabs->addSubTab("general_settings",
				$lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, 'properties'));
				
			// style properties
			$ilTabs->addSubTab("style",
				$lng->txt("obj_sty"),
				$ilCtrl->getLinkTarget($this, 'editStyleProperties'));

			// taxonomy
			include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
			ilObjTaxonomy::loadLanguageModule();
			$ilTabs->addSubTab("taxonomy",
				$lng->txt("tax_taxonomy"),
				$ilCtrl->getLinkTargetByClass("ilobjtaxonomygui", ''));

			$ilTabs->activateSubTab($a_active);
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

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["baseClass"] = "ilGlossaryPresentationGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "infoScreen";
			$_GET["baseClass"] = "ilGlossaryPresentationGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

	/**
	 * Apply filter
	 */
	function applyFilter()
	{		
		include_once("./Modules/Glossary/classes/class.ilTermListTableGUI.php");
		$prtab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
		$prtab->resetOffset();
		$prtab->writeFilterToSession();
		$this->listTerms();		
	}
	
	/**
	 * Reset filter
	 * (note: this function existed before data table filter has been introduced
	 */
	function resetFilter()
	{
		include_once("./Modules/Glossary/classes/class.ilTermListTableGUI.php");
		$prtab = new ilTermListTableGUI($this, "listTerms", $this->tax_node);
		$prtab->resetOffset();
		$prtab->resetFilter();
		$this->listTerms();
	}


	////
	//// Style related functions
	////
	
	/**
	 * Set content style sheet
	 */
	function setContentStyleSheet($a_tpl = null)
	{
		global $tpl;

		if ($a_tpl != null)
		{
			$ctpl = $a_tpl;
		}
		else
		{
			$ctpl = $tpl;
		}

		$ctpl->setCurrentBlock("ContentStyle");
		$ctpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
		$ctpl->parseCurrentBlock();
	}
	
	
	/**
	 * Edit style properties
	 */
	function editStyleProperties()
	{
		global $ilTabs, $tpl;
		
		$this->checkPermission("write");
		
		$this->initStylePropertiesForm();
		$tpl->setContent($this->form->getHTML());
		
		$ilTabs->activateTab("settings");
		$this->setSettingsSubTabs("style");
	}
	
	/**
	 * Init style properties form
	 */
	function initStylePropertiesForm()
	{
		global $ilCtrl, $lng, $ilTabs, $ilSetting;
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$lng->loadLanguageModule("style");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$fixed_style = $ilSetting->get("fixed_content_style_id");
		$style_id = $this->object->getStyleSheetId();

		if ($fixed_style > 0)
		{
			$st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
			$st->setValue(ilObject::_lookupTitle($fixed_style)." (".
				$this->lng->txt("global_fixed").")");
			$this->form->addItem($st);
		}
		else
		{
			$st_styles = ilObjStyleSheet::_getStandardStyles(true, false,
				$_GET["ref_id"]);

			$st_styles[0] = $this->lng->txt("default");
			ksort($st_styles);

			if ($style_id > 0)
			{
				// individual style
				if (!ilObjStyleSheet::_lookupStandard($style_id))
				{
					$st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
					$st->setValue(ilObject::_lookupTitle($style_id));
					$this->form->addItem($st);

//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "edit"));

					// delete command
					$this->form->addCommandButton("editStyle",
						$lng->txt("style_edit_style"));
					$this->form->addCommandButton("deleteStyle",
						$lng->txt("style_delete_style"));
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "delete"));
				}
			}

			if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
			{
				$style_sel = ilUtil::formSelect ($style_id, "style_id",
					$st_styles, false, true);
				$style_sel = new ilSelectInputGUI($lng->txt("style_current_style"), "style_id");
				$style_sel->setOptions($st_styles);
				$style_sel->setValue($style_id);
				$this->form->addItem($style_sel);
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "create"));
				$this->form->addCommandButton("saveStyleSettings",
						$lng->txt("save"));
				$this->form->addCommandButton("createStyle",
					$lng->txt("sty_create_ind_style"));
			}
		}
		$this->form->setTitle($lng->txt("glo_style"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Create Style
	 */
	function createStyle()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
	}
	
	/**
	 * Edit Style
	 */
	function editStyle()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "edit");
	}

	/**
	 * Delete Style
	 */
	function deleteStyle()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "delete");
	}

	/**
	 * Save style settings
	 */
	function saveStyleSettings()
	{
		global $ilSetting;
	
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		if ($ilSetting->get("fixed_content_style_id") <= 0 &&
			(ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
			|| $this->object->getStyleSheetId() == 0))
		{
			$this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "editStyleProperties");
	}
	
	/**
	 * Get public access value for export table 
	 */
	function getPublicAccessColValue($a_type, $a_file)
	{
		global $lng, $ilCtrl;

		if ($this->object->getPublicExportFile($a_type) == $a_file)
		{
			return $lng->txt("yes");
		}
	
		return " ";		
	}

	/**
	 * Show taxonomy
	 *
	 * @param
	 * @return
	 */
	function showTaxonomy()
	{
		global $tpl, $lng;

		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		$tax_ids = ilObjTaxonomy::getUsageOfObject($this->object->getId());
		if (count($tax_ids) > 0)
		{
			include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
			$tax_exp = new ilTaxonomyExplorerGUI($this, "showTaxonomy", $tax_ids[0],
				"ilobjglossarygui", "listTerms");
			if (!$tax_exp->handleCommand())
			{
				//$tpl->setLeftNavContent($tax_exp->getHTML());
				$tpl->setLeftNavContent($tax_exp->getHTML()."&nbsp;");
			}
		}

	}

}

?>
