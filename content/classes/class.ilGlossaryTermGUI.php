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

require_once("content/classes/class.ilGlossaryTerm.php");

/**
* GUI class for glossary terms
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilGlossaryTermGUI
{
	var $ilias;
	var $lng;
	var $tpl;
	var $glossary;
	var $term;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryTermGUI($a_id = 0)
	{
		global $lng, $ilias, $tpl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		if($a_id != 0)
		{
			$this->term =& new ilGlossaryTerm($a_id);
		}
	}

	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
	}

	/**
	* form for new content object creation
	*/
	function create()
	{
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_term_new.html", true);
		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=post");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_new_term"));
		$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
		$this->tpl->setVariable("INPUT_TERM", "term");
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		$lang = ilMetaData::getLanguages();
		$select_language = ilUtil::formSelect ("","term_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("BTN_NAME", "saveTerm");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
	}

	function save()
	{
		$term =& new ilGlossaryTerm();
		$term->setGlossary($this->glossary);
		$term->setTerm($_POST["term"]);
		$term->setLanguage($_POST["term_language"]);
		$term->create();
	}

	function editTerm()
	{
		$this->tpl->setVariable("HEADER", $this->lng->txt("cont_term").": ".$this->term->getTerm());

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_term_edit.html", true);
		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$_GET["ref_id"]."&term_id=".$_GET["term_id"]."&cmd=post");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_term"));
		$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
		$this->tpl->setVariable("INPUT_TERM", "term");
		$this->tpl->setVariable("VALUE_TERM", $this->term->getTerm());
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		$lang = ilMetaData::getLanguages();
		$select_language = ilUtil::formSelect ($this->term->getLanguage(),"term_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("BTN_NAME", "updateTerm");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
	}

	function update()
	{
		$this->term->setTerm($_POST["term"]);
		$this->term->setLanguage($_POST["term_language"]);
		$this->term->update();
	}

	function output()
	{
		require_once("content/classes/class.ilGlossaryDefinition.php");
		require_once("content/classes/Pages/class.ilPageObjectGUI.php");
		$defs = ilGlossaryDefinition::getDefinitionList($this->term->getId());

		$this->tpl->setVariable("TXT_TERM", $this->term->getTerm()."hbh");

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
			}

			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->tpl->setVariable("LINK_EDIT",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=view&def=".$def["id"]);
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("LINK_DELETE",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=confirmDefinitionDeletion&def=".$def["id"]);
			$this->tpl->parseCurrentBlock();
		}
	}
}

?>
