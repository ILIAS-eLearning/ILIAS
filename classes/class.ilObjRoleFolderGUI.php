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
* Class ilObjRoleFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleFolderGUI extends ilObjectGUI
{
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjRoleFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
        global $ilCtrl;

        $this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));
		
		$this->type = "rolf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
	
	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}

	/**
	* view object
	*
	* @access	public
	*/
	function viewObject ()
	{
		global $rbacreview,$rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.usr_role_assignment.html');
		
		$assignable = false;

		if ($this->object->getId() == ROLE_FOLDER_ID)
		{
            $assignable = true;

		    $_SESSION['filtered_roles'] = isset($_POST['filter']) ? $_POST['filter'] : $_SESSION['filtered_roles'];

            if ($_SESSION['filtered_roles'] == 0)
            {
                $_SESSION['filtered_roles'] = 1;
            }
        
		    $this->tpl->setCurrentBlock("filter");
		    $this->tpl->setVariable("FILTER_TXT_FILTER",$this->lng->txt('filter'));
		    $this->tpl->setVariable("SELECT_FILTER",$this->__buildFilterSelect());
		    $this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
		    $this->tpl->setVariable("FILTER_NAME",'view');
		    $this->tpl->setVariable("FILTER_VALUE",$this->lng->txt('apply_filter'));
		    $this->tpl->parseCurrentBlock();


      		// now get roles depending on filter settings
        	$role_list = $rbacreview->getRolesByFilter($_SESSION["filtered_roles"],$this->object->getId());
        }
        else
        {
            $role_list = $rbacreview->getRoleListByObject($_GET["ref_id"],true);
        }

        $counter = 0;

		foreach ($role_list as $role)
		{
            // exclude templates
            if ($role["type"] == "rolt")
            {
                $path = $this->lng->txt("obj_rolt");
                $rolf = ROLE_FOLDER_ID;
            }
            else
            {
                // fetch context path of role
                $rolf_list = $rbacreview->getFoldersAssignedToRole($role["obj_id"],$assignable);

                if ($this->object->getId() != ROLE_FOLDER_ID)
                {
                    $rolf = $this->object->getRefId();
                }
                else
                {
                    $rolf = $rolf_list[0];
                }
                
    			// only list roles that are not set to status "deleted"
    			if ($rbacreview->isDeleted($rolf))
			    {
                    continue;
                }

                // build context path
                $path = "";

                if ($this->tree->isInTree($rolf))
			    {
                    if ($rolf[0] == ROLE_FOLDER_ID)
                    {
                        $path = $this->lng->txt("global");
                    }
                    else
                    {
				        $tmpPath = $this->tree->getPathFull($rolf);
				        $path = $tmpPath[count($tmpPath)-2]["title"];
				    }
			    }
			    else
			    {
				    $path = "<b>Rolefolder ".$rolf." not found in tree! (Role ".$role["obj_id"].")</b>";
			    }
			}
			
			$disabled = false;
			$checkbox = ilUtil::formCheckBox(0,"role_id[]",$role["obj_id"],$disabled);

			// disable checkbox for system role for the system user
			if ($role["obj_id"] == SYSTEM_ROLE_ID or $role["obj_id"] == ANONYMOUS_ROLE_ID or substr($role["title"],0,3) == "il_")
			{
				$disabled = true;
				$checkbox = "";
			}

            if ($_SESSION["filtered_roles"] != 4)
            {
                $result_set[$counter][] = $checkbox;
            }

            $result_set[$counter][] = "<img src=\"".ilUtil::getImagePath("icon_".$role["type"]."_b.gif")."\" alt=\"".$this->lng->txt("obj_".$role["type"])."\" title=\"".$this->lng->txt("obj_".$role["type"])."\" border=\"0\" vspace=\"0\"/>";
            $result_set[$counter][] = "<a href=\"adm_object.php?ref_id=".$rolf."&obj_id=".$role["obj_id"]."&cmd=perm\">".$role["title"]."</a>";
            $result_set[$counter][] = $role["description"];
            $result_set[$counter][] = $path." (".$role["role_type"].")";;

   			++$counter;
        }

		return $this->__showRolesTable($result_set);
    }



	/**
	* view object
	*
	* @access	public
	*/
	function viewObject2()
	{
		global $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "", "name", "type", "last_change");

		if ($list = $rbacreview->getRoleListByObject($_GET["ref_id"],true))
//		if ($list = $rbacreview->getAssignableRoles(true,false))
		{
			foreach ($list as $key => $val)
			{
				//visible data part
				$this->data["data"][] = array(
						"type"			=> $val["type"],
						"name"			=> $val["title"]."#separator#".$val["desc"],
						"role_type"		=> $val["role_type"],
						"last_change"	=> $val["last_update"],
						"obj_id"		=> $val["obj_id"]
					);
			}
		} //if roledata

		$this->maxcount = count($this->data["data"]);

		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"ref_id"	=> $this->object->getRefId(),
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"],
											// DEFAULT ACTION IS 'permObject()'
											"cmd"		=> "perm"
											);

			unset($this->data["data"][$key]["obj_id"]);
			$this->data["data"][$key]["last_change"] = ilFormat::formatDate($this->data["data"][$key]["last_change"]);
		}

		$this->displayList();
	} //function
	
	/**
	* display object list
	*
	* @access	public
 	*/
	function displayList()
	{
		include_once "./classes/class.ilTableGUI.php";

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
		$tbl->setTitle($this->object->getTitle(),"icon_".$this->object->getType()."_b.gif",
					   $this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}

		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id);
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		$tbl->setColumnWidth(array("15","15","65%","12%","23%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		#$tbl->disable("footer");

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

				// surpress checkbox for particular object types, the system role & anonymous role
				if (!$this->objDefinition->hasCheckbox($ctrl["type"]) or $ctrl["obj_id"] == SYSTEM_ROLE_ID
					or ($ctrl["obj_id"] == ANONYMOUS_ROLE_ID and $this->object->getRefId() == ROLE_FOLDER_ID)
					or strpos($data["name"],"il_") === 0)
				{
					$this->tpl->touchBlock("empty_cell");
				}
				else
				{
					// TODO: this object type depending 'if' could become really a problem!!
					if ($ctrl["type"] == "usr" or $ctrl["type"] == "role" or $ctrl["type"] == "rolt")
					{
						$link_id = $ctrl["obj_id"];
					}
					else
					{
						$link_id = $ctrl["ref_id"];
					}
	
					$this->tpl->setCurrentBlock("checkbox");
					$this->tpl->setVariable("CHECKBOX_ID", $link_id);
					$this->tpl->setVariable("CSS_ROW", $css_row);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?";

					$n = 0;

					foreach ($ctrl as $key2 => $val2)
					{
						$link .= $key2."=".$val2;

						if ($n < count($ctrl)-1)
						{
					    	$link .= "&";
							$n++;
						}
					}

					if ($key == "name")
					{
						$name_field = explode("#separator#",$val);
					}
					
					if ($key == "type" || $key == "name")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);

						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

					$this->tpl->setCurrentBlock("text");

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);
					}

					if ($key == "name")
					{
						$this->tpl->setVariable("TEXT_CONTENT", $name_field[0]);
						
						$this->tpl->setCurrentBlock("subtitle");
						$this->tpl->setVariable("DESC", $name_field[1]);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setVariable("TEXT_CONTENT", $val);
					}

					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for

		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show possible action (form buttons)
	*
	* @param	boolean
	* @access	public
 	*/
	function showActions($with_subobjects = false)
	{
		global $rbacsystem;

		$operations = array();

		if ($this->actions == "")
		{
			$d = $this->objDefinition->getActions($_GET["type"]);
		}
		else
		{
			$d = $this->actions;
		}

		foreach ($d as $row)
		{
			if ($rbacsystem->checkAccess($row["name"],$this->object->getRefId()))
			{
				$operations[] = $row;
			}
		}

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $val["name"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($with_subobjects === true)
		{
			$subobjs = $this->showPossibleSubObjects();
		}

		if ((count($operations) > 0) or $subobjs === true)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* confirmObject
	* handles deletion of roles and role templates NOT the rolefolder object itself!!
	* 
	* @access	public
	*/
	function confirmedDeleteObject()
	{
		global $rbacsystem,$rbacreview;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$perform_delete = false;
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
						 $not_deletable,$this->ilias->error_obj->MESSAGE);
		}

		$return_loc = $this->tree->getParentId($this->object->getRefId());
		
		$feedback["count"] = count($_SESSION["saved_post"]);
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// instatiate correct object class (role or rolt)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);

			if ($obj->getType() == "role")
			{
                $rolf_list = $rbacreview->getFoldersAssignedToRole($id,true);
				$obj->setParent($rolf_list[0]);
				$feedback["role"] = true;
			}
			else
			{
				$feedback["rolt"] = true;
			}
			$obj->delete();
			unset($obj);
		}

		// set correct return location if rolefolder is removed
		$return_loc = ilObject::_exists($this->object->getId()) ? $_GET["ref_id"] : $return_loc;
	
		// Compose correct feedback
		if ($feedback["count"] > 1)
		{
			if ($feedback["role"] === true)
			{
				if ($feedback["rolt"] === true)
				{
					sendInfo($this->lng->txt("msg_deleted_roles_rolts"),true);					
				}
				else
				{
					sendInfo($this->lng->txt("msg_deleted_roles"),true);						
				}
			}
			else
			{
				sendInfo($this->lng->txt("msg_deleted_rolts"),true);						
			}
		}
		else
		{
			if ($feedback["role"] === true)
			{
				sendInfo($this->lng->txt("msg_deleted_role"),true);	
			}
			else
			{
			sendInfo($this->lng->txt("msg_deleted_rolt"),true);	
			}	
		}
			
		ilUtil::redirect("adm_object.php?ref_id=".$return_loc);
	}
	
	/**
	* role folders are created automatically
	* POSSIBLE DEPRECATED !!!
	* @access	public
	*/
	function createObject()
	{
		$this->object->setTitle($this->lng->txt("obj_".$this->object->getType()."_local"));
		$this->object->setDescription("obj_".$this->object->getType()."_local_desc");
		
		$this->saveObject();
	}
	
	/**
	* display deletion confirmation screen
	*
	* @access	public
	*/
	function deleteObject()
	{
		if(!isset($_POST["role_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["role_id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["role_id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach ($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if ($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* ???
	* TODO: what is the purpose of this function?
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		sendinfo($this->lng->txt("saved_successfully"),true);
		
		ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=perm");
	}
	
	/**
	* show possible subobjects (pulldown menu)
	* overwritten to prevent displaying of role templates in local role folders
	*
	* @access	public
 	*/
	function showPossibleSubObjects($a_tpl)
	{
		global $rbacsystem;

		$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		
		if ($this->object->getRefId() != ROLE_FOLDER_ID or !$rbacsystem->checkAccess('create_rolt',ROLE_FOLDER_ID))
		{
			unset($d["rolt"]);
		}
		
		if (!$rbacsystem->checkAccess('create_role',$this->object->getRefId()))
		{
			unset($d["role"]);			
		}

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					$subobj[] = $row["name"];
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$a_tpl->setCurrentBlock("add_object");
			$a_tpl->setVariable("SELECT_OBJTYPE", $opts);
			$a_tpl->setVariable("BTN_NAME", "create");
			$a_tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$a_tpl->parseCurrentBlock();
			
			return $a_tpl;
		}
		
		return false;
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// role folders are created automatically
		$_GET["new_type"] = $this->object->getType();
		$_POST["Fobject"]["title"] = $this->object->getTitle();
		$_POST["Fobject"]["desc"] = $this->object->getDescription();

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// put here your object specific stuff	

		// always send a message
		sendInfo($this->lng->txt("rolf_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}

	function __showRolesTable($a_result_set)
	{
        global $rbacsystem;

		$actions = array("delete"  => $this->lng->txt("delete"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();
		
		$tpl = $this->showPossibleSubObjects($tpl);

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setVariable("COLUMN_COUNTS",($_SESSION["filtered_roles"] == 4) ? 4 : 5);
		if ($_SESSION["filtered_roles"] != 4)
		{
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));


			foreach ($actions as $name => $value)
			{
				$tpl->setCurrentBlock("tbl_action_btn");
				$tpl->setVariable("BTN_NAME",$name);
				$tpl->setVariable("BTN_VALUE",$value);
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);


		$this->ctrl->setParameter($this,"cmd","view");

		// title & header columns
		$tbl->setTitle($this->lng->txt("roles"),"icon_role_b.gif",$this->lng->txt("roles"));

		if ($_SESSION["filtered_roles"] == 4)
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("role"),
									   $this->lng->txt("description"),$this->lng->txt("context")));
			$tbl->setHeaderVars(array("type","title","description","context"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("","30%","40%","30%"));
		} 
		else 
		{
			$tbl->setHeaderNames(array("",$this->lng->txt("type"),
									   $this->lng->txt("role"),
									   $this->lng->txt("description"),
									   $this->lng->txt("context")));
			$tbl->setHeaderVars(array("","type","title","description","context"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("","","30%","40%","30%"));
		}
		
		$this->__setTableGUIBasicData($tbl,$a_result_set,"view");
		$tbl->render();
		$this->tpl->setVariable("ROLES_TABLE",$tbl->tpl->get());

		return true;
	}

	function &__initTableGUI()
	{
		include_once "class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			default:
                if (!$_GET["sort_by"] or $_GET["sort_by"] == "name")
                {
                    $_GET["sort_by"] = "title";
                }
                
	           	$order = $_GET["sort_by"];
				break;
		}

        //$tbl->enable("hits");
		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function __unsetSessionVariables()
	{
        // empty
	}

	function __buildFilterSelect()
	{
		$action[1] = $this->lng->txt('all_roles');
		$action[2] = $this->lng->txt('all_global_roles');
		$action[3] = $this->lng->txt('all_local_roles');
		$action[4] = $this->lng->txt('internal_local_roles_only');
		$action[5] = $this->lng->txt('non_internal_local_roles_only');
		$action[6] = $this->lng->txt('role_templates_only');
		
		return ilUtil::formSelect($_SESSION['filtered_roles'],"filter",$action,false,true);
	}
	
	function hitsperpageObject()
	{
        parent::hitsperpageObject();
        $this->viewObject();
	}
} // END class.ilObjRoleFolderGUI
?>
