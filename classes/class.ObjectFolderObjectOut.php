<?php
/**
* Class ObjectFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$
*
* @extends Object
* @package ilias-core
*/

class ObjectFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function ObjectFolderObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "objf";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* list childs of current object
	*/
	function viewObject()
	{
		global $tree, $rbacsystem;

	    $this->getTemplateFile("view");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&cmd=gateway");

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");
		$head_cols = array("", "type", "name", "description", "last_change");

		foreach ($head_cols as $key)
		{
			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}

			$this->tpl->setVariable("HEADER_TEXT", $out);
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?obj_id=".$_GET["obj_id"].
				"&order=type&direction=".$_GET["dir"]."&cmd=".$_GET["cmd"]);

			$this->tpl->parseCurrentBlock();
		}

		if ($rbacsystem->checkAccess("read", $_GET["ref_id"]))
		{
			if ($list = getObjectList("typ",$_GET["order"],$_GET["direction"]))
			{
				foreach ($list as $key => $obj)
				{
					$num++;

					// color changing
					$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");

					// surpress checkbox for particular object types
					if (!$this->objDefinition->hasCheckbox($obj["type"]))
					{
						$this->tpl->touchBlock("empty_cell");
					}
					else
					{
						$this->tpl->setCurrentBlock("checkbox");
						$this->tpl->setVariable("CHECKBOX_ID", $obj["id"]);
						$this->tpl->setVariable("CSS_ROW", $css_row);
						$this->tpl->parseCurrentBlock();
					}

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

					//data
					$data = array("type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_type_b.gif\" border=\"0\">",
							"name" => $obj["title"],
							"description" => $obj["desc"],
							"last_change" => Format::formatDate($obj["last_update"]));

					foreach ($data as $key => $val)
					{
						//build link
						$link = "adm_object.php?";

						if ($_GET["type"] == "lo" && $key == "type")
						{
							$link = "lo_view.php?";
						}

						$link.= "&type=typ&obj_id=".$obj["obj_id"]."&ref_id=".$_GET["ref_id"];

						if ($key == "title" || $key == "type")
						{
							$this->tpl->setCurrentBlock("begin_link");
							$this->tpl->setVariable("LINK_TARGET", $link);

							if ($_GET["type"] == "lo" && $key == "type")
							{
								$this->tpl->setVariable("NEW_TARGET", "\" target=\"lo_view\"");
							}

							$this->tpl->parseCurrentBlock();
							$this->tpl->touchBlock("end_link");
						}

						$this->tpl->setCurrentBlock("text");
						$this->tpl->setVariable("TEXT_CONTENT", $val);
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("table_cell");
						$this->tpl->parseCurrentBlock();
					} //foreach

					$this->tpl->setCurrentBlock("table_row");
					$this->tpl->setVariable("CSS_ROW", $css_row);
					$this->tpl->parseCurrentBlock();
				} //for
			} //if is_array
		}
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		}

		// SHOW VALID ACTIONS
		$this->showActions();

		// SHOW POSSIBLE SUB OBJECTS
		$this->showPossibleSubObjects();
	}
} // END class.ObjectFolderObjectOut
?>