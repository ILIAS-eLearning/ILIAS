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

require_once("./content/classes/class.ilLMTable.php");
require_once("./content/classes/class.ilPageContentGUI.php");

/**
* Class ilLMTableGUI
*
* User Interface for Table Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMTableGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilLMTableGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_lm_obj, $a_pg_obj, $a_content_obj, $a_hier_id);
	}

	function edit()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table_properties.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_tab_properties"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
			$this->lm_obj->getId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		// language
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		$lang = ilMetaData::getLanguages();
		$select_lang = ilUtil::formSelect ($this->content_obj->getLanguage(),"tab_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);

		// width
		$this->tpl->setVariable("TXT_TABLE_WIDTH", $this->lng->txt("cont_table_width"));
		$this->tpl->setVariable("INPUT_TABLE_WIDTH", "tab_width");
		$this->tpl->setVariable("VAL_TABLE_WIDTH", $this->content_obj->getWidth());

		// border
		$this->tpl->setVariable("TXT_TABLE_BORDER", $this->lng->txt("cont_table_border"));
		$this->tpl->setVariable("INPUT_TABLE_BORDER", "tab_border");
		$this->tpl->setVariable("VAL_TABLE_BORDER", $this->content_obj->getBorder());

		// padding
		$this->tpl->setVariable("TXT_TABLE_PADDING", $this->lng->txt("cont_table_cellpadding"));
		$this->tpl->setVariable("INPUT_TABLE_PADDING", "tab_padding");
		$this->tpl->setVariable("VAL_TABLE_PADDING", $this->content_obj->getCellPadding());

		// spacing
		$this->tpl->setVariable("TXT_TABLE_SPACING", $this->lng->txt("cont_table_cellspacing"));
		$this->tpl->setVariable("INPUT_TABLE_SPACING", "tab_spacing");
		$this->tpl->setVariable("VAL_TABLE_SPACING", $this->content_obj->getCellSpacing());

		// header caption
		$this->tpl->setVariable("TXT_HEADER_CAPTION", $this->lng->txt("cont_header_caption"));
		$this->tpl->setVariable("INPUT_HEADER_CAPTION", "header_caption");
		$this->tpl->setVariable("VAL_HEADER_CAPTION", $this->content_obj->getHeaderCaption());

		// footer caption
		$this->tpl->setVariable("TXT_FOOTER_CAPTION", $this->lng->txt("cont_footer_caption"));
		$this->tpl->setVariable("INPUT_FOOTER_CAPTION", "footer_caption");
		$this->tpl->setVariable("VAL_FOOTER_CAPTION", $this->content_obj->getFooterCaption());

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	function saveProperties()
	{
		$this->content_obj->setLanguage($_POST["tab_language"]);
		$this->content_obj->setWidth($_POST["tab_width"]);
		$this->content_obj->setBorder($_POST["tab_border"]);
		$this->content_obj->setCellSpacing($_POST["tab_spacing"]);
		$this->content_obj->setCellPadding($_POST["tab_padding"]);
		$this->content_obj->setHeaderCaption($_POST["header_caption"]);
		$this->content_obj->setFooterCaption($_POST["footer_caption"]);
		$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
		exit;

	}

	function insert()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table_new.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_table"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
			$this->lm_obj->getId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		for($i=1; $i<=10; $i++)
		{
			$nr[$i] = $i;
		}

		// select fields for number of columns
		$this->tpl->setVariable("TXT_COLS", $this->lng->txt("cont_nr_cols"));
		$select_cols = ilUtil::formSelect ("2","nr_cols",$nr,false,true);
		$this->tpl->setVariable("SELECT_COLS", $select_cols);
		$this->tpl->setVariable("TXT_ROWS", $this->lng->txt("cont_nr_rows"));
		$select_rows = ilUtil::formSelect ("2","nr_rows",$nr,false,true);
		$this->tpl->setVariable("SELECT_ROWS", $select_rows);

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_tab");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	function create()
	{
		$new_table = new ilLMTable($this->dom);
		$new_table->createNode();
		$this->pg_obj->insertContent($new_table, $this->hier_id, IL_INSERT_AFTER);
		$new_table->addRows($_POST["nr_rows"], $_POST["nr_cols"]);
		$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

	/**
	* create table as first child of a container (e.g. a TableData Element)
	*/
	function create_child()
	{
		/*
		$new_par = new ilParagraph($this->dom);
		$new_par->createNode();
		$new_par->setText($new_par->input2xml($_POST["par_content"]));
		$this->pg_obj->insertContent($new_par, $this->hier_id, IL_INSERT_CHILD);*/

		header("location: lm_edit.php?cmd=viewWysiwyg&lm_id=".$this->lm_obj->getId()."&obj_id=".
			$this->pg_obj->getId());
	}

}
?>
