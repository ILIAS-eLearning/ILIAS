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

require_once("classes/class.ilHistory.php");

/**
* This class provides user interface methods for history entries
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @package ilias-core
*/
class ilHistoryGUI
{
	var $obj_id;
	var $lng;
	var $tpl;
	
	function ilHistoryGUI($a_obj_id, $a_obj_type = "")
	{
		global $lng;
		
		$this->obj_id = $a_obj_id;
		
		if ($a_obj_type == "")
		{
			$this->obj_type = ilObject::_lookupType($a_obj_id);
		}
		else
		{
			$this->obj_type = $a_obj_type;
		}
		
		$this->lng =& $lng;
	}

	/**
	* get history table
	*/
	function getHistoryTable($a_header_params, $a_user_comment = false)
	{
		require_once("classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI(0, false);
		
		// table header
		$tbl->setTitle($this->lng->txt("history"));
		if ($a_user_comment)
		{
			$tbl->setHeaderNames(array($this->lng->txt("date"),
				$this->lng->txt("user"), $this->lng->txt("action"),
				$this->lng->txt("user_comment")));
			$tbl->setColumnWidth(array("15%", "15%", "30%", "40%"));
			$cols = array("date", "user", "action", "comment");
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("date"),
				$this->lng->txt("user"), $this->lng->txt("action")));
			$tbl->setColumnWidth(array("30%", "30%", "40%"));
			$cols = array("date", "user", "action");
		}

		if ($a_header_params == "")
		{
			$a_header_params = array();
		}
		$header_params = $a_header_params;
		$tbl->setHeaderVars($cols, $header_params);

		// table variables
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// get history entries
		$entries = ilHistory::_getEntriesForObject($this->obj_id, $this->obj_type);

		$tbl->setMaxCount(count($entries));
		$entries = array_slice($entries, $_GET["offset"], $_GET["limit"]);

		//$tbl->render();

		$this->tpl =& $tbl->getTemplateObject();
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.history_row.html", false);
		//$this->tpl->setVariable("TBL_CONTENT", "<tr><td>kkk</td></tr>");
		//$tbl->render();
		if(count($entries) > 0)
		{
			$i=0;
			foreach($entries as $entry)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$css_row = ($cssrow != "tblrow1") ? "tblrow1" : "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TXT_DATE", $entry["date"]);
				$name = ilObjUser::_lookupName($entry["user_id"]);
				$this->tpl->setVariable("TXT_USER",
					$name["title"]." ".$name["firstname"]." ".$name["lastname"]." [".$entry["user_id"]."]");
				$info_params = explode(",", $entry["info_params"]);
				$info_text = $this->lng->txt("hist_".$this->obj_type.
					"_".$entry["action"]);
				$i=1;
				foreach($info_params as $info_param)
				{
					$info_text = str_replace("%".$i, $info_param, $info_text);
					$i++;
				}
				$this->tpl->setVariable("TXT_ACTION", $info_text);
				if ($a_user_comment)
				{
					$this->tpl->setCurrentBlock("user_comment");
					$this->tpl->setVariable("TXT_USER_COMMENT", $entry["user_comment"]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("tbl_content");
				}
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("tbl_content_cell");
			$this->tpl->setVariable("TBL_CONTENT_CELL", $this->lng->txt("hist_no_entries"));
			$this->tpl->setVariable("TBL_COL_SPAN", 4);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("tbl_content_row");
			$this->tpl->setVariable("ROWCOLOR", "tblrow1");
			$this->tpl->parseCurrentBlock();
		}
		$tbl->render();
		$this->tpl->parseCurrentBlock();
		
		return $this->tpl->get();

	}

} // END class.ilHistoryGUI
?>
