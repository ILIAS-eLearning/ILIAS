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

}

?>
