<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");

/**
* GUI class for glossary terms
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryTermGUI: ilTermDefinitionEditorGUI, ilGlossaryDefPageGUI, ilPropertyFormGUI
*
* @ingroup ModulesGlossary
*/
class ilGlossaryTermGUI
{
	var $ilias;
	var $lng;
	var $tpl;
	var $glossary;
	var $term;
	var $link_xml;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryTermGUI($a_id = 0)
	{
		global $lng, $ilias, $tpl, $ilCtrl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("term_id"));

		if($a_id != 0)
		{
			$this->term =& new ilGlossaryTerm($a_id);
		}
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{

			case "iltermdefinitioneditorgui":
				//$this->ctrl->setReturn($this, "listDefinitions");
				$def_edit =& new ilTermDefinitionEditorGUI();
				//$ret =& $def_edit->executeCommand();
				$ret =& $this->ctrl->forwardCommand($def_edit);
				$this->quickList("edit", $def_edit);
				break;

			case "ilpropertyformgui";
				$form = $this->getEditTermForm();
				$this->ctrl->forwardCommand($form);
				break;
				
			default:
				$ret =& $this->$cmd();
				break;
		}
	}

	/**
	 * set offline directory to offdir
	 *
	 * @param offdir contains diretory where to store files
	 */
	function setOfflineDirectory ($offdir) {
		$this->offline_directory = $offdir;
	}


	/**
	 * get offline directory
	 * @return directory where to store offline files
	 */
	function getOfflineDirectory () {
		return $this->offline_directory;
	}


	function setGlossary($a_glossary)
	{
		$this->glossary = $a_glossary;
	}

	function setLinkXML($a_link_xml)
	{
		$this->link_xml = $a_link_xml;
	}

	function getLinkXML()
	{
		return $this->link_xml;
	}

	/**
	* form for new content object creation
	*/
	function create()
	{		
		// deprecated
	}

	/**
	* save term
	*/
	function saveTerm()
	{
		// deprecated
	}


	/**
	 * Edit term
	 */
	function editTerm()
	{
		global $ilTabs, $ilCtrl;

		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		$ilTabs->activateTab("properties");
		
		$this->tpl->setTitle($this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

		$form = $this->getEditTermForm();
		
		$this->tpl->setContent($ilCtrl->getHTML($form));

		$this->quickList();
	}
	
	/**
	 * Get edit term form
	 *
	 * @param
	 * @return
	 */
	function getEditTermForm()
	{
		global $ilTabs, $ilCtrl;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "updateTerm"));
		$form->setTitle($this->lng->txt("cont_edit_term"));
		
		$term = new ilTextInputGUI($this->lng->txt("cont_term"), "term");
		$term->setRequired(true);
		$term->setValue($this->term->getTerm());
		$form->addItem($term);
		
		$lang = new ilSelectInputGUI($this->lng->txt("language"), "term_language");		
		$lang->setRequired(true);
		$lang->setOptions(ilMDLanguageItem::_getLanguages());
		$lang->setValue($this->term->getLanguage());
		$form->addItem($lang);
		
		// taxonomy
		if ($this->glossary->getTaxonomyId() > 0)
		{
			include_once("./Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php");
			$tax_node_assign = new ilTaxSelectInputGUI($this->glossary->getTaxonomyId(), "tax_node", true);
			
			include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
			$ta = new ilTaxNodeAssignment("glo", $this->glossary->getId(), "term", $this->glossary->getTaxonomyId());
			$assgnmts = $ta->getAssignmentsOfItem($this->term->getId());
			$node_ids = array();
			foreach ($assgnmts as $a)
			{
				$node_ids[] = $a["node_id"];
			}
			$tax_node_assign->setValue($node_ids);
			
			$form->addItem($tax_node_assign);
			
		}

		// advanced metadata
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,'glo',$this->glossary->getId(),'term',
			$this->term->getId());
		$this->record_gui->setPropertyForm($form);
		$this->record_gui->setSelectedOnly(true);
		$this->record_gui->parse();
		
		$form->addCommandButton("updateTerm", $this->lng->txt("save"));

		return $form;
	}
	


	/**
	* update term
	*/
	function updateTerm()
	{
		// update term
		$this->term->setTerm(ilUtil::stripSlashes($_POST["term"]));
		$this->term->setLanguage($_POST["term_language"]);
		$this->term->update();
		
		// update taxonomy assignment
		if ($this->glossary->getTaxonomyId() > 0)
		{
			include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
			$ta = new ilTaxNodeAssignment("glo", $this->glossary->getId(), "term", $this->glossary->getTaxonomyId());
			$ta->deleteAssignmentsOfItem($this->term->getId());
			if (is_array($_POST["tax_node"]))
			{
				foreach ($_POST["tax_node"] as $node_id)
				{
					$ta->addAssignment($node_id, $this->term->getId());
				}
			}		

		}
		
		// advanced metadata
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,
			'glo',$this->glossary->getId(),'term', $this->term->getId());
		
		// :TODO: proper validation
		$form = $this->getEditTermForm();
		$form->checkInput();
		
		if($this->record_gui->importEditFormPostValues())
		{		
			$this->record_gui->writeEditForm();
		}
	
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this, "editTerm");
	}

	/**
	 * Get overlay html
	 *
	 * @param
	 * @return
	 */
	function getOverlayHTML($a_close_el_id, $a_glo_ov_id = "", $a_lang = "", $a_outputmode = "offline")
	{
		global $lng;
		
		if ($a_lang == "")
		{
			$a_lang = $lng->getLangKey();
		}

		$tpl = new ilTemplate("tpl.glossary_overlay.html", true, true, "Modules/Glossary");
//		$this->output(true, $tpl);
		if ($a_outputmode == "preview")
		{
			$a_outputmode = "presentation";
		}
		if ($a_outputmode == "offline")
		{
			$this->output(true, $tpl, $a_outputmode);
		}
		else
		{
			$this->output(false, $tpl, $a_outputmode);
		}
		if ($a_glo_ov_id != "")
		{
			$tpl->setCurrentBlock("glovlink");
			$tpl->setVariable("TXT_LINK", $lng->txtlng("content", "cont_sco_glossary", $a_lang));
			$tpl->setVariable("ID_LINK", $a_glo_ov_id);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_CLOSE", $lng->txtlng("common", "close", $a_lang));
		$tpl->setVariable("ID_CLOSE", $a_close_el_id);
		return $tpl->get(); 
	}
	
	/**
	 * output glossary term definitions
	 *
	 * used in ilLMPresentationGUI->ilGlossary()
	 */
	function output($a_offline = false, $a_tpl = "", $a_outputmode = "presentation")
	{
		if ($a_tpl != "")
		{
			$tpl = $a_tpl;
		}
		else
		{
			$tpl = $this->tpl;
		}
		
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");

		$defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

		$tpl->setVariable("TXT_TERM", $this->term->getTerm());

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page_gui = new ilGlossaryDefPageGUI($def["id"]);
			$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
			if (!$a_offline)
			{
				//$page_gui->setFullscreenLink(
				//	"ilias.php?baseClass=ilGlossaryPresentationGUI&cmd=fullscreen&ref_id=".$_GET["ref_id"]);
			}
			else
			{
				$page_gui->setFullscreenLink("fullscreen.html");	// id is set by xslt
			}
			$page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=".$_GET["ref_id"]);

			if (!$a_offline)
			{
				$page_gui->setOutputMode($a_outputmode);
			}
			else
			{
				$page_gui->setOutputMode("offline");
				$page_gui->setOfflineDirectory($this->getOfflineDirectory());
			}

			//$page_gui->setOutputMode("edit");
			//$page_gui->setPresentationTitle($this->term->getTerm());
			$page_gui->setLinkXML($this->getLinkXML());
			$page_gui->setTemplateOutput(false);
			$output = $page_gui->presentation($page_gui->getOutputMode());

			if (count($defs) > 1)
			{
				$tpl->setCurrentBlock("definition_header");
						$tpl->setVariable("TXT_DEFINITION",
				$this->lng->txt("cont_definition")." ".($j+1));
				$tpl->parseCurrentBlock();
			}

			ilUtil::includeMathjax($tpl);

			$tpl->setCurrentBlock("definition");
			$tpl->setVariable("PAGE_CONTENT", $output);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	* get internal links
	*/
	function getInternalLinks()
	{
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");

		$defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

		$term_links = array();
		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page = new ilGlossaryDefPage($def["id"]);
			$page->buildDom();
			$page_links = $page->getInternalLinks();
			foreach($page_links as $key => $page_link)
			{
				$term_links[$key] = $page_link;
			}
		}

		return $term_links;
	}

	/**
	* list definitions
	*/
	function listDefinitions()
	{
		global $ilTabs;
		
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		$ilTabs->activateTab("definitions");
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefPageGUI.php");

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		//$this->tpl->addBlockfile("CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		//ilUtil::sendInfo();
		$this->tpl->addBlockfile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->setTitle(
			$this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setCurrentBlock("add_def");
		$this->tpl->setVariable("TXT_ADD_DEFINITION",
			$this->lng->txt("cont_add_definition"));
		$this->tpl->setVariable("BTN_ADD", "addDefinition");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("def_list");

		$defs = ilGlossaryDefinition::getDefinitionList($_GET["term_id"]);

		$this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page_gui = new ilGlossaryDefPageGUI($def["id"]);
			$page_gui->setStyleId($this->glossary->getStyleSheetId());
			$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
			$page_gui->setTemplateOutput(false);
			$output = $page_gui->preview();

			if (count($defs) > 1)
			{
				$this->tpl->setCurrentBlock("definition_header");
						$this->tpl->setVariable("TXT_DEFINITION",
				$this->lng->txt("cont_definition")." ".($j+1));
				$this->tpl->parseCurrentBlock();
			}

			if ($j > 0)
			{
				$this->tpl->setCurrentBlock("up");
				$this->tpl->setVariable("TXT_UP", $this->lng->txt("up"));
				$this->ctrl->setParameter($this, "def", $def["id"]);
				$this->tpl->setVariable("LINK_UP",
					$this->ctrl->getLinkTarget($this, "moveUp"));
				$this->tpl->parseCurrentBlock();
			}

			if ($j+1 < count($defs))
			{
				$this->tpl->setCurrentBlock("down");
				$this->tpl->setVariable("TXT_DOWN", $this->lng->txt("down"));
				$this->ctrl->setParameter($this, "def", $def["id"]);
				$this->tpl->setVariable("LINK_DOWN",
					$this->ctrl->getLinkTarget($this, "moveDown"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("submit_btns");
			$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->ctrl->setParameter($this, "def", $def["id"]);
			$this->ctrl->setParameterByClass("ilTermDefinitionEditorGUI", "def", $def["id"]);
			$this->tpl->setVariable("LINK_EDIT",
				$this->ctrl->getLinkTargetByClass(array("ilTermDefinitionEditorGUI", "ilGlossaryDefPageGUI"), "edit"));
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("LINK_DELETE",
				$this->ctrl->getLinkTarget($this, "confirmDefinitionDeletion"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			$this->tpl->parseCurrentBlock();
		}
		//$this->tpl->setCurrentBlock("def_list");
		//$this->tpl->parseCurrentBlock();

		$this->quickList();
	}


	/**
	* deletion confirmation screen
	*/
	function confirmDefinitionDeletion()
	{
		global $ilTabs;
		
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		$ilTabs->activateTab("definitions");

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setTitle(
			$this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));

		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_delete.html", true);
		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$page_gui = new ilGlossaryDefPageGUI($definition->getId());
		$page_gui->setTemplateOutput(false);
		$page_gui->setStyleId($this->glossary->getStyleSheetId());
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
		$this->ctrl->redirect($this, "listDefinitions");
	}


	function deleteDefinition()
	{
		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$definition->delete();
		$this->ctrl->redirect($this, "listDefinitions");
	}


	/**
	* move definition upwards
	*/
	function moveUp()
	{
		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$definition->moveUp();
		$this->ctrl->redirect($this, "listDefinitions");
	}


	/**
	* move definition downwards
	*/
	function moveDown()
	{
		$definition =& new ilGlossaryDefinition($_GET["def"]);
		$definition->moveDown();
		$this->ctrl->redirect($this, "listDefinitions");
	}


	/**
	* add definition
	*/
	function addDefinition()
	{
		global $ilTabs;
		
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		$ilTabs->activateTab("definitions");
		
		$this->tpl->setTitle($this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "saveDefinition"));
		$form->setTitle($this->lng->txt("gdf_new"));
		
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$form->addItem($desc);
		
		$form->addCommandButton("saveDefinition", $this->lng->txt("gdf_add"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		
		$this->tpl->setContent($form->getHTML());
	}

	/**
	* cancel adding definition
	*/
	function cancel()
	{
		$this->ctrl->redirect($this, "listDefinitions");
	}

	/**
	* save definition
	*/
	function saveDefinition()
	{
		$def =& new ilGlossaryDefinition();
		$def->setTermId($_GET["term_id"]);
		$def->setTitle(ilUtil::stripSlashes($_POST["title"]));#"content object ".$newObj->getId());		// set by meta_gui->save
		$def->setDescription(ilUtil::stripSlashes($_POST["desc"]));	// set by meta_gui->save
		$def->create();

		$this->ctrl->redirect($this, "listDefinitions");
	}


	/**
	* get template
	*/
	function getTemplate()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs;
		$this->getTabs($ilTabs);
	}

	/**
	* display locator
	*/
	function displayLocator()
	{
		require_once ("./Modules/Glossary/classes/class.ilGlossaryLocatorGUI.php");
		$gloss_loc =& new ilGlossaryLocatorGUI();
		$gloss_loc->setTerm($this->term);
		$gloss_loc->setGlossary($this->glossary);
		//$gloss_loc->setDefinition($this->definition);
		$gloss_loc->display();
	}


	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $lng, $ilHelp;
		
		
		$ilHelp->setScreenIdComponent("glo_term");
		
//echo ":".$_GET["term_id"].":";
		if ($_GET["term_id"] != "")
		{
			$tabs_gui->addTab("properties",
				$lng->txt("term"),
				$this->ctrl->getLinkTarget($this, "editTerm"));

			$tabs_gui->addTab("definitions",
				$lng->txt("cont_definitions"),
				$this->ctrl->getLinkTarget($this, "listDefinitions"));

			$tabs_gui->addTab("usage",
				$lng->txt("cont_usage")." (".ilGlossaryTerm::getNumberOfUsages($_GET["term_id"]).")",
				$this->ctrl->getLinkTarget($this, "listUsages"));
			
			$tabs_gui->addNonTabbedLink("presentation_view",
				$this->lng->txt("glo_presentation_view"),
				ILIAS_HTTP_PATH.
				"/goto.php?target=".
				"git".
				"_".$_GET["term_id"]."_".$_GET["ref_id"]."&client_id=".CLIENT_ID,
				"_top"
		);

		}

		// back to glossary
		$tabs_gui->setBackTarget($this->lng->txt("glossary"),
			$this->ctrl->getLinkTargetByClass("ilobjglossarygui", "listTerms"));
						
	}

	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	public static function _goto($a_target, $a_ref_id = "")
	{
		global $rbacsystem, $ilErr, $lng, $ilAccess;

		$glo_id = ilGlossaryTerm::_lookGlossaryID($a_target);//::_lookupContObjID($a_target);
		
		// get all references
		if ($a_ref_id > 0)
		{
			$ref_ids = array($a_ref_id);
		}
		else
		{
			$ref_ids = ilObject::_getAllReferences($glo_id);
		}

		// check read permissions
		foreach ($ref_ids as $ref_id)
		{
			// Permission check
			if ($ilAccess->checkAccess("read", "", $ref_id))
			{
				$_GET["baseClass"] = "ilGlossaryPresentationGUI";
				$_GET["term_id"] = $a_target;
				$_GET["ref_id"] = $ref_id;
				$_GET["cmd"] = "listDefinitions";
				include_once("ilias.php");
				exit;
			}
		}
		if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle($glo_id)), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}


		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

	/**
	 * List usage
	 */
	function listUsages()
	{
		global $ilTabs, $tpl;

		//$this->displayLocator();
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		$ilTabs->activateTab("usage");
		
		$this->tpl->setTitle($this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo.svg"));
		
		include_once("./Modules/Glossary/classes/class.ilTermUsagesTableGUI.php");
		$tab = new ilTermUsagesTableGUI($this, "listUsages", $_GET["term_id"]);
		
		$tpl->setContent($tab->getHTML());
		
		$this->quickList();
	}
	
	/**
	 * Set quick term list cmd into left navigation URL
	 */
	function quickList()
	{
		global $tpl, $ilCtrl;
		
		//$tpl->setLeftNavUrl($ilCtrl->getLinkTarget($this, "showQuickList"));
		
		include_once("./Modules/Glossary/classes/class.ilTermQuickListTableGUI.php");
		$tab = new ilTermQuickListTableGUI($this, "editTerm");
		$tpl->setLeftNavContent($tab->getHTML());
	}
}

?>
