<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");

/**
* GUI class for glossary terms
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryTermGUI: ilTermDefinitionEditorGUI, ilPageObjectGUI
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


	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
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
		global $ilUser;

		$this->getTemplate();
		$this->displayLocator();
		$this->tpl->setVariable("HEADER", $this->lng->txt("cont_new_term"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_term_b.gif"));
		$this->setTabs();

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_term_new.html", true);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_new_term"));
		$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
		$this->tpl->setVariable("INPUT_TERM", "term");
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		$lang = ilMDLanguageItem::_getLanguages();

		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}

		$select_language = ilUtil::formSelect ($s_lang, "term_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("BTN_NAME", "saveTerm");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
	}

	/**
	* save term
	*/
	function saveTerm()
	{
		$term =& new ilGlossaryTerm();
		$term->setGlossary($this->glossary);
		$term->setTerm(ilUtil::stripSlashes($_POST["term"]));
		$term->setLanguage($_POST["term_language"]);
		$_SESSION["il_text_lang_".$_GET["ref_id"]] = $_POST["term_language"];
		$term->create();

		ilUtil::sendInfo($this->lng->txt("cont_added_term"),true);
		$this->ctrl->returnToParent($this);
	}


	/**
	* edit term
	*/
	function editTerm()
	{
		//$this->displayLocator();
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		//$this->displayLocator();
		$this->tpl->setVariable("HEADER", $this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_term_b.gif"));

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_term_edit.html", true);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "updateTerm"));
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_term"));
		$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
		$this->tpl->setVariable("INPUT_TERM", "term");
		$this->tpl->setVariable("VALUE_TERM", htmlspecialchars($this->term->getTerm()));
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		$lang = ilMDLanguageItem::_getLanguages();
		$select_language = ilUtil::formSelect ($this->term->getLanguage(),"term_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("BTN_NAME", "updateTerm");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
	}


	/**
	* update term
	*/
	function updateTerm()
	{
		$this->term->setTerm(ilUtil::stripSlashes($_POST["term"]));
		$this->term->setLanguage($_POST["term_language"]);
		$this->term->update();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this, "editTerm");
	}

	/**
	* output glossary term definitions
	*
	* used in ilLMPresentationGUI->ilGlossary()
	*/
	function output($a_offline = false)
	{
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

		$defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

		$this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page_gui = new ilPageObjectGUI("gdf", $def["id"]);
			$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
			if (!$a_offline)
			{
				$page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=fullscreen&amp;ref_id=".$_GET["ref_id"]);
			}
			else
			{
				$page_gui->setFullscreenLink("fullscreen.html");	// id is set by xslt
			}
			$page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=".$_GET["ref_id"]);

			if (!$a_offline)
			{
				$page_gui->setOutputMode("presentation");
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
				$this->tpl->setCurrentBlock("definition_header");
						$this->tpl->setVariable("TXT_DEFINITION",
				$this->lng->txt("cont_definition")." ".($j+1));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* get internal links
	*/
	function getInternalLinks()
	{
		require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

		$defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

		$term_links = array();
		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page = new ilPageObject("gdf", $def["id"]);
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
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();

		require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

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

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		//$this->tpl->addBlockfile("CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		//ilUtil::sendInfo();
		$this->tpl->addBlockfile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->setVariable("HEADER",
			$this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_term_b.gif"));

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
			$page_gui = new ilPageObjectGUI("gdf", $def["id"]);
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
				$this->ctrl->getLinkTargetByClass(array("ilTermDefinitionEditorGUI", "ilPageObjectGUI"), "edit"));
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

	}


	/**
	* deletion confirmation screen
	*/
	function confirmDefinitionDeletion()
	{
		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();

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

		$this->tpl->setVariable("HEADER",
			$this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_term_b.gif"));

		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_delete.html", true);
		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

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

		$this->getTemplate();
		$this->displayLocator();
		$this->setTabs();
		$this->tpl->setVariable("HEADER", $this->lng->txt("cont_term").": ".$this->term->getTerm());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_term_b.gif"));

		$term_id = $_GET["term_id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_edit.html");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "saveDefinition"));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("gdf_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("gdf_add"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveDefinition");
		//$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* cancel adding definition
	*/
	function cancel()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "listDefinitions");
	}

	/**
	* save definition
	*/
	function saveDefinition()
	{
		$def =& new ilGlossaryDefinition();
		$def->setTermId($_GET["term_id"]);
		$def->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));#"content object ".$newObj->getId());		// set by meta_gui->save
		$def->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));	// set by meta_gui->save
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
		ilUtil::sendInfo();
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs;

		// catch feedback message
		#include_once("classes/class.ilTabsGUI.php");
		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($ilTabs);

		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

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
//echo ":".$_GET["term_id"].":";
		if ($_GET["term_id"] != "")
		{
			$tabs_gui->addTarget("properties",
				$this->ctrl->getLinkTarget($this, "editTerm"), array("editTerm"),
				get_class($this));
				
			$tabs_gui->addTarget("cont_definitions",
				$this->ctrl->getLinkTarget($this, "listDefinitions"),
				"listDefinitions",
				get_class($this));		
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
	function _goto($a_target, $a_ref_id = "")
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
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle($glo_id)), true);
			include("repository.php");
			exit;
		}


		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

}

?>
