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

require_once("classes/class.ilObjectGUI.php");
require_once("classes/class.ilMetaDataGUI.php");
require_once("content/classes/class.ilObjGlossary.php");
require_once("content/classes/class.ilGlossaryTermGUI.php");
require_once("content/classes/class.ilGlossaryDefinition.php");
require_once("content/classes/class.ilTermDefinitionEditorGUI.php");
require_once("content/classes/Pages/class.ilPCParagraph.php");

/**
* Class ilGlossaryPresentationGUI
*
* GUI class for glossary presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilGlossaryPresentationGUI
{
	var $admin_tabs;
	var $glossary;
	var $ilias;
	var $tpl;
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryPresentationGUI()
	{
		global $lng, $ilias, $tpl;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;

		// Todo: check lm id
		$this->glossary =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

	}


	/**
	* executer command ("listTerms" | "listDefinitions")
	*/
	function executeCommand()
	{
		$cmd = $_GET["cmd"];
		if ($cmd != "listDefinitions")
		{
			$this->prepareOutput();
		}
		if($cmd == "")
		{
			$cmd = "listTerms";
		}

		$this->$cmd();

		$this->tpl->show();
	}

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->glossary->getTitle();

		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setLocator();
	}



	/**
	* list glossary terms
	*/
	function listTerms()
	{
		$this->lng->loadLanguageModule("meta");
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.term_tbl_row.html", true);

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$this->ref_id."$obj_str&cmd=post");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_terms"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array($this->lng->txt("cont_term"),
			 $this->lng->txt("language"), $this->lng->txt("cont_definitions")));

		$cols = array("term", "language", "definitions", "id");
		$header_params = array("ref_id" => $_GET["ref_id"], "cmd" => "listTerms");
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("25%","15%","60%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		//$this->tpl->setVariable("COLUMN_COUNTS", 4);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$term_list = $this->glossary->getTermList();
		$tbl->setMaxCount(count($term_list));

		// sorting array
		//$term_list = ilUtil::sortArray($term_list, $_GET["sort_by"], $_GET["sort_order"]);
		$term_list = array_slice($term_list, $_GET["offset"], $_GET["limit"]);

		// render table
		$tbl->render();

		if (count($term_list) > 0)
		{
			$i=1;
			foreach($term_list as $key => $term)
			{
				$css_row = ilUtil::switchColor($i++,"tblrow1","tblrow2");
				$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
				for($j=0; $j<count($defs); $j++)
				{
					$def = $defs[$j];
					if (count($defs) > 1)
					{
						$this->tpl->setCurrentBlock("definition");
						$this->tpl->setVariable("DEF_TEXT", $this->lng->txt("cont_definition")." ".($j + 1));
						$this->tpl->parseCurrentBlock();
					}

					//
					$this->tpl->setCurrentBlock("definition");
					$short_str = strip_tags(ilPCParagraph::xml2output($def["short_text"]));
					$short_str = str_replace("<", "&lt;", $short_str);
					$short_str = str_replace(">", "&gt;", $short_str);
					$this->tpl->setVariable("DEF_SHORT", $short_str);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("definition_row");
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("view_term");
				$this->tpl->setVariable("TEXT_TERM", $term["term"]);
				$this->tpl->setVariable("LINK_VIEW_TERM", "glossary_presentation.php?ref_id=".
					$_GET["ref_id"]."&cmd=listDefinitions&term_id=".$term["id"]);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TEXT_LANGUAGE", $this->lng->txt("meta_l_".$term["language"]));
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* list definitions of a term
	*/
	function listDefinitions()
	{
		require_once("content/classes/Pages/class.ilPageObjectGUI.php");
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->setLocator();
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

		$term =& new ilGlossaryTerm($_GET["term_id"]);
		$this->tpl->setVariable("HEADER",
			$this->lng->txt("cont_term").": ".$term->getTerm());


		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		//$this->tpl->addBlockfile("STATUSLINE", "statusline", "tpl.statusline.html");

		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$_GET["ref_id"].
			"&cmd=post&term_id=".$_GET["term_id"]);
		/*
		$this->tpl->setVariable("TXT_ADD_DEFINITION",
			$this->lng->txt("cont_add_definition"));
		$this->tpl->setVariable("BTN_ADD", "addDefinition");*/

		$defs = ilGlossaryDefinition::getDefinitionList($_GET["term_id"]);

		$this->tpl->setVariable("TXT_TERM", $term->getTerm());

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page =& new ilPageObject("gdf", $def["id"]);
			$page_gui =& new ilPageObjectGUI($page);
			//$page_gui->setOutputMode("edit");
			//$page_gui->setPresentationTitle($this->term->getTerm());
			$page_gui->setTemplateOutput(false);
			$output = $page_gui->preview();

			if (count($defs) > 1)
			{
				$this->tpl->setCurrentBlock("definition_header");
						$this->tpl->setVariable("TXT_DEFINITION",
				$this->lng->txt("cont_definition")." ".($j+1));
				$this->tpl->parseCurrentBlock();
			}

			/*
			if ($j > 0)
			{
				$this->tpl->setCurrentBlock("up");
				$this->tpl->setVariable("TXT_UP", $this->lng->txt("up"));
				$this->tpl->setVariable("LINK_UP",
					"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=moveUp&def=".$def["id"]);
				$this->tpl->parseCurrentBlock();
			}

			if ($j+1 < count($defs))
			{
				$this->tpl->setCurrentBlock("down");
				$this->tpl->setVariable("TXT_DOWN", $this->lng->txt("down"));
				$this->tpl->setVariable("LINK_DOWN",
					"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=moveDown&def=".$def["id"]);
				$this->tpl->parseCurrentBlock();
			}*/

			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			/*$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->tpl->setVariable("LINK_EDIT",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=view&def=".$def["id"]);
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("LINK_DELETE",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=confirmDefinitionDeletion&def=".$def["id"]);
				*/
			$this->tpl->parseCurrentBlock();
		}
		//$this->tpl->setCurrentBlock("def_list");
		//$this->tpl->parseCurrentBlock();

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
		global $ilias_locator;

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		require_once ("content/classes/class.ilGlossaryLocatorGUI.php");
		$gloss_loc =& new ilGlossaryLocatorGUI();
		$gloss_loc->setMode("presentation");
		if (!empty($_GET["term_id"]))
		{
			$term =& new ilGlossaryTerm($_GET["term_id"]);
			$gloss_loc->setTerm($term);
		}
		$gloss_loc->setGlossary($this->glossary);
		//$gloss_loc->setDefinition($this->definition);
		$gloss_loc->display();
		return;


		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		if (!empty($_GET["term_id"]))
		{
			$this->tpl->touchBlock("locator_separator");
		}

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->glossary->getTitle());
		// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
		$this->tpl->setVariable("LINK_ITEM", "glossary_presentation.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms");
		$this->tpl->parseCurrentBlock();

		// ### AA 03.11.10 added new locator GUI class ###
		// navigate locator
		$ilias_locator->navigate($i++,$this->glossary->getTitle(),"glossary_presentation.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms","bottom");

		if (!empty($_GET["term_id"]))
		{
			$term =& new ilGlossaryTerm($_GET["term_id"]);
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $term->getTerm());
			$this->tpl->setVariable("LINK_ITEM", "glossary_presentation.php?ref_id=".$_GET["ref_id"].
				"&cmd=listDefinitions&term_id=".$term->getId());
			$this->tpl->parseCurrentBlock();

			// ### AA 03.11.10 added new locator GUI class ###
			// navigate locator
			$ilias_locator->navigate($i++,$term->getTerm(),"glossary_edit.php?ref_id=".$_GET["ref_id"].
				"&cmd=listDefinitions&term_id=".$term->getId(),"bottom");
		}

		//$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* output tabs
	*/
	function setTabs()
	{

		// catch feedback message
		include_once("classes/class.ilTabsGUI.php");
		$tabs_gui =& new ilTabsGUI();
		$this->getTabs($tabs_gui);

		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

	}


	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		// back to upper context
		$tabs_gui->addTarget("cont_back",
			"glossary_presentation.php?ref_id=".$_GET["ref_id"], "",
			"");

	}

}

?>
