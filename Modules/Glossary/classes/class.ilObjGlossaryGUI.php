<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
* @ilCtrl_Calls ilObjGlossaryGUI: ilInfoScreenGUI, ilCommonActionDispatcherGUI
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
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess;
		
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		switch ($next_class)
		{
			case 'ilmdeditorgui':
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
				$this->ctrl->setReturn($this, "listTerms");
				$term_gui =& new ilGlossaryTermGUI($_GET["term_id"]);
				$term_gui->setGlossary($this->object);
				//$ret =& $term_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($term_gui);
				break;
				
			case "ilinfoscreengui":
				$this->addHeaderAction();
				$this->showInfoScreen();
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

			default:
				$cmd = $this->ctrl->getCmd("frameset");

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

	protected function initCreationForms($a_new_type)
	{
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type)
			);

		return $forms;
	}

    function initCreateForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($a_new_type."_new"));

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
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

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",
			"ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"".
			ilFrameTargetInfo::_getFrame("MainContent")."\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();

		//parent::viewObject();
	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function properties()
	{
		global $rbacsystem, $tree, $tpl;

		$this->initSettingsForm();
		$this->getSettingsValues();
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
		
		// online
		$cb = new ilCheckboxInputGUI($lng->txt("cont_online"), "cobj_online");
		$cb->setValue("y");
		$this->form->addItem($cb);
		
		// glossary mode
		$options = array(
			"none"=>$this->lng->txt("glo_mode_normal"),
			"level"=>$this->lng->txt("glo_mode_level"),
			"subtree"=>$this->lng->txt("glo_mode_subtree")
			);
		$si = new ilSelectInputGUI($lng->txt("glo_mode"), "glo_mode");
		$si->setOptions($options);
		$si->setInfo($lng->txt("glo_mode_desc"));
		$this->form->addItem($si);

		// presentation mode
		$radg = new ilRadioGroupInputGUI($lng->txt("glo_presentation_mode"), "pres_mode");
		$radg->setValue("table");
		$op1 = new ilRadioOption($lng->txt("glo_table_form"), "table", $lng->txt("glo_table_form_info"));

			// short text length
			$ni = new ilNumberInputGUI($lng->txt("glo_text_snippet_length"), "snippet_length");
			$ni->setMaxValue(3000);
			$ni->setMinValue(100);
			$ni->setMaxLength(4);
			$ni->setSize(4);
			$ni->setInfo($lng->txt("glo_text_snippet_length_info"));
			$ni->setValue(200);
			$op1->addSubItem($ni);

		$radg->addOption($op1);
		$op2 = new ilRadioOption($lng->txt("glo_full_definitions"), "full_def", $lng->txt("glo_full_definitions_info"));
		$radg->addOption($op2);
		$this->form->addItem($radg);

		
		// menu enabled?
/*
		$cb = new ilCheckboxInputGUI($lng->txt("cont_glo_menu"), "glo_act_menu");
		$cb->setValue("y");
		$this->form->addItem($cb);*/
		
		// downloads
		$cb = new ilCheckboxInputGUI($lng->txt("cont_downloads"), "glo_act_downloads");
		$cb->setValue("y");
		$cb->setInfo($lng->txt("cont_downloads_desc"));
		$this->form->addItem($cb);
	
		// save and cancel commands
		$this->form->addCommandButton("saveProperties", $lng->txt("save"));
					
		$this->form->setTitle($lng->txt("cont_glo_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}	

	/**
	 * Get current values for settings from 
	 */
	public function getSettingsValues()
	{
		$values = array();
	
		$values["cobj_online"] = $this->object->getOnline();
		$values["glo_mode"] = $this->object->getVirtualMode();
//		$values["glo_act_menu"] = $this->object->isActiveGlossaryMenu();
		$values["glo_act_downloads"] = $this->object->isActiveDownloads();
		$values["pres_mode"] = $this->object->getPresentationMode();
		$values["snippet_length"] = $this->object->getSnippetLength();
	
		$this->form->setValuesByArray($values);
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
			$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
			$this->object->setVirtualMode($_POST["glo_mode"]);
//			$this->object->setActiveGlossaryMenu(ilUtil::yn2tf($_POST["glo_act_menu"]));
			$this->object->setActiveDownloads(ilUtil::yn2tf($_POST["glo_act_downloads"]));
			$this->object->setPresentationMode($_POST["pres_mode"]);
			$this->object->setSnippetLength($_POST["snippet_length"]);
			$this->object->update();

			// set definition short texts dirty
			include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
			ilGlossaryDefinition::setShortTextsDirty($this->object->getId());

			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "properties");
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* glossary edit frameset
	*/
	function frameset()
	{
		include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();
		$fs_gui->setFramesetTitle($this->object->getTitle());
		$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "listTerms"));
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
		global $ilUser;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.glossary_short_list.html",
			"Modules/Glossary");
		
//		$this->tpl->addBlockFile("EXPLORER_TOP", "exp_top", "tpl.explorer_top.html");
//		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		$this->tpl->setVariable("FORMACTION1", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("CMD_REFR", "quickList");
		$this->tpl->setVariable("TXT_REFR", $this->lng->txt("refresh"));
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_terms"));
		
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// glossary term list template

		// load template for table
		$this->tpl->addBlockfile("SHORT_LIST", "list", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.term_short_tbl_row.html", true);

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		//$tbl->setTitle($this->lng->txt("cont_terms"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array($this->lng->txt("cont_term")));

		$cols = array("term");
		$header_params = $this->ctrl->getParameterArrayByClass("ilobjglossarygui", "listTerms");
		$header_params["cmd"] = "quickList";
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("100%"));
		$tbl->disable("title");

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->disable("header");
		
		$term_list = $this->object->getTermList();
		$tbl->setMaxCount(count($term_list));

		$this->tpl->setVariable("COLUMN_COUNT", 1);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		

		// sorting array
		$term_list = array_slice($term_list, $_GET["offset"], $_GET["limit"]);

		// render table
		$tbl->render();

		if (count($term_list) > 0)
		{
			$i=1;
			foreach($term_list as $key => $term)
			{
				$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
				
				$sep = ": ";
				for($j=0; $j<count($defs); $j++)
				{
					$def = $defs[$j];

					// edit
					$this->tpl->setCurrentBlock("definition");
					$this->tpl->setVariable("SEP", $sep);
					$this->ctrl->setParameterByClass("ilpageobjectgui", "term_id", $term["id"]);
					$this->ctrl->setParameterByClass("ilpageobjectgui", "def", $def["id"]);
					$this->tpl->setVariable("LINK_EDIT_DEF",
						$this->ctrl->getLinkTargetByClass(array("ilglossarytermgui",
						"iltermdefinitioneditorgui",
						"ilpageobjectgui"), "edit"));
					$this->tpl->setVariable("TEXT_DEF", $this->lng->txt("glo_definition_abbr").($j+1));
					$this->tpl->parseCurrentBlock();
					$sep = ", ";
				}

				$this->tpl->setCurrentBlock("tbl_content");
				$css_row = ilUtil::switchColor(++$i,"tblrow1","tblrow2");

				// edit term link
				$this->tpl->setVariable("TEXT_TERM", $term["term"]);
				$this->ctrl->setParameter($this, "term_id", $term["id"]);
				$this->tpl->setVariable("LINK_EDIT_TERM",
					$this->ctrl->getLinkTargetByClass("ilglossarytermgui", "editTerm"));
					
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			//$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			//$this->tpl->parseCurrentBlock();
		}
	}
	

	/**
	* list terms
	*/
	function listTerms()
	{
		global $ilUser, $ilToolbar, $lng, $ilCtrl, $tpl;

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

		include_once("./Modules/Glossary/classes/class.ilTermListTableGUI.php");
		$tab = new ilTermListTableGUI($this, "listTerms");
		$tpl->setContent($tab->getHTML());
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
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
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

		$this->ctrl->setParameterByClass("ilpageobjectgui", "term_id", $term->getId());
		$this->ctrl->setParameterByClass("ilpageobjectgui", "def", $def->getId());
		$this->ctrl->redirectByClass(array("ilglossarytermgui",
			"iltermdefinitioneditorgui", "ilpageobjectgui"), "edit");
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

		$term = new ilGlossaryTerm($_GET["term_id"]);
		
		$add = "";
		$nr = ilGlossaryTerm::getNumberOfUsages($_GET["term_id"]);
		if ($nr > 0)
		{
			$ilCtrl->setParameterByClass("ilglossarytermgui",
				"term_id", $_GET["term_id"]);
			$link = "[<a href='".
				$ilCtrl->getLinkTargetByClass("ilglossarytermgui", "listUsages").
				"'>".$lng->txt("glo_list_usages")."</a>]";
			$add = "<br/>".sprintf($lng->txt("glo_term_is_used_n_times"), $nr)." ".$link;
		}
		

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();


		//$this->tpl->setVariable("HEADER",
		//	$this->lng->txt("cont_term").": ".$term->getTerm());

		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_delete.html", true);
		ilUtil::sendQuestion($this->lng->txt("info_delete_sure").$add);

		$this->tpl->setVariable("TXT_TERM", $term->getTerm());

		$definition =& new ilGlossaryDefinition($_GET["def"]);
		//$page =& new ilPageObject("gdf", $definition->getId());
		$page_gui =& new ilPageObjectGUI("gdf", $definition->getId());
		$page_gui->setTemplateOutput(false);
		$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
		$output = $page_gui->preview();

		$this->tpl->setCurrentBlock("definition");
		$this->tpl->setVariable("PAGE_CONTENT", $output);
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("LINK_CANCEL",
			$this->ctrl->getLinkTarget($this, "cancelDefinitionDeletion"));
		$this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt("confirm"));
		$this->ctrl->setParameter($this, "def", $definition->getId());
		$this->tpl->setVariable("LINK_CONFIRM",
			$this->ctrl->getLinkTarget($this, "deleteDefinition"));
		$this->tpl->parseCurrentBlock();
	}
	
	function cancelDefinitionDeletion()
	{
		$this->ctrl->redirect($this, "listTerms");
	}


	function deleteDefinition()
	{
		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$definition->delete();
		$this->ctrl->redirect($this, "listTerms");
	}

	/**
	* edit term
	*/
	function editTerm()
	{
		/*
		$term = new ilGlossaryTerm($_GET["term_id"]);
		//$this->tpl->setVariable("HEADER", $this->lng->txt("cont_term").": ".$term->getTerm());

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_term_edit.html", true);
		$this->ctrl->setParameter($this, "term_id", $_GET["term_id"]);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_term"));
		$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
		$this->tpl->setVariable("INPUT_TERM", "term");
		$this->tpl->setVariable("VALUE_TERM", htmlspecialchars($term->getTerm()));
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		$lang = ilMDLanguageItem::_getLanguages();
		$select_language = ilUtil::formSelect ($term->getLanguage(),"term_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("BTN_NAME", "updateTerm");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		*/
	}


	/**
	* update term
	*/
	function updateTerm()
	{
		$term = new ilGlossaryTerm($_GET["term_id"]);

		$term->setTerm(ilUtil::stripSlashes($_POST["term"]));
		$term->setLanguage($_POST["term_language"]);
		$term->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this, "listTerms");
	}


	/*
	* list all export files
	*/
	function exportList()
	{
		global $tree;

		//$this->setTabs();

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// create export file button (xml)
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "export"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_create_export_file_xml"));
		$this->tpl->parseCurrentBlock();

		// create export file button (html)
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportHTML"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_create_export_file_html"));
		$this->tpl->parseCurrentBlock();

		// view last export log button
		if (is_file($this->object->getExportDirectory()."/export.log"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "viewExportLog"));
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_view_last_export_log"));
			$this->tpl->parseCurrentBlock();
		}


		$export_dir = $this->object->getExportDirectory();

		$export_files = $this->object->getExportFiles();
		
		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.glo_export_file_row.html", true);

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($this->lng->txt("cont_export_files"));

		$tbl->setHeaderNames(array("", $this->lng->txt("type"),
			$this->lng->txt("cont_file"),
			$this->lng->txt("cont_size"), $this->lng->txt("date") ));

		$cols = array("", "type", "file", "size", "date");
		$header_params = array("ref_id" => $_GET["ref_id"],
			"cmd" => "exportList", "cmdClass" => get_class($this));
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "9%", "40%", "25%", "25%"));
		$tbl->disable("sort");

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		$this->tpl->setVariable("COLUMN_COUNTS", 5);

		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "downloadExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
		$this->tpl->parseCurrentBlock();

		// public access
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "publishExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cont_public_access"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", $exp_file["size"]);

				$public_str = ($exp_file["file"] == $this->object->getPublicExportFile($exp_file["type"]))
					? " <b>(".$this->lng->txt("public").")<b>"
					: "";
				$this->tpl->setVariable("TXT_TYPE", $exp_file["type"].$public_str);
				$this->tpl->setVariable("CHECKBOX_ID", $exp_file["type"].":".$exp_file["file"]);

				$file_arr = explode("__", $exp_file["file"]);
				$this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			//$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 3);
			//$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}


	/**
	* export content object
	*/
	function export()
	{
		require_once("./Modules/Glossary/classes/class.ilGlossaryExport.php");
		$glo_exp = new ilGlossaryExport($this->object);
		$glo_exp->buildExportFile();
		$this->ctrl->redirect($this, "exportList");
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
		$this->ctrl->redirect($this, "exportList");
	}


	/**
	* download export file
	*/
	function downloadExportFile()
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
		ilUtil::deliverFile($export_dir."/".$file[1],
			$file[1]);
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
		$this->ctrl->redirect($this, "exportList");
	}

	/*
	* list all export files
	*/
	function viewExportLog()
	{
		global $tree;

		$this->setTabs();

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// create export file button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportList"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_export_files"));
		$this->tpl->parseCurrentBlock();

		// load files templates
		$this->tpl->setVariable("ADM_CONTENT",
			nl2br(file_get_contents($this->object->getExportDirectory()."/export.log")));

		$this->tpl->parseCurrentBlock();
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFile()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$this->setTabs();

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Glossary");

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["file"] as $file)
		{
				$file = explode(":", $file);
				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
				$this->tpl->setVariable("TEXT_CONTENT", $file[1]." (".$file[0].")");
				$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelDeleteExportFile"  => $this->lng->txt("cancel"),
			"deleteExportFile"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFile()
	{
		session_unregister("ilExportFiles");
		$this->ctrl->redirect($this, "exportList");
	}

	/**
	* delete export files
	*/
	function deleteExportFile()
	{
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$file = explode(":", $file);
			$export_dir = $this->object->getExportDirectory($file[0]);
			
			$exp_file = $export_dir."/".$file[1];
			$exp_dir = $export_dir."/".substr($file, 0, strlen($file) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
		}
		$this->ctrl->redirect($this, "exportList");
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
		
		if (count($_POST["id"]) < 1)
		{
			ilUtil::sendFailure($this->lng->txt("cont_select_term"), true);
			$ilCtrl->redirect($this, "listTerms");
		}

		if (count($_POST["id"]) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("cont_select_max_one_term"), true);
			$ilCtrl->redirect($this, "listTerms");
		}

		// add term
		include_once ("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$term =& new ilGlossaryTerm($_POST["id"][0]);

		// add first definition
		$def =& new ilGlossaryDefinition();
		$def->setTermId($term->getId());
		$def->setTitle(ilUtil::stripSlashes($term->getTerm()));
		$def->create();

		$this->ctrl->setParameterByClass("ilpageobjectgui", "term_id", $term->getId());
		$this->ctrl->setParameterByClass("ilpageobjectgui", "def", $def->getId());
		$this->ctrl->redirectByClass(array("ilglossarytermgui",
			"iltermdefinitioneditorgui", "ilpageobjectgui"), "edit");
		
	}

	function getTemplate()
	{
		$this->tpl->getStandardTemplate();

		$title = $this->object->getTitle();


		if ($_GET["term_id"] > 0)
		{
			$this->tpl->setTitle($this->lng->txt("term").": ".
				ilGlossaryTerm::_lookGlossaryTerm($_GET["term_id"]));
		}
		else
		{
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo_b.gif"));
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
		global $rbacsystem;

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
		$tabs_gui->addTarget("settings",
			$this->ctrl->getLinkTarget($this, "properties"), "properties",
			get_class($this));

		// meta data
		$tabs_gui->addTarget("meta_data",
			 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
			 "", "ilmdeditorgui");

		// export
		$tabs_gui->addTarget("export",
			 $this->ctrl->getLinkTarget($this, "exportList"),
			 array("exportList", "viewExportLog"), get_class($this));

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
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target)
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
		global $ilTabs;

		include_once("./Modules/Glossary/classes/class.ilTermListTableGUI.php");
		$prtab = new ilTermListTableGUI($this, "listTerms");
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
		$prtab = new ilTermListTableGUI($this, "listTerms");
		$prtab->resetOffset();
		$prtab->resetFilter();
		$this->listTerms();
	}


}

?>
