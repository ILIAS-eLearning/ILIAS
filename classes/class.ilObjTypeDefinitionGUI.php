<?php
/**
* Class ilObjTypeDefinitionGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.ilObjTypeDefinitionGUI.php,v 1.1 2003/03/24 15:41:43 akill Exp $
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjTypeDefinitionGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjTypeDefinitionGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "typ";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* list operations of object type
	*/
	function viewObject()
	{
		global $rbacadmin;
		
		$this->getTemplateFile("view");
		$num = 0;

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");

		$cols = array("", "type", "operation", "description", "status");

		foreach ($cols as $key)
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
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&order=type&direction=".$_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);

		if ($ops_arr = getOperationList('', $_GET["order"], $_GET["direction"]))
		{
			foreach ($ops_arr as $key => $ops)
			{
				// BEGIN ROW
				if (in_array($ops["ops_id"],$ops_valid))
				{
					$ops_status = 'enabled';
				}
				else
				{
					$ops_status = 'disabled';
				}

				//visible data part
				$this->objectList["data"][] = array(
					"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
					"title" => $ops["operation"],
					"description" => $ops["desc"],
					"status" => $ops_status
				);

				//control information
				// TODO: Maybe deprecated
				$this->objectList["ctrl"][] = array(
					"type" => "perm",
					"obj_id" => $ops["ops_id"],
					"parent" => $this->id
				);

				$ctrl = $this->data["ctrl"][$i];

				$num++;

				// color changing
				$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");

				$this->tpl->touchBlock("empty_cell");

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();

				//data
				$data = array(
					"type" => "<img src=\"".$this->tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
					"title" => $ops["operation"],
					"description" => $ops["desc"],
					"status" => $ops_status
				);

				foreach ($data as $key => $val)
				{
					// color for status
					if ($key == "status")
					{
						if ($val == "enabled")
						{
							$color = "green";
						}
						else
						{
							$color = "red";
						}

						$val = "<font color=\"".$color."\">".$this->lng->txt($val)."</font>";
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
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		}

		// SHOW VALID ACTIONS
		$this->showActions();

		// SHOW POSSIBLE SUB OBJECTS
		$this->showPossibleSubObjects();
	}


	/**
	* save (de-)activation of operations on object
	*/
	function saveObject()
	{
		global $rbacadmin,$rbacreview;

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);
		foreach ($_POST["id"] as $ops_id => $status)
		{
			if ($status == 'enabled')
			{
				if (!in_array($ops_id,$ops_valid))
				{
					$rbacreview->assignPermissionToObject($_GET["obj_id"],$ops_id);
				}
			}

			if ($status == 'disabled')
			{
				if (in_array($ops_id,$ops_valid))
				{
					$rbacreview->deassignPermissionFromObject($_GET["obj_id"],$ops_id);
//					$this->ilias->raiseError("It's not possible to deassign operations",$this->ilias->error_obj->WARNING);
				}
			}
		}
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		exit();
	}


	/**
	* display edit form
	*/
	function editObject()
	{
		global $rbacsystem, $rbacadmin, $tpl;

		// RBAC deactived for testing
		//if ($rbacsystem->checkAccess('write',$_GET["parent"]))
		//{
			//prepare objectlist
			$this->data = array();
			$this->data["data"] = array();
			$this->data["ctrl"] = array();

			$this->data["cols"] = array("", "type", "operation", "description", "status");

			$ops_valid = $rbacadmin->getOperationsOnType($this->obj_id);

			if ($ops_arr = getOperationList('', $a_order, $a_direction))
			{
				$options = array("e" => "enabled","d" => "disabled");

				foreach ($ops_arr as $key => $ops)
				{
					// BEGIN ROW
					if (in_array($ops["ops_id"],$ops_valid))
					{
						$ops_status = 'e';
					}
					else
					{
						$ops_status = 'd';
					}

					$obj = $ops["ops_id"];
					$ops_options = TUtil::formSelect($ops_status,"id[$obj]",$options);

					//visible data part
					$this->data["data"][] = array(
						"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
						"title" => $ops["operation"],
						"description" => $ops["desc"],
						"status" => $ops_options
					);

				}
			}

		//}
		//else
		//{
		//	$this->ilias->raiseError("No permission to edit operations",$this->ilias->error_obj->WARNING);
		//}

		$this->getTemplateFile("edit");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=save.");

		//table header
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header_cell");

			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}

			$this->tpl->setVariable("TEXT", $out);
			$this->tpl->setVariable("LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&order=type&direction=".$_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}

		//table cell
		for ($i=0; $i< count($this->data["data"]); $i++)
		{
			$data = $this->data["data"][$i];

			$num++;

			// color changing
			$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");

			$this->tpl->touchBlock("empty_cell");
			$this->tpl->setCurrentBlock("table_cell");
			//$this->tpl->setVariable("TEXT", "");
			$this->tpl->parseCurrentBlock();

			//data
			foreach ($data as $key => $val)
			{
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
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("save"));
	}


} // END class.TypeDefinitionObjectOut
?>
