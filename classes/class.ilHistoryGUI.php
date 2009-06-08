<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("classes/class.ilHistory.php");

/**
* This class provides user interface methods for history entries
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilHistoryGUI
{
	var $obj_id;
	var $lng;
	var $tpl;
	
	function ilHistoryGUI($a_obj_id, $a_obj_type = "")
	{
		global $lng, $ilCtrl;
		
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
		$this->ctrl =& $ilCtrl;
	}


	/**
	* get history table
	*/
	function getHistoryTable($a_header_params, $a_user_comment = false)
	{
		$ref_id = $a_header_params["ref_id"];
		
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI(0, false);
		
		// table header
		$tbl->setTitle($this->lng->txt("history"));
		
		$tbl->setHeaderNames(array($this->lng->txt("date")."/".
			$this->lng->txt("user"), $this->lng->txt("action")));
		$tbl->setColumnWidth(array("40%", "60%"));
		$cols = array("date_user", "action");

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
		$tbl->disable("header");
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// get history entries
		$entries = ilHistory::_getEntriesForObject($this->obj_id, $this->obj_type);

		$tbl->setMaxCount(count($entries));
		$entries = array_slice($entries, $_GET["offset"], $_GET["limit"]);

		$this->tpl =& $tbl->getTemplateObject();
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.history_row.html", false);

		if(count($entries) > 0)
		{
			$i=0;
			foreach($entries as $entry)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$css_row = ($css_row != "tblrow1") ? "tblrow1" : "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable('TXT_DATE',ilDatePresentation::formatDate(new ilDateTime($entry["date"],IL_CAL_DATETIME)));
				$name = ilObjUser::_lookupName($entry["user_id"]);
				$login = ilObjUser::_lookupLogin($entry["user_id"]);
				$this->tpl->setVariable("TXT_USER",
					$name["title"]." ".$name["firstname"]." ".$name["lastname"]." [".$login."]");
				$info_params = explode(",", $entry["info_params"]);
				
				// not so nice
				if ($this->obj_type != "lm" && $this->obj_type != "dbk")
				{
					$info_text = $this->lng->txt("hist_".str_replace(":", "_", $this->obj_type).
						"_".$entry["action"]);
				}
				else
				{
					$info_text = $this->lng->txt("hist_".str_replace(":", "_", $entry["obj_type"]).
						"_".$entry["action"]);
				}
				$i=1;
				foreach($info_params as $info_param)
				{
					$info_text = str_replace("%".$i, $info_param, $info_text);
					$i++;
				}
				$this->tpl->setVariable("TXT_ACTION", $info_text);
				if ($this->obj_type == "lm" || $this->obj_type == "dbk")
				{
					$obj_arr = explode(":", $entry["obj_type"]);
					switch ($obj_arr[1])
					{
						case "st":
							$img_type = "st";
							$class = "ilstructureobjectgui";
							$cmd = "view";
							break;
							
						case "pg":
							$img_type = "pg";
							$class = "illmpageobjectgui";
							$cmd = "edit";
							break;

						default:
							$img_type = $obj_arr[0];
							$class = "";
							$cmd = "view";
							break;
					}

					$this->tpl->setCurrentBlock("item_icon");
					$this->tpl->setVariable("SRC_ICON", ilUtil::getImagePath("icon_".$img_type.".gif"));
					$this->tpl->parseCurrentBlock();
					
					if ($class != "")
					{
						$this->tpl->setCurrentBlock("item_link");
						$this->ctrl->setParameterByClass($class, "obj_id", $entry["obj_id"]);
						$this->tpl->setVariable("HREF_LINK", 
							$this->ctrl->getLinkTargetByClass($class, $cmd));
						$this->tpl->setVariable("TXT_LINK", $entry["title"]);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("item_title");
						$this->tpl->setVariable("TXT_TITLE",
							ilObject::_lookupTitle($entry["obj_id"]));
						$this->tpl->parseCurrentBlock();
					}
				}
				if ($a_user_comment && $entry["user_comment"] != "")
				{
					$this->tpl->setCurrentBlock("user_comment");
					$this->tpl->setVariable("TXT_COMMENT", $this->lng->txt("comment"));
					$this->tpl->setVariable("TXT_USER_COMMENT", $entry["user_comment"]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("tbl_content");
				
				$this->tpl->setCurrentBlock("tbl_content");
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
		//$this->tpl->parseCurrentBlock();
		
		return $this->tpl->get();
	}

	/**
	* get versions table
	*/
	function getVersionsTable($a_header_params, $a_user_comment = false)
	{
		$ref_id = $a_header_params["ref_id"];
		
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI(0, false);
		
		// table header
		$tbl->setTitle($this->lng->txt("versions"));
		
		$tbl->setHeaderNames(array($this->lng->txt("date")."/".
			$this->lng->txt("user"), $this->lng->txt("action")));
		$tbl->setColumnWidth(array("40%", "60%"));
		$cols = array("date_user", "action");

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
		$tbl->disable("header");

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// get history entries
		$entries = ilHistory::_getEntriesForObject($this->obj_id, $this->obj_type);

		$tbl->setMaxCount(count($entries));
		$entries = array_slice($entries, $_GET["offset"], $_GET["limit"]);

		$this->tpl =& $tbl->getTemplateObject();
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.history_row.html", false);

		if(count($entries) > 0)
		{
			$i=0;
			foreach($entries as $entry)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$css_row = ($css_row != "tblrow1") ? "tblrow1" : "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable('TXT_DATE',ilDatePresentation::formatDate(new ilDateTime($entry['date'],IL_CAL_DATETIME)));
				
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
				
				$this->tpl->setCurrentBlock("dl_link");
				$this->tpl->setVariable("TXT_DL", $this->lng->txt("download"));
				$this->tpl->setVariable("DL_LINK", "repository.php?cmd=sendfile&hist_id=".$entry["hist_entry_id"]."&ref_id=".$ref_id);
				$this->tpl->setCurrentBlock("tbl_content");
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
		//$this->tpl->parseCurrentBlock();
		
		return $this->tpl->get();
	}

} // END class.ilHistoryGUI
?>
