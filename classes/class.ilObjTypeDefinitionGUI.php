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


/**
* Class ilObjTypeDefinitionGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.ilObjTypeDefinitionGUI.php,v 1.6 2003/06/05 07:45:43 smeyer Exp $
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
		
		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("type", "operation", "description", "status");

		$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);

		if ($list = getOperationList("",$_GET["order"], $_GET["direction"]))
		{
			foreach ($list as $key => $val)
			{

				if (in_array($val["ops_id"],$ops_valid))
				{
					$ops_status = 'enabled';
				}
				else
				{
					$ops_status = 'disabled';
				}

				//visible data part
				$this->data["data"][] = array(
									"type" 			=> "perm",
									"operation"		=> $val["operation"],
									"description"	=> $val["desc"],
									"status"		=> $ops_status,
									"obj_id"		=> $val["ops_id"]
					);

			}
		} //if typedata

		$this->maxcount = count($this->data["data"]);

		// sorting array
		require_once "./include/inc.sort.php";
		$this->data["data"] = sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"]
											);		

			unset($this->data["data"][$key]["obj_id"]);
		}

		$this->displayList(); 	
	}

	/**
	* display object list
	*
	* @access	public
 	*/
	function displayList()
	{
		global $tree, $rbacsystem;

		require_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();
		
		// title & header columns
		$tbl->setTitle($this->lng->txt("obj_".$this->object->getType())." '".$this->object->getTitle()."'","icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id,"obj_id" => $this->id);
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setMaxCount($this->maxcount);
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");
		
		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

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

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);						
					}

					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array
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

		sendInfo($this->lng->txt("saved_successfully"),true);
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		exit();
	}


	/**
	* display edit form
	*/
	function editObject()
	{
		global $rbacsystem, $rbacadmin, $tpl;

		// TODO: maybe we can skip this check
		if (!$rbacsystem->checkAccess('write',$_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to edit operations",$this->ilias->error_obj->WARNING);
		}

//		$this->getTemplateFile("edit");

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("type", "operation", "description", "status");

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
				$ops_options = ilUtil::formSelect($ops_status,"id[$obj]",$options);

				//visible data part
				$this->data["data"][] = array(
							"type"			=> "perm",
							"operation"		=> $ops["operation"],
							"description"	=> $ops["desc"],
							"status"		=> $ops_status,
							"status_html"	=> $ops_options,
							"obj_id"		=> $val["ops_id"]
				);
			}
		} //if typedata

		$this->maxcount = count($this->data["data"]);

		// sorting array
		require_once "./include/inc.sort.php";
		$this->data["data"] = sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"]
											);		

			unset($this->data["data"][$key]["obj_id"]);
			$this->data["data"][$key]["status"] = $this->data["data"][$key]["status_html"];
			unset($this->data["data"][$key]["status_html"]);
		}					

		// build table
		require_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=save");

		// create table
		$tbl = new ilTableGUI();
		
		// title & header columns
		$tbl->setTitle($this->lng->txt("edit_operations")." ".strtolower($this->lng->txt("of"))." '".$this->object->getTitle()."'","icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id,"obj_id" => $this->id,"cmd" => "edit");
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setMaxCount($this->maxcount);
		
		// SHOW VALID ACTIONS
		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");
		
		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					$this->tpl->setCurrentBlock("text");

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);						
					}

					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("save"));

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array


//////////////////////////////
/*		$this->getTemplateFile("edit");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=save");

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
			$this->tpl->setVariable("LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&order=type&direction=".
									$_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}

		//table cell
		for ($i=0; $i< count($this->data["data"]); $i++)
		{
			$data = $this->data["data"][$i];

			$num++;

			// color changing
			$css_row = ilUtil::switchColor($num,"tblrow1","tblrow2");


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
		*/
	}

} // END class.TypeDefinitionObjectOut
?>
