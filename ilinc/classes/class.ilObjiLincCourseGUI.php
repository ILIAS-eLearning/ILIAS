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
* Class ilObjiLincCourseGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* 
* @ilCtrl_Calls ilObjiLincCourseGUI: ilObjiLincClassroomGUI
* 
* @package ilias-core
*/

include_once "./classes/class.ilContainerGUI.php";

class ilObjiLincCourseGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjiLincCourseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "icrs";
		$this->ilContainerGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$this->ctrl->saveParameter($this,'ref_id');

	}
	
	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
		$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		$data["fields"]["homepage"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["homepage"],true);
		$data["fields"]["download"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["download"],true);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.icrs_edit.html","ilinc");
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_edit.html");
		
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TITLE", $data["fields"]["title"]);
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("DESC", $data["fields"]["desc"]);
		$this->tpl->setVariable("TXT_HOMEPAGE_URL", $this->lng->txt("homepage_url"));
		$this->tpl->setVariable("HOMEPAGE_URL", $data["fields"]["homepage"]);
		$this->tpl->setVariable("TXT_DOWNLOAD_RESOURCES_URL", $this->lng->txt("download_resources_url"));
		$this->tpl->setVariable("DOWNLOAD_RESOURCES_URL", $data["fields"]["download"]);
		$this->tpl->setVariable("TXT_NOT_YET", $this->lng->txt("not_implemented_yet"));

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
																   $_GET["ref_id"]."&new_type=".$new_type));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}
	
	function updateObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilErr->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilErr->MESSAGE);
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->object->activated = ilUtil::tf2yn($_POST["Fobject"]["activated"]);

		// save changes to ilinc serverand ilias database
		$success = $this->object->update();
		
		if ($success == false)
		{
			$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
		}

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getLinkTarget($this)));
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin,$rbacsystem;

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilErr->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilErr->MESSAGE);
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->object->activated = ilUtil::tf2yn($_POST["Fobject"]["activated"]);
		
		// when creating new ilinc course we first create it on ilinc server
		include_once ('class.ilnetucateXMLAPI.php');
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->addCourse($_POST["Fobject"]);
		$response = $ilinc->sendRequest();
		
		if ($response->isError())
		{
			$this->ilErr->raiseError($response->getErrorMsg(),$this->ilErr->MESSAGE);
		}
		
		// if everything ok, create and insert ilinc course in ILIAS
		$icrsObj = parent::saveObject();

		// save ilinc_id in ILIAS and save data (temp. TODO: build save function)
		$icrsObj->storeiLincId($response->getFirstID());
		
		// setup rolefolder & default local roles (admin & member)
		$roles = $icrsObj->initDefaultRoles();

		// ...finally assign icrsadmin role to creator of icrs object
		$success = $icrsObj->addMember($this->ilias->account,$icrsObj->getDefaultAdminRole(),true);

		if (!$success)
		{
			$this->ilErr->raiseError($icrsObj->getErrorMsg(),$this->ilErr->MESSAGE);
		}

//		$icrsObj->setRegistrationFlag($_POST["enable_registration"]); //0=no registration, 1=registration enabled 2=passwordregistration
//		$icrsObj->setPassword($_POST["password"]);
//		$icrsObj->setExpirationDateTime($_POST["expirationdate"]." ".$_POST["expirationtime"].":00");

		$this->ilias->account->addDesktopItem($icrsObj->getRefId(),"icrs");	
		
		// always send a message
		sendInfo($this->lng->txt("icrs_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("ilinc_classrooms",
				$this->ctrl->getLinkTarget($this, ""), "", get_class($this));
		}
		
		if ($this->ilias->getSetting("ilinc_active"))
		{
			if ($rbacsystem->checkAccess('write',$this->ref_id))
			{
				$tabs_gui->addTarget("edit_properties",
					$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
			}
	
			if ($rbacsystem->checkAccess('read',$this->ref_id))
			{
				$tabs_gui->addTarget("ilinc_involved_users",
					$this->ctrl->getLinkTarget($this, "members"), "members", get_class($this));
			}
			
			if ($rbacsystem->checkAccess('write',$this->ref_id) and $this->object->isDocent($this->ilias->account))
			{
				// testing: display link to ilinc server directly
				$tabs_gui->addTarget("ilinc_manage_course_documents",
					$url = $this->object->userLogin($this->ilias->account), "agenda","","_blank");
	//			$tabs_gui->addTarget("ilinc_manage_course_documents",
	//				$this->ctrl->getLinkTarget($this, "agenda"), "agenda", get_class($this),"_blank");
	
			}
		}
		
		/*$applications = $this->object->getNewRegistrations();

		if (is_array($applications) and $this->object->isAdmin($this->ilias->account->getId()))
		{
			$tabs_gui->addTarget("ilinc_new_registrations",
				$this->ctrl->getLinkTarget($this, "ShownewRegistrations"), "ShownewRegistrations", get_class($this));
		}*/

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
		}
		
		// show clipboard in repository
		if ($this->ctrl->getTargetScript() == "repository.php" and !empty($_SESSION['il_rep_clipboard']))
		{
			$tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$tabs_gui->addTarget("trash",
					$this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
			}
		}
	}
	
	function __unsetSessionVariables()
	{
		unset($_SESSION["grp_delete_member_ids"]);
		unset($_SESSION["grp_delete_subscriber_ids"]);
		unset($_SESSION["grp_search_str"]);
		unset($_SESSION["grp_search_for"]);
		unset($_SESSION["grp_role"]);
		unset($_SESSION["grp_group"]);
		unset($_SESSION["grp_archives"]);
	}
	
	function __search($a_search_string,$a_search_for)
	{
		include_once("classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");
		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUserForm");
		}

		return $search->getResultByType($a_search_for);
	}

	function __showSearchUserTable($a_result_set,$a_user_ids = NULL, $a_cmd = "search")
	{
        $return_to  = "searchUserForm";
	
    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",$return_to);
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("last_visit")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "last_visit"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => $a_cmd,
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchRoleTable($a_result_set,$a_role_ids = NULL)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","role");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_role"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
    	$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","group");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}
	
	function __showMembersTable($a_result_set,$a_user_ids = NULL)
	{
        global $rbacsystem,$ilBench;
        
		$actions = array("RemoveMember"  => $this->lng->txt("remove"),"changeMember"  => $this->lng->txt("change"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		
		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{			//user is administrator
            $tpl->setCurrentBlock("plain_button");
		    $tpl->setVariable("PBTN_NAME","searchUserForm");
		    $tpl->setVariable("PBTN_VALUE",$this->lng->txt("grp_add_member"));
		    $tpl->parseCurrentBlock();
		    $tpl->setCurrentBlock("plain_buttons");
		    $tpl->parseCurrentBlock();
		
			$tpl->setVariable("COLUMN_COUNTS",7);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

            foreach ($actions as $name => $value)
			{
				$tpl->setCurrentBlock("tbl_action_btn");
				$tpl->setVariable("BTN_NAME",$name);
				$tpl->setVariable("BTN_VALUE",$value);
				$tpl->parseCurrentBlock();
			}
			
			if (!empty($a_user_ids))
			{
				// set checkbox toggles
				$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
				$tpl->setVariable("JS_VARNAME","user_id");			
				$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
				$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
				$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
				$tpl->parseCurrentBlock();
			}
			
            $tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		}

		$this->ctrl->setParameter($this,"cmd","members");


		// title & header columns
		$tbl->setTitle($this->lng->txt("members"),"icon_usr_b.gif",$this->lng->txt("group_members"));

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			//user must be administrator
			$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("ilinc_coursemember_status"),$this->lng->txt("role"),$this->lng->txt("grp_options")));
			$tbl->setHeaderVars(array("","login","firstname","lastname","ilinc_coursemember_status","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("","20%","15%","15%","20%","20%","10%"));
		}
		else
		{
			//user must be member
			$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("ilinc_coursemember_status"),$this->lng->txt("role"),$this->lng->txt("grp_options")));
			$tbl->setHeaderVars(array("login","firstname","lastname","ilinc_coursemember_status","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("20%","15%","15%","20%","20%","10%"));
		}

		$this->__setTableGUIBasicData($tbl,$a_result_set,"members");
		$tbl->render();
		$this->tpl->setVariable("ADM_CONTENT",$tbl->tpl->get());
		
		return true;
	}
	

	function &__initTableGUI()
	{
		include_once "classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "subscribers":
				$offset = $_GET["update_subscribers"] ? $_GET["offset"] : 0;
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : 'login';
				$direction = $_GET["update_subscribers"] ? $_GET["sort_order"] : '';
				break;

			case "group":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;
				
			case "role":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;

			default:
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		//$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
	
	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview;

		$_SESSION["grp_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["grp_role"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["role"]))
		{
			sendInfo($this->lng->txt("grp_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;

			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}
	
	function listUsersGroupObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["grp_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["grp_group"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["group"]))
		{
			sendInfo($this->lng->txt("grp_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["group"] as $group_id)
		{
			if (!$tree->isInTree($group_id))
			{
				continue;
			}
			if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}

			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
			
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
	}
	
	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function canceledObject()
	{
		$return_location = $_GET["cmd_return_location"];
		if (strcmp($return_location, "") == 0)
		{
			$return_location = "members";
		}
				
		sendInfo($this->lng->txt("action_aborted"),true);
		$this->ctrl->redirect($this, $return_location);
	}

	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function cancelMemberObject()
	{
		$return_location = "members";
				
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
	}
	
	/**
	* display group members
	*/
	function membersObject()
	{
		global $rbacsystem,$ilBench,$ilDB,$lng;
		
		if (!$this->ilias->getSetting("ilinc_active"))
		{
			$this->ilias->raiseError($lng->txt("ilinc_server_not_active"),$this->ilias->error_obj->MESSAGE);
		}
		
		//if current user is admin he is able to add new members to group
		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_send_mail")."\" title=\"".$this->lng->txt("grp_mem_send_mail")."\" border=\"0\" vspace=\"0\"/>";
		$val_change = "<img src=\"".ilUtil::getImagePath("icon_change_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_change_status")."\" title=\"".$this->lng->txt("grp_mem_change_status")."\" border=\"0\" vspace=\"0\"/>";
		$val_leave = "<img src=\"".ilUtil::getImagePath("icon_group_out_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_leave")."\" title=\"".$this->lng->txt("grp_mem_leave")."\" border=\"0\" vspace=\"0\"/>";

		// store access checks to improve performance
		$access_delete = $rbacsystem->checkAccess("delete",$this->object->getRefId());
		$access_leave = $rbacsystem->checkAccess("leave",$this->object->getRefId());
		$access_write = $rbacsystem->checkAccess("write",$this->object->getRefId());

		$member_ids = $this->object->getMemberIds();
		
		// fetch all user data in one shot to improve performance (from ILIAS db)
		$members = $this->object->getMemberData($member_ids);
		
		// fetch docent or student assignment form all coursemembers from iLinc server
		$docent_ids = $this->object->getiLincMemberIds(true);
		$student_ids = $this->object->getiLincMemberIds(false);
	
		$account_id = $this->ilias->account->getId();
		$counter = 0;

		foreach ($members as $mem)
		{
			$link_contact = "mail_new.php?type=new&rcp_to=".$mem["login"];
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$mem["id"];
		
			if (($mem["id"] == $account_id && $access_leave) || $access_delete)
			{
				$link_leave = $this->ctrl->getLinkTarget($this,"RemoveMember")."&mem_id=".$mem["id"];
			}

			//build function
			if ($access_delete && $access_write)
			{
				$member_functions = "<a href=\"$link_change\">$val_change</a>";
			}

			if (($mem["id"] == $account_id && $access_leave) || $access_delete)
			{
				$member_functions .="<a href=\"$link_leave\">$val_leave</a>";
			}
			
			// this is twice as fast than the code above
			$str_member_roles = $this->object->getMemberRolesTitle($mem["id"]);

			if ($access_delete && $access_write)
			{
				$result_set[$counter][] = ilUtil::formCheckBox(0,"user_id[]",$mem["id"]);
			}
			
			$user_ids[$counter] = $mem["id"];
			
			$status = $this->object->checkiLincMemberStatus($mem['ilinc_id'],$docent_ids,$student_ids);
			
			if ($status == ILINC_MEMBER_NOTSET)
			{
				$status = "<span class='warning'>".$this->lng->txt($status)."</span>";
			}
			else
			{
				$status = $this->lng->txt($status);
			}
			
            
            //discarding the checkboxes
			$result_set[$counter][] = $mem["login"];
			$result_set[$counter][] = $mem["firstname"];
			$result_set[$counter][] = $mem["lastname"];
			$result_set[$counter][] = $status;
			$result_set[$counter][] = $str_member_roles;
			$result_set[$counter][] = "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions;

			++$counter;

			unset($member_functions);
		}

		return $this->__showMembersTable($result_set,$user_ids);
    }
    
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		//var_dump($next_class,$cmd);

		switch($next_class)
		{
			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';

				if($_GET['item_id'])
				{
					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerInterface($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;

			case "ilregistergui":
				$this->ctrl->setReturn($this, "");   // ###
				$reg_gui = new ilRegisterGUI();
				//$reg_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			case "ililincclassroomgui":
			echo "hier";exit;
				$this->ctrl->setReturn($this, "");   // ###
				$reg_gui = new ilRegisterGUI();
				//$reg_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;
				
			default:
				if ($this->object->requireRegistration() and !$this->object->isUserRegistered())
				{
					$this->ctrl->redirectByClass("ilRegisterGUI", "showRegistrationForm");
				}

				if (empty($cmd))
				{
					#$this->ctrl->returnToParent($this);
					// NOT ACCESSIBLE SINCE returnToParent() starts a redirect
					$cmd = "view";
				}

				// NOT ACCESSIBLE SINCE returnToParent() starts a redirect
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
	}
	
	function viewObject()
	{
		global $tree;

		if($this->ctrl->getTargetScript() == "adm_object.php")
		{
			parent::viewObject();
			return true;
		}
		else
		{
			$this->renderObject();
			//$this->listClassrooms();
			return true;
		}

		return true;
	}
	
	/**
	* get all subitems of the container
	* overwrites method in ilContainerGUI
	*/
	function getSubItems()
	{
		$objects = array();

		if (!($objects = $this->object->getClassrooms()))
		{
			sendinfo($this->lng->txt($this->object->getErrorMsg()));
			return;
		}

		foreach ($objects as $key => $object)
		{
			$this->items['icla'][$key] = $object;
		}
	}

	/**
	* overwrites method in ilContainerGUI
	*/
	function renderItemList($a_type = "all")
	{
		global $objDefinition;
		
		$html = "";
		$this->adminCommands = false;
		
		$class = $objDefinition->getClassName("icla");
		$location = $objDefinition->getLocation("icla");
		$full_class = "ilObj".$class."ListGUI";
		include_once($location."/class.".$full_class.".php");
		
		$item_list_gui = new $full_class();
		$item_list_gui->setContainerObject($this);
		
		$tpl =& $this->newBlockTemplate();
		$first = true;

		$item_html = array();

		if (is_array($this->items['icla']))
		{
			foreach($this->items['icla'] as $key => $item)
			{
	
				$html = $item_list_gui->getListItemHTML($this->object->getRefId(),
							$key, $item["name"], $item["description"],$item);
								
				// check wheter any admin command is allowed for
				// the items
				if (!$this->adminCommands)
				{
					if ($_SESSION["il_cont_admin_panel"] != true)
					{
						/*if ($this->rbacsystem->checkAccess("delete", $item["ref_id"]))
						{
							$this->adminCommands = true;
						}*/
					}
					else
					{
						$this->adminCommands = $item_list_gui->adminCommandsIncluded();
					}
				}
							
				if ($html != "")
				{
					$item_html[] = array("html" => $html, "item_id" => $item["ref_id"]);
				}
			}

			// output block for resource type
			if (count($item_html) > 0)
			{
				// separator row
				if (!$first)
				{
					$this->addSeparatorRow($tpl);
				}
			
				$first = false;

				// add a header for each resource type
				$this->addHeaderRow($tpl, 'icla');
				$this->resetRowType();
	
				// content row
				foreach($item_html as $item)
				{
					$this->addStandardRow($tpl, $item["html"], $item["item_id"]);
				}
			}
		}

		$html = $tpl->get();
		return $html;
	}
	
	function removeClassroomObject()
	{
		include_once ('class.ilObjiLincClassroomGUI.php');
		$icla_gui = new ilObjiLincClassroomGUI($_GET['class_id'],$this->ref_id);
		$ret =& $this->ctrl->forwardCommand($icla_gui);
	}
	
	function editClassroomObject()
	{
		include_once ('class.ilObjiLincClassroomGUI.php');
		$icla_gui = new ilObjiLincClassroomGUI($_GET['class_id'],$this->ref_id);
		$ret =& $this->ctrl->forwardCommand($icla_gui);
	}
	
	function updateClassroomObject()
	{
		include_once ('class.ilObjiLincClassroomGUI.php');
		$icla_gui = new ilObjiLincClassroomGUI($_GET['class_id'],$this->ref_id);
		$ret =& $this->ctrl->forwardCommand($icla_gui);
	}
	
	/**
	* cancel deletion of classroom object
	*
	* @access	public
	*/
	function cancelDeleteClassroomObject()
	{
		session_unregister("saved_post");
		
		$this->setReturnLocation("cancelDeleteClassroom", "repository.php?ref_id=".$this->ref_id);

		sendInfo($this->lng->txt("msg_cancel"),true);

		ilUtil::redirect($this->getReturnLocation("cancelDeleteClassroom","adm_object.php?ref_id=".$_GET["ref_id"]));

	}
	
	/**
	* @access	public
	*/
	function confirmedDeleteClassroomObject()
	{
		include_once ('class.ilObjiLincClassroom.php');
		$icla_obj = new ilObjiLincClassroom($_GET['class_id'],$this->ref_id);
		
		if (!$icla_obj->delete())
		{
			$msg = $ilca_obj->getErrorMsg();
		}
		else
		{
			$msg = $this->lng->txt('icla_deleted');
		}
		
		// Feedback
		sendInfo($msg,true);
		
		$this->setReturnLocation("confirmedDeleteClassroom", "repository.php?ref_id=".$this->ref_id);

		ilUtil::redirect($this->getReturnLocation("confirmedDeleteClassroom","adm_object.php?ref_id=".$_GET["ref_id"]));

	}
	
	function joinClassroomObject()
	{
		// check if user is registered at iLinc server
		/*if (!$this->object->userExists($this->ilias->account))
		{
				$ilinc_user_id = $this->object->addUser($this->ilias->account);
		}

		// check if user is already member of icourse
		if (!$this->object->isMember($this->ilias->account,$this->object_ilinc_id))
		{
			// then assign membership to icourse
			$ilinc_data = $this->ilias->account->getiLincData();
			$this->object->registerUser($ilinc_data['id'],$this->object->ilinc_id,"False");
		}*/

		// join class
		$url = $this->object->joinClass($this->ilias->account,$_GET['class_id']);
		
		if (!$url)
		{
			$this->ilias->raiseError($this->object->getErrorMsg(),$this->ilias->error_obj->FATAL);
		}

		ilUtil::redirect(trim($url));
	}
	
	function agendaObject()
	{
		// user login
		$url = $this->object->userLogin($this->ilias->account);
		
		if (!$url)
		{
			$this->ilias->raiseError($this->object->getErrorMsg(),$this->ilias->error_obj->FATAL);
		}

		ilUtil::redirect(trim($url));
	}
	
	function searchUserFormObject()
	{
		global $rbacsystem;

		$this->lng->loadLanguageModule('search');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.grp_members_search.html");
		
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("grp_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["grp_search_str"] ? $_SESSION["grp_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("exc_roles"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
		
        $usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($usr,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($role,"search_for","role"));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($grp,"search_for","grp"));

		$this->__unsetSessionVariables();
	}
	
	function searchObject()
	{
		global $rbacsystem,$tree;

		$_SESSION["grp_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["grp_search_str"];
		$_SESSION["grp_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["grp_search_for"];
		
		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			sendInfo($this->lng->txt("grp_search_enter_search_string"));
			$this->searchUserFormObject();
			
			return false;
		}

		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"])))
		{
			sendInfo($this->lng->txt("grp_no_results_found"));
			$this->searchUserFormObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("grp_new_search"));
		
		$counter = 0;
		$f_result = array();

		switch($_POST["search_for"])
		{
        	case "usr":
				foreach($result as $user)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
					{
						continue;
					}
					
					$user_ids[$counter] = $user["id"];
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();
					$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$user_ids);

				return true;

			case "role":
				foreach($result as $role)
				{
                    // exclude anonymous role
                    if ($role["id"] == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }

                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role["id"],false))
					{
						continue;
					}
					
				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }
                    
                    $role_ids[$counter] = $role["id"];
                    
					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				
				$this->__showSearchRoleTable($f_result,$role_ids);

				return true;
				
			case "grp":
				foreach($result as $group)
				{
					if(!$tree->isInTree($group["id"]))
					{
						continue;
					}
					
					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"],false))
					{
						continue;
					}
					
                    // exclude myself :-)
                    if ($tmp_obj->getId() == $this->object->getId())
                    {
                        continue;
                    }
                    
                    $grp_ids[$counter] = $group["id"];
                    
					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				
				if(!count($f_result))
				{
					sendInfo($this->lng->txt("grp_no_results_found"));
					$this->searchUserFormObject();

					return false;
				}
				
				$this->__showSearchGroupTable($f_result,$grp_ids);

				return true;
		}
	}
	
	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function addUserObject()
	{
		$user_ids = $_POST["user"];

		if (empty($user_ids[0]))
		{
			// TODO: jumps back to grp content. go back to last search result
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		foreach ($user_ids as $new_member)
		{
			$user_obj = $this->ilias->obj_factory->getInstanceByObjId($new_member);

			if (!$this->object->addMember($user_obj,$this->object->getDefaultMemberRole(),false))
			{
				//var_dump($this->object->getErrorMsg());exit;
				$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
			}
			
			unset($user_obj);
		}
		
		//echo "end";exit;

		unset($_SESSION["saved_post"]);

		sendInfo($this->lng->txt("ilinc_msg_member_assigned"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}
	
	/**
	* displays confirmation formular with users that shall be removed from group
	* @access public
	*/
	function removeMemberObject()
	{
		$user_ids = array();

		if (isset($_POST["user_id"]))
		{
			$user_ids = $_POST["user_id"];
		}
		else if (isset($_GET["mem_id"]))
		{
			$user_ids[] = $_GET["mem_id"];
		}

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}
		
		if (count($user_ids) == 1 and $this->ilias->account->getId() != $user_ids[0])
		{
			if (!in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]) 
				and !in_array($this->ilias->account->getId(),$this->object->getAdminIds()))
			{
				$this->ilErr->raiseError($this->lng->txt("ilinc_err_no_permission"),$this->ilErr->MESSAGE);
			}
		}
		//bool value: says if $users_ids contains current user id
		$is_dismiss_me = array_search($this->ilias->account->getId(),$user_ids);
		
		$confirm = "confirmedRemoveMember";
		$cancel  = "canceled";
		$info	 = ($is_dismiss_me !== false) ? "ilinc_dismiss_myself" : "ilinc_dismiss_member";
		$status  = "";
		$return  = "members";
		$this->confirmationObject($user_ids, $confirm, $cancel, $info, $status, $return);
	}

	/**
	* displays confirmation form
	* @access public
	*/
	function confirmationObject($user_id="", $confirm, $cancel, $info="", $status="",$a_cmd_return_location = "")
	{
		$this->data["cols"] = array("type", "title", "description", "last_change");

		if (is_array($user_id))
		{
			foreach ($user_id as $id)
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

				$this->data["data"]["$id"] = array(
					"type"        => $obj_data->getType(),
					"title"       => $obj_data->getTitle(),
					"desc"        => $obj_data->getDescription(),
					"last_update" => $obj_data->getLastUpdateDate(),

					);
			}
		}
		else
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($user_id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate(),
				);
		}

		//write  in sessionvariables
		if(is_array($user_id))
		{
			$_SESSION["saved_post"]["user_id"] = $user_id;
		}
		else
		{
			$_SESSION["saved_post"]["user_id"][0] = $user_id;
		}

		if (isset($status))
		{
			$_SESSION["saved_post"]["status"] = $status;
		}

		$this->data["buttons"] = array( $cancel  => $this->lng->txt("cancel"),
						$confirm  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		infoPanel();

		sendInfo($this->lng->txt($info));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this)."&cmd_return_location=".$a_cmd_return_location);

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
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("spacer.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* remove members from group
	* TODO: set return location to parent object if user removes himself
	* TODO: allow user to remove himself when he is not group admin
	* @access public
	*/
	function confirmedRemoveMemberObject()
	{
		//User needs to have administrative rights to remove members...
		foreach($_SESSION["saved_post"]["user_id"] as $member_id)
		{
			$user_obj = new ilObjUser($member_id);

			if (!$this->object->removeMember($user_obj))
			{
				$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
			}
			
			$user_obj->dropDesktopItem($this->object->getRefId(), "icrs");
		}
		
		unset($_SESSION["saved_post"]);

		sendInfo($this->lng->txt("ilinc_msg_membership_annulled"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}
	
	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMemberObject()
	{
		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}

		$member_ids = array();

		if (isset($_POST["user_id"]))
		{
			$member_ids = $_POST["user_id"];
		}
		else if (isset($_GET["mem_id"]))
		{
			$member_ids[0] = $_GET["mem_id"];
		}

		if (empty($member_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		if (!in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]) 
			and !in_array($this->ilias->account->getId(),$this->object->getAdminIds()))
		{
			$this->ilErr->raiseError($this->lng->txt("grp_err_no_permission"),$this->ilErr->MESSAGE);
		}

		$stati = array_flip($this->object->getLocalRoles(true));
		
		// fetch docent or student assignment form all coursemembers from iLinc server
		$docent_ids = $this->object->getiLincMemberIds(true);
		$student_ids = $this->object->getiLincMemberIds(false);

		//build data structure
		foreach ($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $this->object->getMemberRoles($member_id);
			$ilinc_data = $member->getiLincData();
						
			$ilinc_status = $this->object->checkiLincMemberStatus($ilinc_data['id'],$docent_ids,$student_ids);

			$docent = 0; $student = 0;

			if ($ilinc_status == ILINC_MEMBER_DOCENT)
			{
				$docent = 1;
			}
			elseif ($ilinc_status == ILINC_MEMBER_STUDENT)
			{
				$student = 1;
			}
			
			$radio1 = ilUtil::formRadioButton($docent,"ilinc_member_status_select[".$member->getId()."][".$ilinc_data['id']."]",ILINC_MEMBER_DOCENT);
			$radio2 = ilUtil::formRadioButton($student,"ilinc_member_status_select[".$member->getId()."][".$ilinc_data['id']."]",ILINC_MEMBER_STUDENT);


			$this->data["data"][$member->getId()]= array(
					"login"		=> $member->getLogin(),
					"firstname"	=> $member->getFirstname(),
					"lastname"	=> $member->getLastname(),
					"ilinc_coursemember_status" => $radio1." ".$this->lng->txt("ilinc_docent")."<br/>".$radio2." ".$this->lng->txt("ilinc_student"),
					"grp_role"	=> ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."][]",$stati,true,true,3)
				);
		}
		
		unset($member);
		
		infoPanel();

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->data["buttons"] = array( "members"  => $this->lng->txt("back"),
										"updateMemberStatus"  => $this->lng->txt("confirm"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",5);
		//$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		//sort data array
		$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
		$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		
		// create table
		include_once "./classes/class.ilTableGUI.php";

		$tbl = new ilTableGUI($output);

		// title & header columns
		$tbl->setTitle($this->lng->txt("grp_mem_change_status"),"icon_usr_b.gif",$this->lng->txt("grp_mem_change_status"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("ilinc_coursemember_status"),$this->lng->txt("role")));
		$tbl->setHeaderVars(array("login","firstname","lastname","ilinc_coursemember_status","role"),$this->ctrl->getParameterArray($this,"",false));

		$tbl->setColumnWidth(array("20%","15%","15%","15%","30%"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();

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
	
	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function updateMemberStatusObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()) )
		{
			$this->ilErr->raiseError("permission_denied",$this->ilErr->MESSAGE);
		}

		if (isset($_POST["member_status_select"]))
		{
			foreach ($_POST["member_status_select"] as $key=>$value)
			{
				$this->object->leave($key);
				$this->object->join($key,$value);
			}
		}

		if (isset($_POST["ilinc_member_status_select"]))
		{
			$users_to_add = array();
			$users_to_register = array();
			$users_to_unregister = array();

			foreach ($_POST["ilinc_member_status_select"] as $user_id => $ilinc_arr)
			{
				$ilinc_user_id = key($ilinc_arr);
				$ilinc_user_status = current($ilinc_arr);
				//var_dump($user_id,$ilinc_arr,$ilinc_user_id,$ilinc_user_status);
				
				// if no ilinc user id was passed, there are 3 options:
				// 1. user was added by roleassignment and is registered on iLinc server
				// 2. user was added by roleassignment and is registered NOT YET on iLinc server
				// 3. iLinc server returns an empty response which happens sometimes :-(
				if ($ilinc_user_id == 0)
				{
					//echo "0";
					
					// check if user is already registered on iLinc server
					$user_obj = new ilObjUser($user_id);
					$ilinc_data = $user_obj->getiLincData();
					
					if ($ilinc_data['id'] == '0')
					{
						// not registered. put user on 'add list'
						$users_to_add[] =& $user_obj;
					}
					else
					{
						$users_to_register[$ilinc_data['id']] = ILINC_MEMBER_STUDENT;
					}
					
					continue;
				}
				
				//echo "1";
				$users_to_unregister[] = $ilinc_user_id;
				$users_to_register[$ilinc_user_id] = $ilinc_user_status;
				//var_dump($users_to_unregister,$users_to_register);
			}
			
			if (!$this->object->unregisterUsers($users_to_unregister))
			{				
				//echo "2";
				//var_dump($this->object->getErrorMsg());exit;
				$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
			}
			
			if (count($users_to_add) > 0)
			{
				//echo "3";
				foreach ($users_to_add as $user)
				{
					if (!$this->object->addUser($user))
					{
						//echo "4";
						//var_dump($this->object->getErrorMsg());exit;
						$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
					}
					else
					{
						//echo "5";
						$ilinc_data = $user->getiLincData();
						$users_to_register[$ilinc_data['id']] = ILINC_MEMBER_STUDENT;
					}
				}
			}

			if (!$this->object->registerUsers($users_to_register))
			{
				//echo "6";
				//var_dump($this->object->getErrorMsg());exit;
				$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
			}
		}
		
		//echo "ende";exit;

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}
	
	function editObject()
	{
		if (!$this->ilias->getSetting("ilinc_active"))
		{
			$this->ilias->raiseError($this->lng->txt("ilinc_server_not_active"),$this->ilias->error_obj->MESSAGE);
		}
		
		$fields = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$fields["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$fields["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		}
		else
		{
			$fields["title"] = ilUtil::prepareFormOutput($this->object->getTitle());
			$fields["desc"] = ilUtil::stripSlashes($this->object->getDescription());
		}

		$this->displayEditForm($fields);
	}
	
	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	function displayEditForm($fields)
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.icrs_edit.html","ilinc");

		foreach ($fields as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}
		
		$checkbox_access = ilUtil::formCheckbox($this->object->activated,"Fobject[activated]",1);
		
		$this->tpl->setVariable("TXT_ACCESS", $this->lng->txt("online"));
		$this->tpl->setVariable("CHKBOX_ACCESS", $checkbox_access);
		
		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",$this->ctrl->getFormAction($this).$obj_str));
		//$this->tpl->setVariable("FORMACTION", $this->getFormAction("update","adm_object.php?cmd=gateway&ref_id=".$this->ref_id.$obj_str));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

	}
	
	function joinObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("join", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$this->object->addMember($this->ilias->account,$this->object->getDefaultMemberRole(),false))
		{
			//var_dump($this->object->getErrorMsg());exit;
			$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
		}
		
		sendInfo($this->lng->txt("ilinc_msg_joined"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}
} // END class.ilObjiLincCourseGUI
?>
