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

require_once("classes/class.ilObjStyleSheet.php");

/**
* GUI class for glossary term definition editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
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
		global $ilias, $tpl, $lng, $objDefinition;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->glossary =& new ilObjGlossary($_GET["ref_id"], true);
		$this->definition =& new ilGlossaryDefinition($_GET["def"]);
		$this->term =& new ilGlossaryTerm($this->definition->getTermId());
	}

	function executeCommand()
	{
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		//$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));

		$this->main_header($this->lng->txt("cont_term").": ".$this->term->getTerm().", ".
			$this->lng->txt("cont_definition")." ".$this->definition->getNr());

		require_once ("content/classes/Pages/class.ilPageObjectGUI.php");
		$page =& $this->definition->getPageObject();
		$page->addUpdateListener($this, "saveShortText");
		$page_gui =& new ilPageObjectGUI($page);
		$page_gui->setTemplateTargetVar("ADM_CONTENT");
		$page_gui->setOutputMode("edit");
		$page_gui->setPresentationTitle($this->term->getTerm());
		$page_gui->setTargetScript("glossary_edit.php?ref_id=".
			$this->glossary->getRefId()."&def=".$this->definition->getId()."&mode=page_edit");
		$page_gui->setReturnLocation("glossary_edit.php?ref_id=".
			$this->glossary->getRefId()."&def=".$this->definition->getId()."&cmd=view");

		if($_GET["mode"] == "page_edit")
		{
			$page_gui->showPageEditor();
		}
		else
		{
			$cmd = $_GET["cmd"];
			$this->setAdminTabs();
//echo "cmd:".$_GET["cmd"].":<br>";
			switch ($cmd)
			{
				case "editDefinitionMetaData":
					$this->editMeta();
					break;

				case "saveDefinitionMetaData":
					$this->saveMeta();
					break;

				case "confirmDefinitionDeletion":
				case "cancelDefinitionDeletion":
				case "deleteDefinition":
				case "moveDown":
				case "moveUp":
					$this->$cmd();
					break;

				default:
					$page_gui->$cmd();
			}
		}

	}


	function confirmDefinitionDeletion()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_delete.html", true);
		sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("TXT_TERM", $this->term->getTerm());

		$page =& new ilPageObject("gdf", $this->definition->getId());
		$page_gui =& new ilPageObjectGUI($page);
		$page_gui->setTemplateOutput(false);
		$output = $page_gui->preview();

		$this->tpl->setCurrentBlock("definition");
		$this->tpl->setVariable("PAGE_CONTENT", $output);
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("LINK_CANCEL",
			"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=cancelDefinitionDeletion&def=".$this->definition->getId());
		$this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("LINK_CONFIRM",
			"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=deleteDefinition&def=".$this->definition->getId());
		$this->tpl->parseCurrentBlock();
	}

	function cancelDefinitionDeletion()
	{
		header("Location: glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listDefinitions".
			"&term_id=".$this->term->getId());
		exit;
	}


	function deleteDefinition()
	{
		$this->definition->delete();
		header("Location: glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listDefinitions".
			"&term_id=".$this->term->getId());
		exit;
	}


	function moveUp()
	{
		$this->definition->moveUp();
		header("Location: glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listDefinitions".
			"&term_id=".$this->term->getId());
		exit;
	}


	function moveDown()
	{
		$this->definition->moveDown();
		header("Location: glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listDefinitions".
			"&term_id=".$this->term->getId());
		exit;
	}


	/**
	* edit meta data of glossary term definition
	*/
	function editMeta()
	{
		include_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->definition);
		$meta_gui->edit("ADM_CONTENT", "adm_content", "glossary_edit.php?ref_id=".
			$_GET["ref_id"]."&def=".$_GET["def"]."&cmd=saveDefinitionMetaData");
	}


	/**
	* save meta data of glossary term definition
	*/
	function saveMeta()
	{
		include_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->definition);
		$meta_gui->save();
		header("Location: glossary_edit.php?cmd=view&ref_id=".$_GET["ref_id"].
			"&def=".$_GET["def"]);
	}

	/**
	* output main header (title and locator)
	*/
	function main_header($a_header_title)
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->setVariable("HEADER", $a_header_title);
		$this->displayLocator();
		//$this->setAdminTabs($a_type);
	}



	function setAdminTabs()
	{
		$tabs = array();
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

		$tabs[] = array("edit", "view");
		$tabs[] = array("cont_preview", "preview");
		$tabs[] = array("meta_data", "editDefinitionMetaData");

		foreach ($tabs as $row)
		{
			$i++;

			if ($row[1] == $_GET["cmd"])
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);
			$this->tpl->setVariable("TAB_LINK", "glossary_edit.php?ref_id=".$_GET["ref_id"]."&def=".
				$_GET["def"]."&cmd=".$row[1]);
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE", "tabinactive");
		$this->tpl->setVariable("TAB_LINK", "glossary_edit.php?ref_id=".$_GET["ref_id"].
			"&cmd=listDefinitions&term_id=".$this->term->getId());
		$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("cont_term"));
		$this->tpl->parseCurrentBlock();

	}

	function displayLocator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->glossary->getTitle());
		$this->tpl->setVariable("LINK_ITEM", "glossary_edit.php?ref_id=".$_GET["ref_id"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->term->getTerm());
		$this->tpl->setVariable("LINK_ITEM", "glossary_edit.php?ref_id=".$_GET["ref_id"].
			"&cmd=listDefinitions&term_id=".$this->term->getId());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("cont_definition")." ".$this->definition->getNr());
		$this->tpl->setVariable("LINK_ITEM", "glossary_edit.php?ref_id=".$_GET["ref_id"].
			"&cmd=".$_GET["cmd"]."&def=".$_GET["def"]);
		$this->tpl->parseCurrentBlock();

		//$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}

	function saveShortText()
	{
		$page =& $this->definition->getPageObject();
		$text = $page->getFirstParagraphText();
		$this->definition->setShortText(ilUtil::shortenText($text, 190, true));
		$this->definition->update();
	}
}
?>
