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

require_once("./Services/COPage/classes/class.ilPCDataTable.php");
require_once("./Services/COPage/classes/class.ilPCTableGUI.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCTableGUI
*
* User Interface for Data Table Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCDataTableGUI extends ilPCTableGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCDataTableGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}


	/**
	* edit properties form
	*/
	function edit()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table_properties.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_tab_properties"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// table
		$this->tpl->setVariable("TXT_TABLE", $this->lng->txt("cont_table"));
		$this->tpl->setVariable("INPUT_TD_WIDTH", "td_width");
		$this->tpl->setVariable("BTN_WIDTH", "setWidth");
		$this->tpl->setVariable("BTN_TXT_WIDTH", $this->lng->txt("cont_set_width"));
		// todo: we need a css concept here!
		$select_class = ilUtil::formSelect ("","td_class",
			array("" => $this->lng->txt("none"), "ilc_Cell1" => "Cell1", "ilc_Cell2" => "Cell2",
			"ilc_Cell3" => "Cell3", "ilc_Cell4" => "Cell4"),false,true);
		$this->tpl->setVariable("SELECT_CLASS", $select_class);
		$this->tpl->setVariable("BTN_CLASS", "setClass");
		$this->tpl->setVariable("BTN_TXT_CLASS", $this->lng->txt("cont_set_class"));
		$tab_node = $this->content_obj->getNode();
		$content = $this->dom->dump_node($tab_node);
		//$dom2 =& domxml_open_mem($this->xml);
		$trans =& $this->pg_obj->getLanguageVariablesXML();
		$content = "<dummy>".$content.$trans."</dummy>";

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$med_disabled_path = ilUtil::getImagePath("media_disabled.gif");
		$params = array ('mode' => 'table_edit', 'med_disabled_path' => $med_disabled_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
		$output = str_replace("&amp;","&",$output);

//echo "<b>HTML</b>".htmlentities($output);
		$this->tpl->setVariable("CONT_TABLE", $output);


		// language
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
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
		$caption = $this->content_obj->getCaption();
		$caption = str_replace("&", "&amp;", $caption);
		$this->tpl->setVariable("TXT_CAPTION", $this->lng->txt("cont_caption"));
		$this->tpl->setVariable("INPUT_CAPTION", "tab_caption");
		$this->tpl->setVariable("VAL_CAPTION", $caption);
		$select_align = ilUtil::formSelect ($this->content_obj->getCaptionAlign(),"tab_cap_align",
			array("top" => $this->lng->txt("cont_top"), "bottom" => $this->lng->txt("cont_bottom")),false,true);
		$this->tpl->setVariable("SELECT_CAPTION", $select_align);

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelUpdate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* Edit data of table.
	*/
	function editData()
	{
		global $lng, $ilCtrl;
		
		$this->displayValidationError();
		
		include_once("./Services/COPage/classes/class.ilPCParagraph.php");
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tabledata.html", "Services/COPage");
		$dtpl = $this->tpl;
		//$dtpl = new ilTemplate("tpl.tabledata.html", true, true, "Services/COPage");
		$dtpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$dtpl->setVariable("BB_MENU", $this->getBBMenu());
		
		$this->tpl->addJavascript("./Services/COPage/phpBB/3_0_0/editor.js");
		$this->tpl->addJavascript("./Services/COPage/js/page_editing.js");

		// get all rows
		$xpc = xpath_new_context($this->dom);
		$path = "//PageContent[@HierId='".$this->getHierId()."']".
			"/Table/TableRow";
		$res =& xpath_eval($xpc, $path);

		for($i = 0; $i < count($res->nodeset); $i++)
		{

			$xpc2 = xpath_new_context($this->dom);
			$path2 = "//PageContent[@HierId='".$this->getHierId()."']".
				"/Table/TableRow[$i+1]/TableData";
			$res2 =& xpath_eval($xpc2, $path2);
			
			// if this is the first row -> col icons
			if ($i == 0)
			{
				for($j = 0; $j < count($res2->nodeset); $j++)
				{
					if ($j == 0)
					{
						$dtpl->touchBlock("empty_td");
					}

					if ($j == 0)
					{
						if (count($res2->nodeset) == 1)
						{
							$move_type = "none";
						}
						else
						{
							$move_type = "forward";
						}
					}
					else if ($j == (count($res2->nodeset) - 1))
					{
						$move_type = "backward";
					}
					else
					{
						$move_type = "both";
					} 
					$dtpl->setCurrentBlock("col_icon");
					$dtpl->setVariable("COL_ICON_ALT", $lng->txt("content_column"));
					$dtpl->setVariable("COL_ICON", ilUtil::getImagePath("col.gif"));
					$dtpl->setVariable("COL_ONCLICK", "COL_".$move_type);
					$dtpl->setVariable("NR", $j);
					$dtpl->parseCurrentBlock();
				}
				$dtpl->setCurrentBlock("row");
				$dtpl->parseCurrentBlock();
			}


			for($j = 0; $j < count($res2->nodeset); $j++)
			{
				// first col: row icons
				if ($j == 0)
				{
					if ($i == 0)
					{
						if (count($res->nodeset) == 1)
						{
							$move_type = "none";
						}
						else
						{
							$move_type = "forward";
						}
					}
					else if ($i == (count($res->nodeset) - 1))
					{
						$move_type = "backward";
					}
					else
					{
						$move_type = "both";
					}
					$dtpl->setCurrentBlock("row_icon");
					$dtpl->setVariable("ROW_ICON_ALT", $lng->txt("content_row"));
					$dtpl->setVariable("ROW_ICON", ilUtil::getImagePath("row.gif"));
					$dtpl->setVariable("ROW_ONCLICK", "ROW_".$move_type);
					$dtpl->setVariable("NR", $i);
					$dtpl->parseCurrentBlock();
				}
				
				// cell
				$dtpl->setCurrentBlock("cell");
				
				if (is_array($_POST["cmd"]) && key($_POST["cmd"]) == "update")
				{
					$s_text = ilUtil::stripSlashes("cell_".$i."_".$j, false);
				}
				else
				{
					$s_text = ilPCParagraph::xml2output($this->content_obj->getCellText($i, $j));
				}

				$dtpl->setVariable("PAR_TA_NAME", "cell[".$i."][".$j."]");
				$dtpl->setVariable("PAR_TA_CONTENT", $s_text);
				$dtpl->parseCurrentBlock();
			}
			$dtpl->setCurrentBlock("row");
			$dtpl->parseCurrentBlock();
		}
		
		// init menues
		$types = array("row", "col");
		$moves = array("none", "backward", "both", "forward");
		$commands = array(
			"row" => array(	"newRowAfter" => "cont_ed_new_row_after",
							"newRowBefore" => "cont_ed_new_row_before",
							"moveRowUp" => "cont_ed_row_up",
							"moveRowDown" => "cont_ed_row_down",
							"deleteRow" => "cont_ed_delete_row"),
			"col" => array(	"newColAfter" => "cont_ed_new_col_after",
							"newColBefore" => "cont_ed_new_col_before",
							"moveColLeft" => "cont_ed_col_left",
							"moveColRight" => "cont_ed_col_right",
							"deleteCol" => "cont_ed_delete_col")
		);

		foreach($types as $type)
		{
			foreach($moves as $move)
			{
				foreach($commands[$type] as $command => $lang_var)
				{
					if ($move == "none" && (substr($command, 0, 4) == "move"))
					{
						continue;
					}
					if (($move == "backward" && (in_array($command, array("movedown", "moveright")))) ||
						($move == "forward" && (in_array($command, array("moveup", "moveleft")))))
					{
						continue;
					}
					$this->tpl->setCurrentBlock("menu_item");
					$this->tpl->setVariable("MENU_ITEM_TITLE", $lng->txt($lang_var));
					$this->tpl->setVariable("CMD", $command);
					$this->tpl->setVariable("TYPE", $type);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("menu");
				$this->tpl->setVariable("TYPE", $type);
				$this->tpl->setVariable("MOVE", $move);
				$this->tpl->parseCurrentBlock();
			}
		}
		
		// update/cancel
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "update");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelUpdate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("FORMACTION2",
			$ilCtrl->getFormAction($this, "tableAction"));
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_table"));

	}
	
	/**
	* update table data in dom and update page in db
	*/
	function update()
	{
		global $ilBench;

		$ilBench->start("Editor","Data_Table_update");

		// handle input data
		include_once("./Services/COPage/classes/class.ilPCParagraph.php");
		$data = array();
		if (is_array($_POST["cell"]))
		{
			foreach ($_POST["cell"] as $i => $row)
			{
				if (is_array($row))
				{
					foreach ($row as $j => $cell)
					{
						$data[$i][$j] =
							ilPCParagraph::_input2xml($cell,
								$this->content_obj->getLanguage());
					}
				}
			}
		}
		
		$this->updated = $this->content_obj->setData($data);

		if ($this->updated !== true)
		{
			$ilBench->stop("Editor","Data_Table_update");
			$this->edit();
			return;
		}

		$this->updated = $this->pg_obj->update();
		$ilBench->stop("Editor","Data_Table_update");

		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->edit();
		}
	}
	
	/**
	* set width of selected table data cells
	*/
	function setWidth()
	{
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $hier_id)
			{
				$this->content_obj->setTDWidth($hier_id, $_POST["td_width"]);
			}
		}
		$this->setProperties();
		$this->updated = $this->pg_obj->update();
		$this->pg_obj->addHierIDs();
		$this->edit();
	}

	/**
	* set class of selected table data cells
	*/
	function setClass()
	{
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $hier_id)
			{
				$this->content_obj->setTDClass($hier_id, $_POST["td_class"]);
			}
		}
		$this->setProperties();
		$this->updated = $this->pg_obj->update();
		$this->pg_obj->addHierIDs();
		$this->edit();
	}

	
	function setProperties()
	{
		// mask html
		$caption = $_POST["tab_caption"];
		$caption = str_replace("&","&amp;", $caption);
		$caption = str_replace("<","&lt;", $caption);
		$caption = str_replace(">","&gt;", $caption);

		$this->content_obj->setLanguage($_POST["tab_language"]);
		$this->content_obj->setWidth($_POST["tab_width"]);
		$this->content_obj->setBorder($_POST["tab_border"]);
		$this->content_obj->setCellSpacing($_POST["tab_spacing"]);
		$this->content_obj->setCellPadding($_POST["tab_padding"]);
		$this->content_obj->setCaption($caption, $_POST["tab_cap_align"]);
	}
	
	/**
	* save table properties in db and return to page edit screen
	*/
	function saveProperties()
	{
		$this->setProperties();
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}

	/**
	* insert new table form
	*/
	function insert()
	{
		global $ilUser;
		
		// new table form (input of rows and columns)
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table_new.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_table"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		for($i=1; $i<=10; $i++)
		{
			$nr[$i] = $i;
		}

		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}

		// select fields for number of columns
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$select_language = ilUtil::formSelect ($s_lang, "tab_language", $lang, false, true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_language);
		$this->tpl->setVariable("TXT_COLS", $this->lng->txt("cont_nr_cols"));
		$select_cols = ilUtil::formSelect ("2","nr_cols",$nr,false,true);
		$this->tpl->setVariable("SELECT_COLS", $select_cols);
		$this->tpl->setVariable("TXT_ROWS", $this->lng->txt("cont_nr_rows"));
		$select_rows = ilUtil::formSelect ("2","nr_rows",$nr,false,true);
		$this->tpl->setVariable("SELECT_ROWS", $select_rows);
		
		//import html table
		$this->tpl->setVariable("TXT_HTML_IMPORT", $this->lng->txt("cont_table_html_import"));
		$this->tpl->setVariable("TXT_SPREADSHEET", $this->lng->txt("cont_table_spreadsheet_import"));		
		$this->tpl->setVariable("TXT_BTN_HTML_IMPORT", $this->lng->txt("import"));		
		$this->tpl->setVariable("TXT_HTML_IMPORT_INFO", $this->lng->txt("cont_table_html_import_info"));
		$this->tpl->setVariable("TXT_SPREADSHEET_IMPORT_INFO", $this->lng->txt("cont_table_spreadsheet_import_info"));		
		$this->tpl->setVariable("CMD_HTML_IMPORT", "create_tab");
		$this->tpl->setVariable("SELECT_ROWS", $select_rows);
					
//		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_tab");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelCreate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}
	

	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		global	$lng;
		$this->content_obj = new ilPCDataTable($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setLanguage($_POST["tab_language"]);
		$import_table = trim($_POST["import_table"]);
		
		// import xhtml or spreadsheet table
		if (!empty ($import_table))
		{
			switch($_POST["import_type"])
			{
				// xhtml import
				case "html":
					if (!$this->content_obj->importHtml ($_POST["tab_language"], $import_table))
					{
						$this->insert();
						return;	
					}
					break;
					
				// spreadsheet
				case "spreadsheet":
					$this->content_obj->importSpreadsheet($_POST["tab_language"], $import_table);
					break;
			}
		}
		else
		{		
			$this->content_obj->addRows($_POST["nr_rows"], $_POST["nr_cols"]);
		}
		
		$this->updated = $this->pg_obj->update();
		
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}
	
	/**
	* Perform operation on table (adding, moving, deleting rows/cols)
	*/
	function tableAction()
	{
		global $ilCtrl;

		$cell_hier_id = ($_POST["type"] == "col")
			? $this->hier_id."_1_".($_POST["id"] + 1)
			: $this->hier_id."_".($_POST["id"] + 1)."_1";
		$cell_obj = $this->pg_obj->getContentObject($cell_hier_id);
		if (is_object($cell_obj))
		{
			$cell_obj->$_POST["action"]();
			$_SESSION["il_pg_error"] = $this->pg_obj->update();
		}
		$ilCtrl->redirect($this, "editData");
	}
}
?>
