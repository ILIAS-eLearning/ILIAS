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

require_once("./content/classes/class.ilLMList.php");
require_once("./content/classes/class.ilPageContentGUI.php");

/**
* Class ilLMListGUI
*
* User Interface for LM List Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMListGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilLMListGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_lm_obj, $a_pg_obj, $a_content_obj, $a_hier_id);
	}


	/**
	* insert new list form
	*/
	function insert()
	{
		// new list form (list item number)
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.list_new.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_list"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		for($i=1; $i<=10; $i++)
		{
			$nr[$i] = $i;
		}

		// select fields for number of columns
		$this->tpl->setVariable("TXT_ORDER", $this->lng->txt("language"));
		$order = array("Unordered" => $this->lng->txt("cont_Unordered"),
			"Number" => $this->lng->txt("cont_Number"),
			"Roman" => $this->lng->txt("cont_Roman"),
			"roman" => $this->lng->txt("cont_roman"),
			"Alphabetic" => $this->lng->txt("cont_Alphabetic"),
			"alphabetic" => $this->lng->txt("cont_alphabetic"));
		$select_order = ilUtil::formSelect ("","list_order",$order,false,true);
		$this->tpl->setVariable("SELECT_ORDER", $select_order);
		$this->tpl->setVariable("TXT_NR_ITEMS", $this->lng->txt("cont_nr_items"));
		$select_items = ilUtil::formSelect ("2","nr_items",$nr,false,true);
		$this->tpl->setVariable("SELECT_NR_ITEMS", $select_items);

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_list");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		$this->content_obj = new ilLMList($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		$this->content_obj->addItems($_POST["nr_items"]);
		$this->content_obj->setOrderType($_POST["list_order"]);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
				$this->pg_obj->getId());
			exit;
		}
		else
		{
			$this->insert();
		}
	}

		/**
	* edit properties form
	*/
	function edit()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.list_properties.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_list_properties"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		$this->displayValidationError();

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		// list
		$this->tpl->setVariable("TXT_LIST", $this->lng->txt("cont_list"));

		$this->tpl->setVariable("TXT_ORDER", $this->lng->txt("language"));
		$order = array("Unordered" => $this->lng->txt("cont_Unordered"),
			"Number" => $this->lng->txt("cont_Number"),
			"Roman" => $this->lng->txt("cont_Roman"),
			"roman" => $this->lng->txt("cont_roman"),
			"Alphabetic" => $this->lng->txt("cont_Alphabetic"),
			"alphabetic" => $this->lng->txt("cont_alphabetic"));
		$select_order = ilUtil::formSelect ("","list_order",$order,false,true);
		$this->tpl->setVariable("SELECT_ORDER", $select_order);




		$this->tpl->setVariable("INPUT_TD_WIDTH", "td_width");
		$this->tpl->setVariable("BTN_WIDTH", "setWidth");
		$this->tpl->setVariable("BTN_TXT_WIDTH", $this->lng->txt("cont_set_width"));
		// todo: we need a css concept here!
		$select_class = ilUtil::formSelect ("","td_class",
			array("" => $this->lng->txt("none"), "ilc_Cell1" => "ilc_Cell1", "ilc_Cell2" => "ilc_Cell2",
			"ilc_Cell3" => "ilc_Cell3", "ilc_Cell4" => "ilc_Cell4"),false,true);
		$this->tpl->setVariable("SELECT_CLASS", $select_class);
		$this->tpl->setVariable("BTN_CLASS", "setClass");
		$this->tpl->setVariable("BTN_TXT_CLASS", $this->lng->txt("cont_set_class"));
		$tab_node = $this->content_obj->getNode();
		$content = $this->dom->dump_node($tab_node);
		//$dom2 =& domxml_open_mem($this->xml);

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$params = array ('mode' => 'table_edit');
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
//echo "<b>HTML</b>".htmlentities($output);
		$this->tpl->setVariable("CONT_TABLE", $output);


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

		// caption
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "tab_caption");
		$this->tpl->setVariable("VAL_CAPTION", $this->content_obj->getCaption());
		$select_align = ilUtil::formSelect ($this->content_obj->getCaptionAlign(),"tab_cap_align",
			array("top" => $this->lng->txt("cont_top"), "bottom" => $this->lng->txt("cont_bottom")),false,true);
		$this->tpl->setVariable("SELECT_CAPTION", $select_align);

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


}
?>
