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
* Class ilObjUserFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjUserFolderGUI.php,v 1.20 2003/11/07 11:11:17 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjUserFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjUserFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "usrf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* list users
	*
	* @access	public
	*/
	function viewObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "name", "last_change");

		if ($usr_data = getObjectList("usr",$_GET["order"], $_GET["direction"]))
		{
			//var_dump("<pre>",$usr_data,"</pre>");exit;
			
			foreach ($usr_data as $key => $val)
			{
				if ($key != ANONYMOUS_USER_ID)
				{
					//visible data part
					$this->data["data"][] = array(
								"type"			=> $val["type"],
								"name"			=> $val["title"]."#separator#".$val["desc"],
								//"email"			=> $val["desc"],
								"last_change"	=> $val["last_update"],
								"obj_id"		=> $val["obj_id"]
							);
				}
			}
		} //if userdata

		$this->maxcount = count($this->data["data"]);

		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"ref_id"	=> $this->id,
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"]
											);		

			unset($this->data["data"][$key]["obj_id"]);
						$this->data["data"][$key]["last_change"] = ilFormat::formatDate($this->data["data"][$key]["last_change"]);
		}
		
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=searchUserForm");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_user"));
		$this->tpl->parseCurrentBlock();

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
		$tbl->setColumnWidth(array("15","15","75%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		//$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
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

				// surpress checkbox for particular object types AND the system role
				if (!$this->objDefinition->hasCheckbox($ctrl["type"]) or $ctrl["obj_id"] == SYSTEM_ROLE_ID or $ctrl["obj_id"] == SYSTEM_USER_ID or $ctrl["obj_id"] == ANONYMOUS_ROLE_ID)
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

					if ($key == "name" || $key == "title")
					{
						$name_field = explode("#separator#",$val);
					}

					if ($key == "title" || $key == "name" || $key == "type")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);

						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

					// process clipboard information"
					if (isset($_SESSION["clipboard"]))
					{
						$cmd = $_SESSION["clipboard"]["cmd"];
						$parent = $_SESSION["clipboard"]["parent"];

						foreach ($_SESSION["clipboard"]["ref_ids"] as $clip_id)
						{
							if ($ctrl["ref_id"] == $clip_id)
							{
								if ($cmd == "cut" and $key == "title")
								{
									$val = "<del>".$val."</del>";
								}

								if ($cmd == "copy" and $key == "title")
								{
									$val = "<font color=\"green\">+</font>  ".$val;
								}

								if ($cmd == "link" and $key == "title")
								{
									$val = "<font color=\"black\"><</font> ".$val;
								}
							}
						}
					}

					$this->tpl->setCurrentBlock("text");

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);
					}

					if ($key == "name" || $key == "title")
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
			$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show possible subobjects (pulldown menu)
	* overwritten to prevent displaying of role templates in local role folders
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		global $rbacsystem;

		$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		
		if (!$rbacsystem->checkAccess('create_user',$this->object->getRefId()))
		{
			unset($d["usr"]);			
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
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		
		return false;
	}
	
	/**
	* confirmObject
	* 
	* @access	public
	*/
	function confirmedDeleteObject()
	{
		global $rbacsystem;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete"),$this->ilias->error_obj->WARNING);
		}
		
		if (in_array($_SESSION["AccountId"],$_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_delete_yourself"),$this->ilias->error_obj->WARNING);		
		}

		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->delete();
		}
			
		// Feedback
		sendInfo($this->lng->txt("user_deleted"),true);	

		header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();			
	}

	/**
	* display deletion confirmation screen
	*/
	function deleteObject()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
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

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
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
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* displays user search form 
	* 
	*
	*/
	function searchUserFormObject ()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_search_form.html");

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("TXT_SEARCH_USER",$this->lng->txt("search_user"));
		$this->tpl->setVariable("TXT_SEARCH_IN",$this->lng->txt("search_in"));
		$this->tpl->setVariable("TXT_SEARCH_USERNAME",$this->lng->txt("username"));
		$this->tpl->setVariable("TXT_SEARCH_FIRSTNAME",$this->lng->txt("firstname"));
		$this->tpl->setVariable("TXT_SEARCH_LASTNAME",$this->lng->txt("lastname"));
		$this->tpl->setVariable("TXT_SEARCH_EMAIL",$this->lng->txt("email"));
		$this->tpl->setVariable("BUTTON_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));	
	}
	
	function searchCancelledObject ()
	{
		sendInfo($this->lng->txt("action_aborted"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");
		exit();
	}
	
	function searchUserObject ()
	{
		global $rbacreview;

		$obj_str = "&obj_id=".$this->obj_id;
	
		$_POST["search_string"] = $_POST["search_string"] ? $_POST["search_string"] : urldecode($_GET["search_string"]);

		if (empty($_POST["search_string"]))
		{
			sendInfo($this->lng->txt("msg_no_search_string"),true);

			header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=searchUserForm");
			exit();
		}

		if (count($search_result = ilObjUser::searchUsers($_POST["search_string"])) == 0)
		{
			sendInfo($this->lng->txt("msg_no_search_result")." ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'",true);

			header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=searchUserForm");
			exit();		
		}
		
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","adm_object.php?ref_id=".$this->ref_id."&cmd=searchUserForm");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_new"));
		$this->tpl->parseCurrentBlock();

		$this->data["cols"] = array("", "login", "firstname", "lastname", "email");

		foreach ($search_result as $key => $val)
		{
			//visible data part
			$this->data["data"][] = array(
							"login"			=> $val["login"],
							"firstname"		=> $val["firstname"],
							"lastname"		=> $val["lastname"],
							"email"			=> $val["email"],
							"obj_id"		=> $val["usr_id"]
						);
		}

		$this->maxcount = count($this->data["data"]);

		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "name")
		{
			$_GET["sort_by"] = "login";
		}

		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
												"ref_id"	=> $this->id,
												"obj_id"	=> $val["obj_id"]
											);
			$tmp[] = $val["obj_id"];
			unset($this->data["data"][$key]["obj_id"]);
		}

		// remember filtered users
		$_SESSION["user_list"] = $tmp;		
	
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway&sort_by=name&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

		// create table
		include_once "./classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("search_result"),"icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = array(
							"ref_id"		=> $this->ref_id,
							"cmd"			=> "searchUser",
							"search_string" => urlencode($_POST["search_string"])
					  		);

		$tbl->setHeaderVars($this->data["cols"],$header_params);
		//$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));	

		$this->showActions(true);
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

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
		
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				//$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();
	
				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?ref_id=7&obj_id=".$ctrl["obj_id"];

					if ($key == "login")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);
						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach
		
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		}
	}
} // END class.ilObjUserFolderGUI
?>
