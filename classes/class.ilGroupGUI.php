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
require_once("include/inc.header.php");
require_once("classes/class.ilObjGroupGUI.php");
require_once("classes/class.ilGroupExplorer.php");
require_once("classes/class.ilTableGUI.php");
require_once("classes/class.ilObjGroup.php");

/**
* Class ilGroupGUI
*
* GUI class for Group management
*
* @author	Martin Rus <mrus@smail.uni-koeln.de>
* @author	Sascha Hofmann <shofmann@databay.de>
*
* @version	$Id$
* @package	ilias-core
*/
class ilGroupGUI extends ilObjectGUI
{
	var $tpl;
	var $lng;
	var $objDefinition;
	var $tree;
	var $ilias;
	var $object;
	var $grp_tree;
	var $grp_id;
	/**
	* Constructor
	* @access	public
	*/

	function ilGroupGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $tpl, $ilias, $lng, $tree, $rbacsystem, $objDefinition;

		$this->type ="grp";
		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->formaction = array();
		$this->return_location = array();

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;

		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];
		
		if ($_GET["offset"] == "")
		{
			$_GET["offset"] = 0;
		}
		
		$_GET["offset"] = intval($_GET["offset"]);
		$_GET["limit"] = intval($_GET["limit"]);

		if ($_GET["limit"] == 0)
		{
			$_GET["limit"] = 10;	// TODO: move to user settings
		}
		if (empty($_GET["sort_by"]))
		{
			$_GET["sort_by"]= "title";
		}

		// get the object
		$this->assignObject();
		$this->object =& $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		$this->lng =& $this->object->lng;

		$this->grp_id = ilUtil::getGroupId($_GET["ref_id"]);

		$this->grp_tree = new ilTree($this->grp_id,$this->grp_id);
		$this->grp_tree->setTableNames("grp_tree","object_data","object_reference");

		//return to the same place , where the action was executed
		$this->setReturnLocation("cut","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("clear","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("copy","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("link","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("paste","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("cancelDelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("cancel","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("confirmedDelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("removeFromSystem","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		$this->setReturnLocation("undelete","group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);

		$cmd = $_GET["cmd"];
		//var_dump ($cmd);
		if($cmd == "")
		{
			$cmd = "view";
		}
		if (isset($_POST["cmd"]))
		{
			$cmd = key($_POST["cmd"]);
			$fullcmd = $cmd."Object";

			// only createObject!!
			$this->$fullcmd();
			exit();
		}

		$this->$cmd();
	}

	/**
	* loads basic template file for group-enviroment
	*
	* @param	access public
	* @param	boolean variable, if not set or set true tabs are displayed
	* @param	multidimensional array for additional tabs; is only passed on;optional
	* @param	script that is used for linking in loacator;optional; default: "group.php"
	**/
	function prepareOutput($tabs=true, $addtab="")
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.group_basic.html");
		//$title = $this->object->getTitle();
		infoPanel();
		sendInfo();
		$this->setAdminTabs($tabs, $addtab);
		$this->setLocator();
	}

	/**
	* set admin tabs
	* @access	public
	* @param	boolean; whether standard tabs are set or not
	* @param	multdimensional array for additional tabs; optional
	*/
	function setAdminTabs($settabs=false, $addtabs="")
	{

		if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
		{
			$ftabtype = "tabactive";
			$ttabtype = "tabinactive";
		}
		else
		{
			$ftabtype = "tabinactive";
			$ttabtype = "tabactive";
		}
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

		/*if ($settabs)
		{

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $ttabtype);
			$this->tpl->setVariable("TAB_TARGET", "bottom");
			$this->tpl->setVariable("TAB_LINK", "group.php?viewmode=tree&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("treeview"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $ftabtype);
			$this->tpl->setVariable("TAB_TARGET", "bottom");
			$this->tpl->setVariable("TAB_LINK", "group.php?viewmode=flat&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt("flatview"));
			$this->tpl->parseCurrentBlock();
		}*/

		if (!empty($addtabs))
		{
			foreach($addtabs as $addtab)
			{
				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable("TAB_TYPE", $addtab["ftabtype"]);
				$this->tpl->setVariable("TAB_TARGET", $addtab["target"]);
				$this->tpl->setVariable("TAB_LINK", "group.php?".$addtab["tab_cmd"]);
				$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($addtab["tab_text"]));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	function AccessDenied()
	{
		global $ilias, $rbacsystem;
		$grpObj = new ilObjGroup($_GET["ref_id"],true);
		$grp	=& $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

		$_SESSION["saved_post"]["user_id"][0] = $this->ilias->account->getId();
		$_SESSION["status"] 	= 0;

		$tab[0] = array ();
		$tab[0]["tab_cmd"] = "cmd=confirmedAssignMemberObject&ref_id=".$_GET["ref_id"]; 	//link for tab
		$tab[0]["ftabtype"] = "tabinactive"; 					//tab is marked
		$tab[0]["target"] = "bottom";  						//target-frame of tab_cmd
		$tab[0]["tab_text"] = $this->lng->txt("group_access");

		if($grpObj->getGroupStatus() == 0) //open
		{
			$stat = "offene Gruppe";
			$msg = "Sie sind bislang kein Mitglied dieser Gruppe. Zur besseren Verwaltung der Gruppenmitglieder ist es jedoch notwendig, das Sie der gewünschten Gruppe beitreten.
				<br>Als Gruppenmitglied haben Sie folgende Vorteile:
				<br>- Sie werden über Aktualisierungen informiert
				<br>- Sie haben Zugriff auf gruppenspezifische Objekte wie Diskussionsforen, Lerneinheiten, etc.
 				<br><br>Sie können Ihre Mitgliedschaft jederzeit wieder aufheben.";
		}
		else
		{
			$msg = "Die von Ihnen angewählte Gruppe ist geschlossen, dass heißt eine Mitgliedschaft ist zur Zeit nicht möglich!";
			$stat = "geschlossene Gruppe";
		}

		$this->prepareOutput(false, $tab);
		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_access"));
		$this->tpl->addBlockFile("CONTENT", "tbldesc", "tpl.grp_accessdenied.html");
		$this->tpl->setVariable("TXT_HEADER","Zugriff verweigert!");
		$this->tpl->setVariable("TXT_MESSAGE",$msg);

		$this->tpl->setVariable("TXT_GRP_NAME", $this->lng->txt("grp_name").":");
		$this->tpl->setVariable("GRP_NAME",$grp->getTitle());
		$this->tpl->setVariable("TXT_GRP_DESC",$this->lng->txt("grp_desc").":");
		$this->tpl->setVariable("GRP_DESC",$grp->getDescription());
		$this->tpl->setVariable("TXT_GRP_OWNER",$this->lng->txt("owner").":");
		$this->tpl->setVariable("GRP_OWNER",$grp->getOwner());
		$this->tpl->setVariable("TXT_GRP_STATUS",$this->lng->txt("grp_status").":");
		$this->tpl->setVariable("GRP_STATUS", $stat);

		$this->tpl->parseCurrentBlock();

/*
		$this->tpl->setVariable("TXT_COL1","Beschreibung:");
		$this->tpl->setVariable("TXT_COL2",$grp->getDescription());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TXT_COL1","Zugang:");
		$this->tpl->setVariable("TXT_COL2",$stat);
		$this->tpl->parseCurrentBlock();
*/

//	G R O U P  D E T A I L S
		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_details"));
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&parent_non_rbac_id=".$this->object->getRefId());
		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

		$cont_arr = $this->getContent();
		$this->tpl->addBlockfile("CONTENT", "group_table", "tpl.table.html");

		$maxcount = count($cont_arr);
		$cont_arr = sortArray($cont_arr,$_GET["sort_by"],$_GET["sort_order"]);
		$cont_arr = array_slice($cont_arr,$_GET["offset"],$_GET["limit"]);

		//check if user got "write" permissions; if so $access is set true to prevent further database queries in this function
		 $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				//temporary solution later rolf should be viewablle for grp admin
				if ($cont_data["type"] != "rolf")
				{
					$this->tpl->setCurrentBlock("tbl_content");
					$newuser = new ilObjUser($cont_data["owner"]);
					// change row color
					$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
					$num++;

					$obj_icon = "icon_".$cont_data["type"]."_b.gif";

					$this->tpl->setVariable("TITLE", $cont_data["title"]);
//					$this->tpl->setVariable("LINK_TARGET", $link_target);
					$this->tpl->setVariable("IMG", $obj_icon);
					$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
					$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
					$this->tpl->setVariable("OWNER", $newuser->getFullName());
					$this->tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cont_data["last_update"]));
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_any_objects"));
			$this->tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();
		// buttons in bottom-bar
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("owner"),$this->lng->txt("last_change"),$this->lng->txt("context")));
		$tbl->setHeaderVars(array("title","description","status","last_change","context"), array("cmd"=>"accessDenied", "ref_id"=>$_GET["ref_id"]));
		$tbl->setColumnWidth(array("7%","10%","15%","15%","22%"));

		// title & header columns
		$tbl->setTitle($this->lng->txt("group_details")." - ".$this->object->getTitle(),"icon_grp_b.gif", $this->lng->txt("group_details"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		// render table
		$tbl->render();
		$this->tpl->show();
	}
/*
	function AccessDenied()
	{
		global $ilias;
		$grpObj = new ilObjGroup($_GET["ref_id"],true);
		$grp	=& $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

		$_SESSION["saved_post"]["user_id"][0] = $this->ilias->account->getId();
		$_SESSION["status"] 	= 0;

		$tab[0] = array ();
		$tab[0]["tab_cmd"] = "cmd=confirmedAssignMemberObject&ref_id=".$_GET["ref_id"]; 	//link for tab
		$tab[0]["ftabtype"] = "tabinactive"; 					//tab is marked
		$tab[0]["target"] = "bottom";  						//target-frame of tab_cmd
		$tab[0]["tab_text"] = $this->lng->txt("group_access");


		if($grpObj->getGroupStatus() == 0) //open
		{
			$stat = "offene Gruppe";
			$msg = "Sie sind bislang kein Mitglied dieser Gruppe. Zur besseren Verwaltung der Gruppenmitglieder ist es jedoch notwendig, das Sie der gewünschten Gruppe beitreten.
				<br>Als Gruppenmitglied haben Sie folgende Vorteile:
				<br>- Sie werden über Aktualisierungen informiert
				<br>- Sie haben Zugriff auf gruppenspezifische Objekte wie Diskussionsforen, Lerneinheiten, etc.
 				<br><br>Sie können Ihre Mitgliedschaft einfach durch anklicken des folgenden Symbols: Mitgliedschaft aufheben.";
		}
		else
		{
			$msg = "Die von Ihnen angewählte Gruppe ist geschlossen, dass heißt eine Mitgliedschaft ist zur Zeit nicht möglich!";
			$stat = "geschlossene Gruppe";
		}
		$this->prepareOutput(false, $tab);
		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_access"));

		$this->tpl->addBlockFile("CONTENT", "message", "tpl.grp_accessdenied.html");
		$this->tpl->setVariable("TXT_HEADER","Zugriff verweigert!");
		$this->tpl->setVariable("TXT_MESSAGE",$msg);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("tblcontent");
		$this->tpl->setVariable("TXT_COL1","Gruppenname:");
		$this->tpl->setVariable("TXT_COL2",$grp->getTitle());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TXT_COL1","Beschreibung:");
		$this->tpl->setVariable("TXT_COL2",$grp->getDescription());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TXT_COL1","Zugang:");
		$this->tpl->setVariable("TXT_COL2",$stat);
		$this->tpl->parseCurrentBlock();



		$this->tpl->show();

	}
*/
	/**
	* calls current view mode (tree frame or list)
	*/
	function view()
	{
		global $rbacsystem,$ilias;

		if (isset($_GET["viewmode"]))
		{
			$_SESSION["viewmode"] = $_GET["viewmode"];
		}
		else if(!isset($_SESSION["viewmode"]))
			$_SESSION["viewmode"] = "flat";	//default viewmode
		
		//exception
		if($this->object->getType()=="fold")
		{
			$tmp_object =& $ilias->obj_factory->getInstanceByRefId(ilUtil::getGroupId($_GET["ref_id"]));
		}
		else
		{
			$tmp_object = $this->object;
		}
		if (!$rbacsystem->checkAccess('read',$_GET["ref_id"]) || !$tmp_object->isMember())
		{
			header("location: group.php?cmd=AccessDenied&ref_id=".$_GET["ref_id"]);
		}

		// tree frame
		if ($_SESSION["viewmode"] == "tree")
		{
			$this->tpl = new ilTemplate("tpl.group.html", false, false);
			$this->tpl->setVariable ("EXP", "group.php?cmd=explorer&ref_id=".$_GET["ref_id"]."&expand=".$_GET["expand"]);
			$this->tpl->setVariable ("SOURCE", "group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
			$this->tpl->show();
		}
		else	// list
		{
			$this->show_content();
		}
	}

	function viewObject()
	{
		//necessary for gateway calls
		$this->view();
	}


	function explorer()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		//TODO: is obsolet, wenn man an $exp->setOutput(0); die ref_id der Gruppe bergeben kann

		$exp = new ilGroupExplorer("group.php",$this->grp_id);

		if ($_GET["expand"] == "")
		{
			$expanded = $this->grp_id;
		}
		else
		{
			$expanded = $_GET["expand"];
		}

		$exp->setExpand($expanded);
		$exp->setExpandTarget("group.php?cmd=explorer&ref_id=".$this->grp_id);

		//filter object types
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("frm");
		$exp->addFilter("lm");
		$exp->addFilter("slm");
		$exp->addFilter("glo");
		$exp->addFilter("crs");
		$exp->addFilter("fold");
		$exp->addFilter("file");
		$exp->setFiltered(true);

		//build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();
		$obj_grp = & $this->ilias->obj_factory->getInstanceByRefId($this->grp_id);
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER",ilUtil::shortenText($this->lng->txt("obj_grp").":".$obj_grp->getTitle(), "50", true));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->parseCurrentBlock();

		$this->tpl->show();
	}

	/**
	* edit Group
	* @access public
	*/
	function editGroup()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$this->prepareOutput(false);
		$this->tpl->setVariable("HEADER", $this->lng->txt("grp_edit"));
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

		$data = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];
		}
		else
		{
			$data["title"] = $this->object->getTitle();
			$data["desc"] = $this->object->getDescription();
		}

		$this->tpl->addBlockFile("CONTENT", "edit", "tpl.grp_edit.html");

		foreach ($data as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		$stati = array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));

		//build form
		$grp_status = $this->object->getGroupStatus();
		$opts = ilUtil::formSelect($grp_status,"group_status",$stati,false,true);
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$this->object->getRefId());
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", "bottom");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_CANCEL", "show_content" );
		$this->tpl->setVariable("CMD_SUBMIT", "updateGroupStatus");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->show();
	}

	function getContent()
	{
		global $rbacsystem;
		$cont_arr = array();
		$objects = $this->grp_tree->getChilds($this->object->getRefId(),"title"); //provides variable with objects located under given node

//		print_r($this->object);
		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				//if ($rbacsystem->checkAccess('visible',$this->object["ref_id"]) or $this->object["type"] == "fold" )
				if ($rbacsystem->checkAccess('visible',$this->object->getRefId()) or $this->object->getType() == "fold" )
				{
					$cont_arr[$key] = $object;
					//var_dump($cont_arr[$key]);
				}
			}
		}
		return $cont_arr;
	}

	/*
	*function displays content of a group given by its ref_id
	*
	* @access	public
	*/
	function show_content()
	{
		global $rbacsystem;

		$tab = array();

		if (!isset($_SESSION["viewmode"]) OR $_SESSION["viewmode"] == "flat")
		{
			$tab[0] = array ();
			$tab[0]["tab_cmd"] = "cmd=view&viewmode=tree&ref_id=".$_GET["ref_id"]; 	//link for tab
			$tab[0]["ftabtype"] = "tabinactive"; 					//tab is marked
			$tab[0]["target"] = "bottom";  						//target-frame of tab_cmd
			$tab[0]["tab_text"] = 'treeview';
		
			$tab[1] = array ();
			$tab[1]["tab_cmd"] = "cmd=view&viewmode=flat&ref_id=".$_GET["ref_id"];//link for tab
			$tab[1]["ftabtype"] = "tabactive";  					//tab is marked
			$tab[1]["target"] = "bottom";  						//target-frame of tab_cmd
			$tab[1]["tab_text"] ='flatview';
		
		
		
		}
		else
		{
			$tab[0] = array ();
			$tab[0]["tab_cmd"] = "cmd=view&viewmode=tree&ref_id=".$_GET["ref_id"]; 	//link for tab
			$tab[0]["ftabtype"] = "tabactive"; 					//tab is marked
			$tab[0]["target"] = "bottom";  						//target-frame of tab_cmd
			$tab[0]["tab_text"] = 'treeview';
			
			$tab[1] = array ();
			$tab[1]["tab_cmd"] = "cmd=view&viewmode=flat&ref_id=".$_GET["ref_id"];//link for tab
			$tab[1]["ftabtype"] = "tabinactive";  					//tab is marked
			$tab[1]["target"] = "bottom";  						//target-frame of tab_cmd
			$tab[1]["tab_text"] ='flatview';
		}

		$tab[2] = array ();
		$tab[2]["tab_cmd"]  = 'cmd=groupmembers&ref_id='.$this->grp_id;			//link for tab
		$tab[2]["ftabtype"] = 'tabinactive';						//tab is marked
		$tab[2]["target"]   = "_self";							//target-frame of tab_cmd
		$tab[2]["tab_text"] = 'group_members';						//tab -text

		//check if trash is filled
		$objects = $this->grp_tree->getSavedNodeData($_GET["ref_id"]);

		if (count($objects) > 0)
		{
			$tab[3] = array ();
			$tab[3]["tab_cmd"]  = 'cmd=trash&ref_id='.$_GET["ref_id"];		//link for tab
			$tab[3]["ftabtype"] = 'tabinactive';					//tab is marked
			$tab[3]["target"]   = "bottom";						//target-frame of tab_cmd
			$tab[3]["tab_text"] = 'trash';						//tab -text
		}

		if( $rbacsystem->checkAccess('delete',$_GET["ref_id"]) && $this->object->getType() == 'grp')
		{
			$tab[4] = array ();
			$tab[4]["tab_cmd"]  = 'cmd=editGroup&ref_id='.$_GET["ref_id"];		//link for tab
			$tab[4]["ftabtype"] = 'tabinactive';					//tab is marked
			$tab[4]["target"]   = "_self";						//target-frame of tab_cmd
			$tab[4]["tab_text"] = "grp_edit";				//tab -text
		}

		$this->prepareOutput(false, $tab);
		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_details"));

		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&parent_non_rbac_id=".$this->object->getRefId());

		$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

		// set offset & limit
/*
		$cont_arr = array();
		$objects = $this->grp_tree->getChilds($this->object->getRefId(),"title"); //provides variable with objects located under given node

		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				if ($rbacsystem->checkAccess('visible',$object["ref_id"]) or $object["type"] == "fold" )
				{
					$cont_arr[$key] = $object;
					//var_dump($cont_arr[$key]);
				}
			}
		}
*/
		$cont_arr = $this->getContent();

		$maxcount = count($cont_arr);
		$cont_arr = sortArray($cont_arr,$_GET["sort_by"],$_GET["sort_order"]);
		$cont_arr = array_slice($cont_arr,$_GET["offset"],$_GET["limit"]);

		// load template for table
		$this->tpl->addBlockfile("CONTENT", "group_table", "tpl.table.html");
		// load template for table content data
		$access = false;

		//check if user got "write" permissions; if so $access is set true to prevent further database queries in this function
		if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			 $access = true;
			 $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows_checkbox.html");
		}
		else
		{
			 $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");
		}

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;

			foreach ($cont_arr as $cont_data)
			{
				//temporary solution later rolf should be viewablle for grp admin
				if ($cont_data["type"] != "rolf")
				{
					$this->tpl->setCurrentBlock("tbl_content");
					$newuser = new ilObjUser($cont_data["owner"]);
					// change row color
					$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
					$num++;

					if ($cont_data["type"] == "lm" || $cont_data["type"] == "frm" )
					{
						$link_target = "_top";
					}
					else
					{
						$link_target = "_self";
					}

					$obj_link = $this->getURLbyType($cont_data);

					$obj_icon = "icon_".$cont_data["type"]."_b.gif";

					if ($access and $cont_data["type"] != "fold" and $cont_data["type"] != "file")
					{
						$this->tpl->setVariable("CHECKBOX", ilUtil::formCheckBox(0,"id[]",$cont_data["ref_id"]));
					}
					$this->tpl->setVariable("TITLE", $cont_data["title"]);
					$this->tpl->setVariable("LO_LINK", $obj_link);
					$this->tpl->setVariable("LINK_TARGET", $link_target);
					$this->tpl->setVariable("IMG", $obj_icon);
					$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
					$this->tpl->setVariable("DESCRIPTION", $cont_data["description"]);
					$this->tpl->setVariable("OWNER", $newuser->getFullName());
					$this->tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cont_data["last_update"]));
					//TODO
					if ($cont_data["ref_id"] != -1)
					{
						$this->tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["child"]));
					}

					$this->tpl->parseCurrentBlock();
				}
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_any_objects"));
			$this->tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();
		// buttons in bottom-bar
		if ($access)
		{
			$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("owner"),$this->lng->txt("last_change"),$this->lng->txt("context")));
			$tbl->setHeaderVars(array("checkbox","title","description","status","last_change","context"), array("cmd"=>"show_content", "ref_id"=>$_GET["ref_id"]));
			$tbl->setColumnWidth(array("3%","7%","10%","15%","15%","22%"));
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->SetVariable("COLUMN_COUNTS", "6");
			$this->showActions(true);
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("owner"),$this->lng->txt("last_change"),$this->lng->txt("context")));
			$tbl->setHeaderVars(array("title","description","status","last_change","context"), array("cmd"=>"show_content", "ref_id"=>$_GET["ref_id"]));
			$tbl->setColumnWidth(array("7%","10%","15%","15%","22%"));
		}

		// title & header columns
		$tbl->setTitle($this->lng->txt("group_details")." - ".$this->object->getTitle(),"icon_grp_b.gif", $this->lng->txt("group_details"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		// render table
		$tbl->render();
		$this->tpl->show();
	}

	/*
	* function returns specific link-url depending on object-type
	*
	* @access	public
	*/
	function getURLbyType($cont_data)
	{
		switch ($cont_data["type"])
		{
	  		case "frm":
				$URL = "forums_threads_liste.php?ref_id=".$cont_data["ref_id"];
				break;

			case "crs":
				$URL = "lo_list.php?cmd=displayList&ref_id=".$cont_data["ref_id"];
				break;

			case "lm":
				$URL = "content/lm_presentation.php?ref_id=".$cont_data["ref_id"];
				break;

			case "fold":
				$URL = "group.php?ref_id=".$cont_data["ref_id"]."&cmd=show_content";
				break;

			case "file":
				$URL = "group.php?cmd=get_file&ref_id=".$cont_data["ref_id"];
				break;
		}

		return $URL;
	}

	/**
	* get
	* @access	public
	* @param	integer	group id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function getContextPath ($a_endnode_id, $a_startnode_id = 0)
	{
		$path = "";

		if (!$a_startnode_id)
		{
			$a_startnode_id = $this->grp_id;
		}

		$tmpPath = $this->grp_tree->getPathFull($a_endnode_id, $a_startnode_id);

		// count -1, to exclude the forum itself
		for ($i = 0; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];
		}

		return $path;
	}

	/**
	* remove member object from group preparation(messages,link)
	* @access	public
	*/
	function removeMemberObject()
	{
		$user_ids = array();

		if(isset($_POST["user_id"]))
			$user_ids = $_POST["user_id"];
		else if(isset($_GET["mem_id"]))
			$user_ids = $_GET["mem_id"];
		if(isset($user_ids))
		{
			$confirm = "confirmedRemoveMember";
			$cancel  = "canceldelete";
			$info	 = "info_delete_sure";
			$status  = "";
			$this->confirmation($user_ids, $confirm, $cancel, $info, $status,"n");
			$this->tpl->show();
		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("location: group.php?cmd=show_content&ref_id=".$_GET["ref_id"]);
		}
	}

	/**
	* remove members from group
	* @access public
	*/
	function confirmedRemoveMemberObject()
	{
		global $rbacsystem;

		if (isset($_SESSION["saved_post"]["user_id"]) )
		{
			foreach($_SESSION["saved_post"]["user_id"] as $mem_id)
			{
				$newGrp = new ilObjGroup($_GET["ref_id"],true);

				if ($rbacsystem->checkAccess('leave',$_GET["ref_id"]))
				{
					//check ammount of members
					if (count($newGrp->getGroupMemberIds()) == 1)
					{
						if ($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
						{
							//GROUP DELETE
							$this->ilias->raiseError("Group would be deleted!",$this->ilias->error_obj->MESSAGE);
						}
						else
						{
							$this->ilias->raiseError("You do not have the permissions to delete this group!",$this->ilias->error_obj->MESSAGE);
						}
					}
					else
					{
						//MEMBER LEAVES GROUP
						if ($newGrp->isMember($mem_id) && !$newGrp->isAdmin($mem_id))
						{
							if (!$newGrp->leave($mem_id))
							{
								$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
							}
						}
						elseif ($newGrp->isAdmin($mem_id)) //ADMIN LEAVES GROUP
						{
							if(count($newGrp->getGroupAdminIds()) <= 1 )
							{
								$this->ilias->raiseError("At least one group administrator is required! Please entitle a new group administrator first ! ",$this->ilias->error_obj->WARNING);
							}
							elseif (!$newGrp->leave($mem_id))
							{
								$this->ilias->raiseError("Error while attempting to discharge user!",$this->ilias->error_obj->MESSAGE);
							}
						}
					}
				}
				else
				{
					$this->ilias->raiseError("You are not allowed to leave this group!",$this->ilias->error_obj->MESSAGE);
				}
			}
		}

		unset($_SESSION["saved_post"]);
		header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
	}

	/**
	* displays confirmation form that is used from several methods(deleteObject,newMembers, removeMember, ChangeMemberStatus)
	* works like a gateway
	* @access public
	* @param	id of the displayed objects
	* @param	confirm = name of method that is called after confirmation
	* @param	cancel  = name of method that is called when canceled
	* @param	info	= message
	* @param	status  = userstatus of new members[member=0|admin=1]
	* @param	call_by_ref = message
	*
	*/
	function confirmation($user_id="", $confirm, $cancel, $info="", $status="",$ref_IDs="n")
	{
		$num =0;
		$this->prepareOutput(false);
		$this->tpl->setVariable("HEADER", $this->lng->txt("objs_delete"));
		sendInfo ($this->lng->txt($info));
		$this->tpl->addBlockFile("CONTENT", "confirmation", "tpl.table.html");
		$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&parent_on_rbac_id=".$_GET["parent_non_rbac_id"]."&gateway=true");
		$this->tpl->addBlockFile("TBL_CONTENT", "confirmcontent","tpl.grp_tbl_confirm.html" );

		$this->tpl->setCurrentBlock("confirmcontent");

		// set offset & limit
		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 10;	// TODO: move to user settings
		}
		if ($offset == "")
		{
			$offset = 0;	// TODO: move to user settings
		}

		if (is_array($user_id))

		{
			$maxcount = count ($user_id);
			foreach ($user_id as $id)
			{
				if($ref_IDs == "y")
					$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);
				else
					$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

				$this->tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$this->tpl->setVariable("DESCRIPTION", $obj_data->getDescription());
				$this->tpl->setVariable("TITLE", $obj_data->getTitle());
				$this->tpl->setVariable("TYPE", ilUtil::getImageTagByType($obj_data->getType(),$this->tpl->tplPath));
				$this->tpl->setVariable("LAST_UPDATE", $obj_data->getLastUpdateDate());
				$this->tpl->parseCurrentBlock();
				unset($obj_data);
			}
		}
		else
		{
			$maxcount = 1;
			if($ref_IDs == "y")
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($user_id);
			else
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($user_id);

			$this->tpl->setVariable("DESCRIPTION", $obj_data->getDescription());
			$this->tpl->setVariable("TITLE", $obj_data->getTitle());
			$this->tpl->setVariable("TYPE", ilUtil::getImageTagByType($obj_data->getType(),$this->tpl->tplPath));
			$this->tpl->setVariable("LAST_UPDATE", $obj_data->getLastUpdateDate());
			$this->tpl->parseCurrentBlock();
		}

		// the variable $_SESSION["saved_post"] is aleady set  for the method  "confirmedDelete"
		if ($confirm != "confirmedDelete")
		{
			if (is_array($user_id))
			{
				$_SESSION["saved_post"]["user_id"] = $user_id;
			}
			else
			{
				$_SESSION["saved_post"]["user_id"][0] = $user_id;
			}

			if(isset($status))
			{
				$_SESSION["saved_post"]["status"] = $status;
			}
		}

		$this->tpl->setVariable("COLUMN_COUNTS", "4");

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", $confirm);
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("confirm"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", $cancel);
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$tbl = new ilTableGUI();
		$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title"),$this->lng->txt("description"),$this->lng->txt("last_change")));
		$tbl->setHeaderVars(array("typ","title","description","last_change"));
		$tbl->setColumnWidth(array("3%","16%","22%","*"));
		$tbl->setMaxcount($maxcount);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setTitle($this->lng->txt("objs_delete"),"icon_grp_b.gif",$this->lng->txt("group_details"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->render();
	}

	/**
	* displays form with all members of group
	* @access public
	*/
	function groupmembers()
	{
		global $rbacsystem;

		//check Access
  		if (!$rbacsystem->checkAccess("read",$this->object->getRefId()))
		{
			$this->ilias->raiseError("Permission denied !",$this->ilias->error_obj->MESSAGE);
		}

		$tab = array();

		//create additional tabs for tab-bar
		$tab[0] = array ();
		$tab[0]["tab_cmd"] = 'cmd=view&ref_id='.$_GET["ref_id"];
		$tab[0]["ftabtype"] = 'tabinactive';
		$tab[0]["target"] = "bottom";
		$tab[0]["tab_text"] = 'group_objects';

		$tab[1] = array ();
		$tab[1]["tab_cmd"] = 'cmd=groupmembers&ref_id='.$_GET["ref_id"];
		$tab[1]["ftabtype"] = 'tabactive';
		$tab[1]["target"] = "bottom";
		$tab[1]["tab_text"] = 'group_members';

		$this->prepareOutput(false, $tab);
		$this->tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$admin_ids = $newGrp->getGroupAdminIds();

		//if current user is admin he is able to add new members to group
		if (in_array($_SESSION["AccountId"], $admin_ids))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","group.php?cmd=newmembersobject&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("add_member"));
			$this->tpl->parseCurrentBlock();
		}

		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_send_mail")."\" title=\"".$this->lng->txt("grp_mem_send_mail")."\" border=\"0\" vspace=\"0\"/>";
		$val_change = "<img src=\"".ilUtil::getImagePath("icon_change_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_change_status")."\" title=\"".$this->lng->txt("grp_mem_change_status")."\" border=\"0\" vspace=\"0\"/>";
		$val_leave = "<img src=\"".ilUtil::getImagePath("icon_group_out_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_leave")."\" title=\"".$this->lng->txt("grp_mem_leave")."\" border=\"0\" vspace=\"0\"/>";

		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$member_ids = $newGrp->getGroupMemberIds($_GET["ref_id"]);

		foreach($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);

			$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
			$link_change = "group.php?cmd=changeMemberObject&ref_id=".$this->ref_id."&mem_id=".$member->getId();
			$link_leave = "group.php?type=grp&cmd=removeMemberObject&ref_id=".$_GET["ref_id"]."&mem_id=".$member->getId();

			//build function
			if (in_array($_SESSION["AccountId"], $admin_ids))
			{
				$member_functions = "<a href=\"$link_change\">$val_change</a>";
			}

			if (in_array($_SESSION["AccountId"], $admin_ids) || $member->getId() == $_SESSION["AccountId"])
			{
				$member_functions .="<a href=\"$link_leave\">$val_leave</a>";
			}

			$grp_role_id = $newGrp->getGroupRoleId($member->getId());
			$newObj	     = new ilObject($grp_role_id,false);


			//INTERIMS:quite a circumstantial way to handle the table structure....
			if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
			{
				$this->data["data"][$member->getId()]= array(
					"check"		=> ilUtil::formCheckBox(0,"user_id[]",$member->getId()),
					"login"        => $member->getLogin(),
					"firstname"       => $member->getFirstname(),
					"lastname"        => $member->getLastname(),
					"grp_role" => $newObj->getTitle(),
					"functions" => "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions
					);

				unset($member_functions);
				unset($member);
				unset($newObj);
			}
			else
			{
				//discarding the checkboxes
				$this->data["data"][$member->getId()]= array(
					"login"        => $member->getLogin(),
					"firstname"       => $member->getFirstname(),
					"lastname"        => $member->getLastname(),
					"grp_role" => $newObj->getTitle(),
					"functions" => "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions
					);

				unset($member_functions);
				unset($member);
				unset($newObj);
			}
		}

		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_members"));
		$this->tpl->addBlockfile("CONTENT", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&gateway=true");

		$this->data["buttons"] = array( "RemoveMember"  => $this->lng->txt("remove"),
						"changeMember"  => $this->lng->txt("change"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("TPLPATH",$this->tplPath);

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write",$this->object->getRefId() ))
		{
			//user is administrator
			$this->tpl->setVariable("COLUMN_COUNTS",6);

			foreach ($this->data["buttons"] as $name => $value)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME",$name);
				$this->tpl->setVariable("BTN_VALUE",$value);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			//user is member
			$this->tpl->setVariable("COLUMN_COUNTS",5);//user must be member
		}

		//sort data array
		include_once "./include/inc.sort.php";
		include_once "./classes/class.ilTableGUI.php";

		$this->data["data"] = sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);

		$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// create table
		$tbl = new ilTableGUI($output);

		// title & header columns
		$tbl->setTitle($this->lng->txt("group_members")." - ".$this->object->getTitle(),"icon_usr_b.gif",$this->lng->txt("group_members"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write",$this->object->getRefId() ))
		{
			//user must be administrator
			$tbl->setHeaderNames(array($this->lng->txt("check"),$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("functions")));
			$tbl->setHeaderVars(array("check","login","firstname","lastname","role","functions"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));
			$tbl->setColumnWidth(array("5%","15%","30%","30%","10%","10%"));
		}
		else
		{
			//user must be member
			$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("functions")));
			$tbl->setHeaderVars(array("login","firstname","lastname","role","functions"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));
			$tbl->setColumnWidth(array("20%","30%","30%","10%","10%"));
		}

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$tbl->render();
		$this->tpl->show();
	}

	function deleteObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["saved_post"] = $_POST["id"];
		unset($this->data);
		$confirm = "confirmedDelete";
		$cancel  = "canceldelete";
		$info	 = "info_delete_sure";
		$status  = "";

		$this->confirmation($_POST["id"], $confirm, $cancel, $info,"","y");
		$this->tpl->show();
	}

	/**
	* show trash content of object
	*
	* @access	public
 	*/
	function trash()
	{
		$this->prepareOutput(false);

		$objects = $this->tree->getSavedNodeData($_GET["ref_id"]);

		if (count($objects) == 0)
		{
			sendInfo($this->lng->txt("msg_trash_empty"));
			$this->data["empty"] = true;
		}
		else
		{
			$this->data["empty"] = false;
			$this->data["cols"] = array("","type", "title", "description", "last_change");

			foreach ($objects as $obj_data)
			{
				$this->data["data"]["$obj_data[child]"] = array(
					"checkbox"    => "",
					"type"        => $obj_data["type"],
					"title"       => $obj_data["title"],
					"desc"        => $obj_data["desc"],
					"last_update" => $obj_data["last_update"]);
			}

			$this->data["buttons"] = array( "undelete"  => $this->lng->txt("btn_undelete"),
									  "removeFromSystem"  => $this->lng->txt("btn_remove_system"));
		}

		// load template for table
		$this->tpl->addBlockfile("CONTENT", "group_table", "tpl.obj_confirm.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_rows.html");
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.obj_confirm.html");

		$this->tpl->setVariable("HEADER",  $this->object->getTitle());

		if ($this->data["empty"] == true)
		{
			return;
		}

		/* TODO: fix message display in conjunction with sendIfno & raiseError functionality
		$this->tpl->addBlockfile("MESSAGE", "adm_trash", "tpl.message.html");
		$this->tpl->setCurrentBlock("adm_trash");
		$this->tpl->setVariable("MSG",$this->lng->txt("info_trash"));
		$this->tpl->parseCurrentBlock();
		*/
		//sendInfo($this->lng->txt("info_trash"));

		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]);

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

		foreach ($this->data["data"] as $key1 => $value)
		{
			$this->tpl->setCurrentBlock("tbl_content");
			// BEGIN TABLE CELL
			foreach ($value as $key2 => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");
				// CREATE CHECKBOX
				if ($key2 == "checkbox")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::formCheckBox(0,"trash_id[]",$key1));
				}

				// CREATE TEXT STRING
				elseif ($key2 == "type")
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
		$this->tpl->show();
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function canceldeleteObject()
	{
		session_unregister("saved_post");

		sendInfo($this->lng->txt("action_aborted"),true);

		header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
		exit();
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		//TODO: check the
		// creates a child object
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		$this->prepareOutput();

		// TODO: get rid of $_GET variable
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// temp. switch for file upload
		if ($new_type == "file")
		{
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];
			$data["fields"]["file"] = $_SESSION["error_post_vars"]["Fobject"]["file"];

			$this->tpl->addBlockFile("CONTENT", "create_table" ,"tpl.file_new.html");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
		else
		{
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";

			$this->tpl->addBlockFile("CONTENT", "create_table" ,"tpl.obj_edit.html");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION","group.php?gateway=false&cmd=save&ref_id=".$_GET["ref_id"]."&parent_non_rbac_id=".$_GET["parent_non_rbac_id"]."&new_type=".$new_type);
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}

		$this->tpl->show();
	}

	/**
	* show possible action (form buttons)
	*
	* @access	public
 	*/
	function showActions($with_subobjects = false)
	{
		$notoperations = array();
		// NO PASTE AND CLEAR IF CLIPBOARD IS EMPTY
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "paste";
			$notoperations[] = "clear";

			// temp. disabled
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";
		}
		// CUT COPY PASTE LINK DELETE IS NOT POSSIBLE IF CLIPBOARD IS FILLED
		if ($_SESSION["clipboard"])
		{
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";

			//temp. disabled
			$notoperations[] = "paste";
			$notoperations[] = "clear";
		}

		$operations = array();

		$d = $this->objDefinition->getActions("grp");

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $val["lng"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($with_subobjects == true)
		{
			$this->showPossibleSubObjects();
		}

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		$d = $this->objDefinition->getCreatableSubObjects("grp");

		$import = false;

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
					if($row["import"] == "1")	// import allowed?
					{
						$import = true;
					}
				}
			}
		}

		$import = false;

		if (is_array($subobj))
		{
			// show import button if at least one
			// object type can be imported
			if ($import)
			{
				$this->tpl->setCurrentBlock("import_object");
				$this->tpl->setVariable("BTN_IMP", "import");
				$this->tpl->setVariable("TXT_IMP", $this->lng->txt("import"));
				$this->tpl->parseCurrentBlock();
			}

			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* create new object form
	*/
	function create()
	{
		//TODO: check the acces rights; compare class.ilObjectGUI.php
		global $rbacsystem;

		if (isset($_POST["new_type"]))
		{
			$new_type =  $_POST["new_type"];
		}
		else
		{
			$new_type =	 $_GET["type"];
		}

		$data = array();
		$data["fields"] = array();
		$data["fields"]["group_name"] = "";
		$data["fields"]["desc"] = "";

		$this->prepareOutput();
		$this->tpl->addBlockFile("CONTENT", "newgroup", "tpl.grp_edit.html");
		$this->tpl->setVariable("HEADER", $this->lng->txt("grp_new"));
		$this->tpl->setVariable("TARGET","target=\"bottom\"");

		$node = $this->tree->getNodeData($_GET["parent_ref_id"]);
		$this->tpl->setVariable("TXT_PAGEHEADLINE", $node["title"]);

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
		}

		$stati = array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));
		
		
		//build form
		$opts = ilUtil::formSelect(0,"group_status_select",$stati,false,true);

		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&cmd=save"."&ref_id=".$_GET["parent_ref_id"]."&new_type=".$new_type);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMemberObject()
	{
		include_once "./classes/class.ilTableGUI.php";

		$member_ids = array();
		if(isset($_POST["user_id"]))
			$member_ids = $_POST["user_id"];
		else if(isset($_GET["mem_id"]))
			$member_ids[0] = $_GET["mem_id"];

		$newGrp = new ilObjGroup($_GET["ref_id"],true);
		$stati = array(0=>"grp_member_role",1=>"grp_admin_role");

		//build data structure
		foreach($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $newGrp->getMemberStatus($member_id);

			$this->data["data"][$member->getId()]= array(
				"login"        => $member->getLogin(),
				"firstname"       => $member->getFirstname(),
				"lastname"        => $member->getLastname(),
				"grp_role" => ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."]",$stati,false,true)
				);
			unset($member);
		}

		$tab = array();
		$tab[0] = array ();
		$tab[0]["tab_cmd"] = 'cmd=groupmembers&ref_id='.$_GET["ref_id"];
		$tab[0]["ftabtype"] = 'tabinactive';
		$tab[0]["target"] = "bottom";
		$tab[0]["tab_text"] = 'group_members';

		$this->prepareOutput(false, $tab);
		$this->tpl->setVariable("HEADER", $this->lng->txt("obj_change"));

		$this->tpl->addBlockfile("CONTENT", "member_table", "tpl.table.html");

		//load template for table content data
		//$this->tpl->setVariable("FORMACTION", "group.php?ref_id=".$_GET["ref_id"]."&gateway=true");
		$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&tree_id=".$this->grp_tree->getTreeId()."&tree_table=grp_tree");
		$this->data["buttons"] = array( "updateMemberStatus"  => $this->lng->txt("confirm"),
						"canceldelete"  => $this->lng->txt("cancel"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",4);
		$this->tpl->setVariable("TPLPATH",$this->ilias->tplPath);

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		$offset = intval($_GET["offset"]);
		$limit = intval($_GET["limit"]);

		if ($limit == 0)
		{
			$limit = 10;	// TODO: move to user settings
		}

		if ($offset == "")
		{
			$offset = 0;	// TODO: move to user settings
		}

		// create table
		$tbl = new ilTableGUI($this->data["data"]);
		// title & header columns
		$tbl->setTitle($this->lng->txt("change member status"),"icon_usr_b.gif",$this->lng->txt("change member status"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("role"),$this->lng->txt("status")));
		$tbl->setHeaderVars(array("firstname","lastname","role","status"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function updateMemberStatusObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilias->raiseError("No permissions to change member status!",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$grp = new ilObjGroup($_GET["ref_id"]);

			if(isset($_POST["member_status_select"]))
			{
				foreach ($_POST["member_status_select"] as $key=>$value)
				{
					$grp->setMemberStatus($key,$value);
				}
			}
		}
		//TODO: link back
		header("location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
	}

	function updateGroupStatusObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilias->raiseError("No permissions to change group status!",$this->ilias->error_obj->WARNING);
		}
		else
		{
			if(isset($_POST["group_status"]))
			{
				$this->object->setTitle($_POST["Fobject"]["title"]);
				$this->object->setDescription($_POST["Fobject"]["desc"]);
				$this->object->setGroupStatus($_POST["group_status"]);
				$this->update = $this->object->update();
			}
		}
		header("Location: group.php?".$this->link_params);
	}


	/**
	* displays search form for new users
	* @access public
	*/
	function newMembersObject()
	{

		$tab = array();

		//create additional tabs for tab-bar
		$tab[0] = array ();
		$tab[0]["tab_cmd"] = 'cmd=view&ref_id='.$_GET["ref_id"];
		$tab[0]["ftabtype"] = 'tabinactive';
		$tab[0]["target"] = "bottom";
		$tab[0]["tab_text"] = 'group_objects';

		$tab[1] = array ();
		$tab[1]["tab_cmd"] = 'cmd=groupmembers&ref_id='.$_GET["ref_id"];
		$tab[1]["ftabtype"] = 'tabinactive';
		$tab[1]["target"] = "bottom";
		$tab[1]["tab_text"] = 'group_members';

		$this->prepareOutput(false, $tab);

		$this->tpl->setVariable("HEADER", $this->lng->txt("add_member"));

		$this->tpl->addBlockFile("CONTENT", "newmember","tpl.grp_newmember.html");

		$this->tpl->setVariable("TXT_MEMBER_NAME", $this->lng->txt("username"));
		$this->tpl->setVariable("TXT_STATUS", $this->lng->txt("group_memstat"));

		$radio_member = ilUtil::formRadioButton($_POST["status"] ? 0:1,"status",0);
		$radio_admin = ilUtil::formRadioButton($_POST["status"] ? 1:0,"status",1);

		$this->tpl->setVariable("RADIO_MEMBER", $radio_member);
		$this->tpl->setVariable("RADIO_ADMIN", $radio_admin);
		$this->tpl->setVariable("TXT_MEMBER_STATUS", $this->lng->txt("group_memstat_member"));
		$this->tpl->setVariable("TXT_ADMIN_STATUS", $this->lng->txt("group_memstat_admin"));
		$this->tpl->setVariable("TXT_SEARCH", "Search");

		if(isset($_POST["search_user"]) )
			$this->tpl->setVariable("SEARCH_STRING", $_POST["search_user"]);
		else if(isset($_GET["search_user"]) )
			$this->tpl->setVariable("SEARCH_STRING", $_GET["search_user"]);

		$this->tpl->setVariable("FORMACTION_NEW_MEMBER", "group.php?type=grp&cmd=newMembersObject&ref_id=".$_GET["ref_id"]."&search_user=".$_POST["search_user"]);

		$this->tpl->parseCurrentBlock();

		//query already started ?
		if ((isset($_POST["search_user"]) && isset($_POST["status"])) || ( isset($_GET["search_user"]) && isset($_GET["status"])))//&& isset($_GET["ref_id"]) )
		{
			$member_ids = ilObjUser::searchUsers($_POST["search_user"] ? $_POST["search_user"] : $_GET["search_user"]);

			if(count($member_ids) < 1)
			{
				//TODO!!!
				$this->ilias->raiseError("No matching results !",$this->ilias->error_obj->ERROR);
			}
			else
			{

				//INTERIMS SOLUTION
				$_SESSION["status"] = $_POST["status"];
				foreach($member_ids as $member)
				{
					$this->data["data"][$member["usr_id"]]= array(
						"check"		=> ilUtil::formCheckBox(0,"user_id[]",$member["usr_id"]),
						"login"        => $member["login"],
						"firstname"       => $member["firstname"],
						"lastname"        => $member["lastname"]
						);
				}

				//display search results
				infoPanel();

				$this->tpl->addBlockfile("NEW_MEMBERS_TABLE", "member_table", "tpl.table.html");

				// load template for table content data
				$this->tpl->setVariable("FORMACTION", "group.php?gateway=true&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&tree_id=".$this->grp_tree->getTreeId()."&tree_table=grp_tree");
				$this->tpl->setVariable("FORM_ACTION_METHOD", "post");

				$this->data["buttons"] = array( "assignMember"  => $this->lng->txt("assign"),
								"canceldelete"  => $this->lng->txt("cancel"));

				$this->tpl->setCurrentBlock("tbl_action_row");
				$this->tpl->setVariable("COLUMN_COUNTS",4);
				$this->tpl->setVariable("TPLPATH",$this->tplPath);

				foreach ($this->data["buttons"] as $name => $value)
				{
					$this->tpl->setCurrentBlock("tbl_action_btn");
					$this->tpl->setVariable("BTN_NAME",$name);
					$this->tpl->setVariable("BTN_VALUE",$value);
					$this->tpl->parseCurrentBlock();
				}

				//sort data array
				include_once "./include/inc.sort.php";
				$this->data["data"] = sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);

				$offset = intval($_GET["offset"]);
				$limit = intval($_GET["limit"]);

				if ($limit == 0)
				{
					$limit = 10;	// TODO: move to user settings
				}

				if ($offset == "")
				{
					$offset = 0;	// TODO: move to user settings
				}

				$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

				// create table
				include_once "./classes/class.ilTableGUI.php";
				$tbl = new ilTableGUI($output);
				// title & header columns
				$tbl->setTitle($this->lng->txt("member list"),"icon_usr_b.gif",$this->lng->txt("member list"));
				$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
				$tbl->setHeaderNames(array($this->lng->txt("check"),$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname")));
				$tbl->setHeaderVars(array("check","login","firstname","lastname"),array("ref_id"=>$_GET["ref_id"],"cmd"=>$_GET["cmd"],"search_user"=>$_POST["search_user"] ? $_POST["search_user"] : $_GET["search_user"],"status"=>$_POST["status"] ? $_POST["status"] : $_GET["status"]));

				$tbl->setColumnWidth(array("5%","25%","35%","35%"));

				// control
				$tbl->setOrderColumn($_GET["sort_by"]);
				$tbl->setOrderDirection($_GET["sort_order"]);
				$tbl->setLimit($_GET["limit"]);
				$tbl->setOffset($_GET["offset"]);
				$tbl->setMaxCount(count($this->data["data"]));

				$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

				// render table
				$tbl->render();
			}
		}

		$this->tpl->show();
	}


	function confirmedAssignMemberObject()
	{
		if (isset($_SESSION["saved_post"]) && isset($_SESSION["status"]))
		{
			//let new members join the group
			$newGrp = new ilObjGroup($this->object->getRefId(), true);
			foreach ($_SESSION["saved_post"]["user_id"] as $new_member)
			{
				if (!$newGrp->join($new_member, $_SESSION["status"]))
				{
					$this->ilias->raiseError("An Error occured while assigning user to group !",$this->ilias->error_obj->MESSAGE);
				}
			}

			unset($_SESSION["status"]);
			unset($_SESSION["saved_post"]);
		}

		header("Location: group.php?cmd=view&".$this->link_params);
	}

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function assignMemberObject()
	{

		$user_ids = $_POST["user_id"];

		if(isset($user_ids))
		{
			$confirm = "confirmedAssignMember";
			$cancel  = "canceldelete";
			$info	 = "info_assign_sure";
			$status  = $_SESSION["status"];
			$this->confirmation($user_ids, $confirm, $cancel, $info, $status,"n");
			$this->tpl->show();

		}
		else
		{
			sendInfo($this->lng->txt("You have to choose at least one user !"),true);
			header("Location: group.php?cmd=view&ref_id=".$_GET["ref_id"]);
		}
	}

	/**
	* set Locator
	* @access	public
	*/
	function setLocator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$modifier = 1;

		$path = $this->grp_tree->getPathFull($_GET["ref_id"]);

		$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("groups"));
		$this->tpl->setVariable("LINK_ITEM", "grp_list.php");
		$this->tpl->setVariable("LINK_TARGET", "target=\"bottom\"");
		$this->tpl->parseCurrentBlock();

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $row["title"]);
			$this->tpl->setVariable("LINK_ITEM", "group.php?ref_id=".$row["child"]);
			$this->tpl->setVariable("LINK_TARGET", "target=\"bottom\"");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("locator");

		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}

		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}

		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}

	function get_file()
	{
		global $rbacsystem;

		$fileObj =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
		$file_name = $fileObj->getFilePath()."/".$fileObj->getFileName();

		// security check: does requested file exists? (is in a group)
		$grp_ref_id = ilUtil::getGroupId($fileObj->getrefId());

		if ($grp_ref_id === false)
		{
			// does not exists; abort
			return false;
		}

		// security check: is current user allowed to download the file?
		if (!$rbacsystem->checkAccess("read",$grp_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

	    // Create download file name to be displayed to user
	    $save_as_name = basename($file_name);
	    // Send binary filetype HTTP header
	    header("Content-Type: ".$fileObj->getFileType());
	    // Send content-length HTTP header
	    header('Content-Length: '.filesize($file_name));
	    // Send content-disposition with save file name HTTP header
	    header('Content-Disposition: attachment; filename="'.$save_as_name.'"');
	    // Output file
	    readfile($file_name);
	    // Done
	    return true;
	}
}
?>
