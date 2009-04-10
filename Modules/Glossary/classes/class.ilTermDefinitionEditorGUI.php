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

require_once("./Services/Style/classes/class.ilObjStyleSheet.php");
require_once ("./Services/COPage/classes/class.ilPageObjectGUI.php");

/**
* GUI class for glossary term definition editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilTermDefinitionEditorGUI: ilPageObjectGUI, ilMDEditorGUI
*
* @ingroup ModulesGlossary
*/
class ilTermDefinitionEditorGUI
{
	var $ilias;
	var $tpl;
	var $lng;
	var $glossary;
	var $definition;
	var $term;

	/**
	* Constructor
	* @access	public
	*/
	function ilTermDefinitionEditorGUI()
	{
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		$this->glossary =& new ilObjGlossary($_GET["ref_id"], true);
		$this->definition =& new ilGlossaryDefinition($_GET["def"]);
		$this->term =& new ilGlossaryTerm($this->definition->getTermId());

		$this->ctrl->saveParameter($this, array("def"));
	}


	function &executeCommand()
	{
		global $tpl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();


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

		require_once ("./Modules/Glossary/classes/class.ilGlossaryLocatorGUI.php");
		$gloss_loc =& new ilGlossaryLocatorGUI();
		$gloss_loc->setTerm($this->term);
		$gloss_loc->setGlossary($this->glossary);
		$gloss_loc->setDefinition($this->definition);

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->setTitle($this->term->getTerm()." - ".
			$this->lng->txt("cont_definition")." ".
			$this->definition->getNr());
		if ($this->ctrl->getCmdClass() == "ilpageobjectgui")
		{
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_def_b.gif"));
		}

		switch ($next_class)
		{

			case "ilpageobjectgui":
			
				// not so nice, to do: revise locator handling
				if ($this->ctrl->getCmdClass() == "ilpageobjectgui"
					|| $this->ctrl->getCmdClass() == "ileditclipboardgui")
				{
					$gloss_loc->display();
				}
				$this->setTabs();
				$this->ctrl->setReturnByClass("ilPageObjectGUI", "edit");
				$this->ctrl->setReturn($this, "listDefinitions");
				$page_gui =& new ilPageObjectGUI("gdf", $this->definition->getId());
				$page = $page_gui->getPageObject();
				$this->definition->assignPageObject($page);
				$page->addUpdateListener($this, "saveShortText");
				$page_gui->setEditPreview(true);
				$page_gui->activateMetaDataEditor($this->glossary->getId(),
					$this->definition->getId(), "gdf");
				//	$this->obj, "MDUpdateListener");
				
				$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$_GET["ref_id"]);
				$page_gui->setFullscreenLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=fullscreen&amp;ref_id=".$_GET["ref_id"]);
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->setOutputMode("edit");
				$page_gui->setLocator($gloss_loc);
				$page_gui->setIntLinkHelpDefault("GlossaryItem", $_GET["ref_id"]);
				$page_gui->setIntLinkReturn($this->ctrl->getLinkTargetByClass("ilobjglossarygui", "quickList"));
				$page_gui->setPageBackTitle($this->lng->txt("cont_definition"));
				$page_gui->setLinkParams("ref_id=".$_GET["ref_id"]);
				$page_gui->setHeader($this->term->getTerm());
				$page_gui->setFileDownloadLink("ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=downloadFile&amp;ref_id=".$_GET["ref_id"]);
				$page_gui->setPresentationTitle($this->term->getTerm());
				$ret =& $this->ctrl->forwardCommand($page_gui);
				$tpl->setContent($ret);
				break;

			default:
				$this->setTabs();
				$gloss_loc->display();
				$ret =& $this->$cmd();
				break;

		}
	}


	/**
	* output main header (title and locator)
	*/
	function main_header($a_header_title)
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->setVariable("HEADER", $a_header_title);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->displayLocator();
		//$this->setAdminTabs($a_type);
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs;

		// catch feedback message
		$this->getTabs($ilTabs);
	}

	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		// back to glossary
		$tabs_gui->setBack2Target($this->lng->txt("glossary"),
			$this->ctrl->getParentReturn($this));

		// back to upper context
		$tabs_gui->setBackTarget($this->lng->txt("term"),
			$this->ctrl->getLinkTargetByClass("ilglossarytermgui", "editTerm"));

	}


	function saveShortText()
	{
		$this->definition->updateShortText();
	}
}
?>
