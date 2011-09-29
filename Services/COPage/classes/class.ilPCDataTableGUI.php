<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		$this->setCharacteristics(array("StandardTable" => $this->lng->txt("cont_StandardTable")));
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->getCharacteristicsOfCurrentStyle("table");	// scorm-2004
		
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
	* Edit data of table. (classic version)
	*/
	function editDataCl()
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
		$dtpl->setVariable("BB_MENU", $this->getBBMenu("cell_0_0"));
		
		$this->tpl->addJavascript("./Services/COPage/phpBB/3_0_5/editor.js");
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
				if ($res2->nodeset[$j]->get_attribute("Hidden") != "Y")
				{
					$dtpl->setCurrentBlock("cell");
					
					if (is_array($_POST["cmd"]) && key($_POST["cmd"]) == "update")
					{
						$s_text = ilUtil::stripSlashes("cell_".$i."_".$j, false);
					}
					else
					{
						$s_text = ilPCParagraph::xml2output($this->content_obj->getCellText($i, $j));
					}
	
//					$dtpl->setVariable("PAR_TA_NAME", "cell[".$i."][".$j."]");
					$dtpl->setVariable("PAR_TA_ID", "cell_".$i."_".$j);
					$dtpl->setVariable("PAR_TA_CONTENT", $s_text);
					
					$cs = $res2->nodeset[$j]->get_attribute("ColSpan");
					$rs = $res2->nodeset[$j]->get_attribute("RowSpan");
//					$dtpl->setVariable("WIDTH", "140");
//					$dtpl->setVariable("HEIGHT", "80");
					if ($cs > 1)
					{
						$dtpl->setVariable("COLSPAN", 'colspan="'.$cs.'"');
						$dtpl->setVariable("WIDTH", (140 + ($cs - 1) * 146));
					}
					if ($rs > 1)
					{
						$dtpl->setVariable("ROWSPAN", 'rowspan="'.$rs.'"');
						$dtpl->setVariable("HEIGHT", (80 + ($rs - 1) * 86));
					}
					$dtpl->parseCurrentBlock();
				}
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
	 * Update table data in dom and update page in db
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
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "editData");
		}
	}

	/**
	 * Update via JavaScript
	 */
	function updateJS()
	{
		global $ilBench, $lng, $ilCtrl;
				
		if ($_POST["cancel_update"])
		{
			$this->ctrl->redirect($this, "editData");
		}

		// handle input data
		include_once("./Services/COPage/classes/class.ilPCParagraph.php");
		include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
		$data = array();

		$content = ilUtil::stripSlashes($_POST["ajaxform_content"], false);
		//$content = ilUtil::stripOnlySlashes($_POST["ajaxform_content"], false);
//echo htmlentities($content); exit;

		while (is_int($pos1 = strpos($content, '<div id="div_cell_')))
		{
			// get main div next table cell
			$pos2 = strpos($content, '<!-- td_div_end -->', $pos1);
			$pos3 = strpos($content, '>', $pos1) + 1;
			$pos4 = strpos($content, '"', $pos1 + 18);
			$div = substr($content, $pos3, $pos2-$pos3);
			// determine id of table cell
			$id = substr($content, $pos1 + 18, $pos4 - $pos1 - 18);
			$id = explode("_", $id);

			// @todo: General issue: we get different formatted content here
			// compared to the paragraphs
			// paragraphs: tiny -> ajax
			// tables: tiny -> browser dom -> ajax
			// it would be better to store the content that has been changed
			// in variables insted to read it from the dom and to jsonify it back
			// to here
$div = str_replace("</p>", "<br />", $div);
$div = str_replace("\n", "", $div);
$div = str_replace("<p>", "", $div);
$div = "<div class='ilc_text_block_TableContent'>".$div;
			$div = str_replace("<br>", "<br />", $div);
			$div = str_replace("&nbsp;", " ", $div);
$div = str_replace("<br /></div>", "</div>", $div);
$div = str_replace("\n", "", $div);
$div = str_replace("\r", "", $div);
//
			$text = ilPCParagraph::handleAjaxContent($div);
			if ($text === false)
			{
				$ilCtrl->returnToParent($this, "jump".$this->hier_id);
			}
			$text = $text["text"];

			$text = ilPCParagraph::_input2xml($text,
				$this->content_obj->getLanguage(), true, false);
			$text = ilPCParagraph::handleAjaxContentPost($text);

			$data[$id[0]][$id[1]] = $text;

			$content = substr($content, $pos1 + 10);
		}

/*		if (is_array($_POST["cell"]))
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
		}*/

		$this->updated = $this->content_obj->setData($data);

		if ($this->updated !== true)
		{
			$this->editData();
			return;
		}

		$this->updated = $this->pg_obj->update();

		
		// perform table action? (move...?)
		//$this->update(false);
		$this->pg_obj->addHierIDs();

		$cell_hier_id = ($_POST["tab_cmd_type"] == "col")
			? $this->hier_id."_1_".($_POST["tab_cmd_id"] + 1)
			: $this->hier_id."_".($_POST["tab_cmd_id"] + 1)."_1";
		$cell_obj = $this->pg_obj->getContentObject($cell_hier_id);
		if (is_object($cell_obj))
		{
			$cell_obj->$_POST["tab_cmd"]();
			$_SESSION["il_pg_error"] = $this->pg_obj->update();
		}

		
		
		//if ($a_redirect)
		//{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "editData");
		//}
	}


	/**
	* Get new table object
	*/
	function getNewTableObject()
	{
		return new ilPCDataTable($this->dom);
	}
	
	/**
	* After creation processing
	*/
	function afterCreation()
	{
		global $ilCtrl;

		$this->pg_obj->stripHierIDs();
		$this->pg_obj->addHierIDs();
		$ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
		$ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
		$this->content_obj->setHierId($this->content_obj->readHierId());
		$this->setHierId($this->content_obj->readHierId());
		$this->content_obj->setPCId($this->content_obj->readPCId());
		$this->editData();
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

		if (DEVMODE == 1)
		{
			$ilTabs->addTarget("cont_ed_edit_data_cl",
				$ilCtrl->getLinkTarget($this, "editDataCl"), "editDataCl",
				get_class($this));
		}
	}


	////
	//// Full JS implementation
	////

	/**
	 * Edit data of table
	 */
	function editData()
	{
		global $lng, $ilCtrl;
//var_dump($_GET);
//var_dump($_POST);

		$this->setTabs();

		$this->displayValidationError();

		include_once("./Services/COPage/classes/class.ilPCParagraph.php");

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tabledata2.html", "Services/COPage");
		$dtpl = $this->tpl;
		//$dtpl = new ilTemplate("tpl.tabledata.html", true, true, "Services/COPage");
		$dtpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "tableAction"));
//		$dtpl->setVariable("BB_MENU", $this->getBBMenu("cell_0_0"));

//		$this->tpl->addJavascript("./Services/COPage/phpBB/3_0_5/editor.js");
//		$this->tpl->addJavascript("./Services/COPage/js/page_editing.js");


		$this->tpl->setVariable("WYSIWYG_ACTION",
			$ilCtrl->getFormAction($this, "updateJS"));


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
				if ($res2->nodeset[$j]->get_attribute("Hidden") != "Y")
				{
					$dtpl->setCurrentBlock("cell");

					if (is_array($_POST["cmd"]) && key($_POST["cmd"]) == "update")
					{
						$s_text = ilUtil::stripSlashes("cell_".$i."_".$j, false);
					}
					else
					{
						$s_text = ilPCParagraph::xml2output($this->content_obj->getCellText($i, $j),
							 true, false);
						include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
						$s_text = ilPCParagraphGUI::xml2outputJS($s_text, "TableContent",
							$this->content_obj->readPCId()."_".$i."_".$j);
					}

					$dtpl->setVariable("PAR_TA_NAME", "cell[".$i."][".$j."]");
					$dtpl->setVariable("PAR_TA_ID", "cell_".$i."_".$j);
					$dtpl->setVariable("PAR_TA_CONTENT", $s_text);

					$cs = $res2->nodeset[$j]->get_attribute("ColSpan");
					$rs = $res2->nodeset[$j]->get_attribute("RowSpan");
					$dtpl->setVariable("WIDTH", "140");
					$dtpl->setVariable("HEIGHT", "80");
					if ($cs > 1)
					{
						$dtpl->setVariable("COLSPAN", 'colspan="'.$cs.'"');
						$dtpl->setVariable("WIDTH", (140 + ($cs - 1) * 146));
					}
					if ($rs > 1)
					{
						$dtpl->setVariable("ROWSPAN", 'rowspan="'.$rs.'"');
						$dtpl->setVariable("HEIGHT", (80 + ($rs - 1) * 86));
					}
					$dtpl->parseCurrentBlock();
				}
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


		$this->tpl->setVariable("FORMACTION2",
			$ilCtrl->getFormAction($this, "tableAction"));
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_table"));

		// js editing preparation
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initDragDrop();
		ilYuiUtil::initConnection();
		ilYuiUtil::initPanel(false);
		$GLOBALS["tpl"]->addJavascript("Services/RTE/tiny_mce_3_3_9_2/il_tiny_mce_src.js");
		$GLOBALS["tpl"]->addJavaScript("./Services/COPage/js/ilcopagecallback.js");
		$GLOBALS["tpl"]->addJavaScript("./Services/COPage/js/ilpageedit.js");
		$GLOBALS["tpl"]->addJavascript("Services/COPage/js/page_editing.js");

		$GLOBALS["tpl"]->addOnloadCode("var preloader = new Image();
			preloader.src = './templates/default/images/loader.gif';
			ilCOPage.setContentCss('".
			ilObjStyleSheet::getContentStylePath((int) $this->getStyleId()).
			", ".ilUtil::getStyleSheetLocation().", ./Services/COPage/css/tiny_extra.css');
			ilCOPage.editTD('cell_0_0');
				");


		$this->tpl->setVariable("IL_TINY_MENU",
			ilPageObjectGUI::getTinyMenu(
			$this->pg_obj->getParentType(),
			false,
			$this->pg_obj->getParentType() == "wpg",
			false,
			$this->getStyleId(),
			false, false));


	}


}
?>