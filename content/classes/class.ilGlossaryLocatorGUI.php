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

/**
* Glossary Locator GUI
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilGlossaryLocatorGUI
{
	var $mode;
	var $temp_var;
	var $tree;
	var $obj;
	var $lng;
	var $tpl;


	function ilGlossaryLocatorGUI()
	{
		global $lng, $tpl;

		$this->mode = "std";
		$this->temp_var = "LOCATOR";
		$this->lng =& $lng;
		$this->tpl =& $tpl;
	}

	function setTemplateVariable($a_temp_var)
	{
		$this->temp_var = $a_temp_var;
	}

	function setTerm(&$a_term)
	{
		$this->term =& $a_term;
	}

	function setGlossary(&$a_glossary)
	{
		$this->glossary =& $a_glossary;
	}

	function setDefinition(&$a_def)
	{
		$this->definition =& $a_def;
	}

	/**
	* display locator
	*/
	function display()
	{
		global $lng;

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
		$this->tpl->setVariable("TXT_LOCATOR", $debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}

}
?>
