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
			ilUtil::sendSuccess($lng->txt("msg_obj_modified", true));
			$this->ctrl->redirect($this, "editData");
		}
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

	}

}
?>
