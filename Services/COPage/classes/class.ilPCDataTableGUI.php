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
return parent::edit();
	}

	/**
	* Edit data of table.
	*/
	function editData()
	{
		global $lng, $ilCtrl;
//var_dump($_GET);
//var_dump($_POST);

		$this->setTabs();

		$this->displayValidationError();
		
		include_once("./Services/COPage/classes/class.ilPCParagraph.php");
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tabledata.html", "Services/COPage");
		$dtpl = $this->tpl;
		//$dtpl = new ilTemplate("tpl.tabledata.html", true, true, "Services/COPage");
		$dtpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "tableAction"));
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
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("FORMACTION2",
			$ilCtrl->getFormAction($this, "tableAction"));
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_table"));

	}
	
	/**
	* update table data in dom and update page in db
	*/
	function update($a_redirect = true)
	{
		global $ilBench, $lng;

		$ilBench->start("Editor","Data_Table_update");

		// handle input data
		include_once("./Services/COPage/classes/class.ilPCParagraph.php");
		$data = array();
//var_dump($_POST);
//var_dump($_GET);
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
			$this->editData();
			return;
		}

		$this->updated = $this->pg_obj->update();
		$ilBench->stop("Editor","Data_Table_update");

		if ($a_redirect)
		{
			ilUtil::sendInfo($lng->txt("msg_obj_modified", true));
			$this->ctrl->redirect($this, "editData");
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


	/**
	* insert new table form
	*/
	function insert()
	{
		global $ilUser, $ilCtrl, $tpl, $lng;

		$this->displayValidationError();

		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->lng->txt("cont_insert_table"));
		
		$nr = array();
		for($i=1; $i<=20; $i++)
		{
			$nr[$i] = $i;
		}
		
		// cols
		$cols = new ilSelectInputGUI($this->lng->txt("cont_nr_cols"), "nr_cols");
		$cols->setOptions($nr);
		$cols->setValue(2);
		$form->addItem($cols);

		// rows
		$rows = new ilSelectInputGUI($this->lng->txt("cont_nr_rows"), "nr_rows");
		$rows->setOptions($nr);
		$rows->setValue(2);
		$form->addItem($rows);

		// width
		$width = new ilTextInputGUI($this->lng->txt("cont_table_width"), "width");
		$width->setValue("");
		$width->setSize(6);
		$width->setMaxLength(6);
		$form->addItem($width);
		
		// border
		$border = new ilTextInputGUI($this->lng->txt("cont_table_border"), "border");
		$border->setValue("1px");
		$border->setSize(6);
		$border->setMaxLength(6);
		$form->addItem($border);

		// padding
		$padding = new ilTextInputGUI($this->lng->txt("cont_table_cellpadding"), "padding");
		$padding->setValue("2px");
		$padding->setSize(6);
		$padding->setMaxLength(6);
		$form->addItem($padding);

		// spacing
		$spacing = new ilTextInputGUI($this->lng->txt("cont_table_cellspacing"), "spacing");
		$spacing->setValue("0px");
		$spacing->setSize(6);
		$spacing->setMaxLength(6);
		$form->addItem($spacing);

		// first row style
		require_once("./Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$fr_style = new ilRadioMatrixInputGUI($this->lng->txt("cont_first_row_style"), "first_row_style");
		$options = array("" => $this->lng->txt("none"), "ilc_Cell1" => "Cell1", "ilc_Cell2" => "Cell2",
			"ilc_Cell3" => "Cell3", "ilc_Cell4" => "Cell4");
		foreach($options as $k => $option)
		{
			$options[$k] = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="'.$k.'">'.
				$option.'</td></tr></table>';
		}

		// first row style
		require_once("./Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$fr_style = new ilRadioMatrixInputGUI($this->lng->txt("cont_first_row_style"), "first_row_style");
		$options = array("" => $this->lng->txt("none"), "ilc_Cell1" => "Cell1", "ilc_Cell2" => "Cell2",
			"ilc_Cell3" => "Cell3", "ilc_Cell4" => "Cell4");
		foreach($options as $k => $option)
		{
			$options[$k] = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="'.$k.'">'.
				$option.'</td></tr></table>';
		}
			
		$fr_style->setValue("");
		$fr_style->setOptions($options);
		$form->addItem($fr_style);

		// alignment
		$align_opts = array("Left" => $lng->txt("cont_left"),
			"Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
			"LeftFloat" => $lng->txt("cont_left_float"),
			"RightFloat" => $lng->txt("cont_right_float"));
		$align = new ilSelectInputGUI($this->lng->txt("cont_align"), "align");
		$align->setOptions($align_opts);
		$align->setValue("Center");
		$form->addItem($align);

		// import table
		$import = new ilRadioGroupInputGUI($this->lng->txt("cont_paste_table"), "import_type");
		$op = new ilRadioOption($this->lng->txt("cont_html_table"), "html");
		$import->addOption($op);
		$op2 = new ilRadioOption($this->lng->txt("cont_spreadsheet_table"), "spreadsheet");
		
			$import_data = new ilTextAreaInputGUI("", "import_table");
			$import_data->setRows(8);
			$import_data->setCols(50);
			$op2->addSubItem($import_data);
		
		$import->addOption($op2);
		$import->setValue("html");
		$form->addItem($import);

		// language
		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		//$select_language = ilUtil::formSelect ($s_lang, "tab_language", $lang, false, true);
		$language = new ilSelectInputGUI($this->lng->txt("language"), "tab_language");
		$language->setOptions($lang);
		$language->setValue($s_lang);
		$form->addItem($language);
				
		$form->addCommandButton("create_tab", $lng->txt("save"));
		$form->addCommandButton("cancelCreate", $lng->txt("cancel"));

		$html = $form->getHTML();
		$tpl->setContent($html);
return;

		
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
		global	$lng, $ilCtrl;
		
		$this->content_obj = new ilPCDataTable($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setLanguage(ilUtil::stripSlashes($_POST["tab_language"]));
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
			$this->content_obj->addRows(ilUtil::stripSlashes($_POST["nr_rows"]),
				ilUtil::stripSlashes($_POST["nr_cols"]));
		}
		$this->content_obj->setWidth(ilUtil::stripSlashes($_POST["width"]));
		$this->content_obj->setBorder(ilUtil::stripSlashes($_POST["border"]));
		$this->content_obj->setCellPadding(ilUtil::stripSlashes($_POST["padding"]));
		$this->content_obj->setCellSpacing(ilUtil::stripSlashes($_POST["spacing"]));
		$this->content_obj->setHorizontalAlign(ilUtil::stripSlashes($_POST["align"]));
		
		$frtype = ilUtil::stripSlashes($_POST["first_row_style"]);
		if ($frtype != "")
		{
			$this->content_obj->setFirstRowStyle($frtype);
		}
		
		$this->updated = $this->pg_obj->update();
		
		if ($this->updated === true)
		{
			$this->pg_obj->stripHierIDs();
			$this->pg_obj->addHierIDs();
			$ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
			$ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
			$this->content_obj->setHierId($this->content_obj->readHierId());
			$this->setHierId($this->content_obj->readHierId());
			$this->content_obj->setPCId($this->content_obj->readPCId());
//echo $this->content_obj->readHierId().":".$this->content_obj->readPCId();
			$this->editData();
			//$ilCtrl->redirect($this, "editData");
			
//			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
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

		$this->update(false);
		$this->pg_obj->addHierIDs();

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
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilCtrl, $ilTabs;
		
		parent::setTabs();
		
		$ilTabs->addTarget("cont_ed_edit_data",
			$ilCtrl->getLinkTarget($this, "editData"), "editData",
			get_class($this));

	}

}
?>
