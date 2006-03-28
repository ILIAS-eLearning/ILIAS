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
* Class ilRepositorySearchGUI
*
* GUI class for user, group, role search
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilSearchResult.php';
include_once 'Services/Search/classes/class.ilSearchSettings.php';

class ilRepositorySearchGUI
{
	var $search_type = 'usr';

	/**
	* Constructor
	* @access public
	*/
	function ilRepositorySearchGUI()
	{
		global $ilCtrl,$tpl,$lng;

		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('search');

		$this->__setSearchType();
		$this->__loadQueries();
		
		

		$this->result_obj = new ilSearchResult();
		$this->settings =& new ilSearchSettings();

	}


	/**
	* Set/get search string
	* @access public
	*/
	function setString($a_str)
	{
		$_SESSION['search']['string'] = $this->string = $a_str;
	}
	function getString()
	{
		return $this->string;
	}
		
	/**
	* Control
	* @access public
	*/
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
					$cmd = "show";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	function cancel()
	{
		$this->ctrl->returnToParent($this);
	}

	function addUser()
	{
		// call callback if that function does give a return value => show error message
		$class =& $this->callback['class'];
		$method = $this->callback['method'];

		// listener redirects if everything is ok.
		$class->$method($_POST['user']);
		$this->show();
	}

	function setCallback(&$class,$method)
	{
		$this->callback = array('class' => $class,'method' => $method);
	}

	function show()
	{
		$this->__showSearch();
		$this->__showSearchResults();
	}


	function __showSearch()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.rep_search.html','Services/Search');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("SEARCH_MEMBERS_HEADER",$this->lng->txt("add_members_header"));


		// user search
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("search_for_users"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($this->search_type == 'usr',"search_for","usr"));
		$this->__fillUserSearch();


		// groups
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("search_for_grp_members"));
		$this->tpl->setVariable("GROUP_TERM",$this->lng->txt('search_grp_title'));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($this->search_type == 'grp',"search_for","grp"));
		$this->tpl->setVariable("GRP_VALUE",$_SESSION['rep_query']['grp']['title']);


		// roles
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("search_for_role_members"));
		$this->tpl->setVariable("ROLE_TERM",$this->lng->txt('search_role_title'));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($this->search_type == 'role',"search_for","role"));
		$this->tpl->setVariable("ROLE_VALUE",$_SESSION['rep_query']['role']['title']);


		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
	}

	function performSearch()
	{
		switch($this->search_type)
		{
			case 'usr':
				$this->__performUserSearch();
				break;

			case 'grp':
				$this->__performGroupSearch();
				break;

			case 'role':
				$this->__performRoleSearch();
				break;

			default:
				echo 'not defined';
		}
		$this->result_obj->filter(ROOT_FOLDER_ID,QP_COMBINATION_OR);

		if(!count($this->result_obj->getResults()))
		{
			sendInfo($this->lng->txt('search_no_match'));
		}
		$this->__updateResults();
		if($this->result_obj->isLimitReached())
		{
			$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
			sendInfo($message);
		}
		// show results
		$this->show();
	}

	function __performUserSearch()
	{
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

		foreach(ilUserSearchOptions::_getSearchableFieldsInfo() as $info)
		{
			$name = $info['db'];
			$query_string = $_SESSION['rep_query']['usr'][$name];

			// continue if no query string is given
			if(!$query_string)
			{
				continue;
			}
		
			if(!is_object($query_parser = $this->__parseQueryString($query_string)))
			{
				sendInfo($query_parser);
				return false;
			}
			switch($info['type'])
			{
				case FIELD_TYPE_UDF_SELECT:
				case FIELD_TYPE_UDF_TEXT:
					$udf_search = ilObjectSearchFactory::_getUserDefinedFieldSearchInstance($query_parser);
					$udf_search->setFields(array($name));
					$result_obj = $udf_search->performSearch();

					// Store entries
					$this->__storeEntries($result_obj);
					break;

				case FIELD_TYPE_SELECT:
				case FIELD_TYPE_TEXT:
					$user_search =& ilObjectSearchFactory::_getUserSearchInstance($query_parser);
					$user_search->setFields(array($name));
					$result_obj = $user_search->performSearch();

					// store entries
					$this->__storeEntries($result_obj);
					break;
			}
		}
	}

	function __performGroupSearch()
	{
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

		$query_string = $_SESSION['rep_query']['grp']['title'];
		if(!is_object($query_parser = $this->__parseQueryString($query_string)))
		{
			sendInfo($query_parser,true);
			return false;
		}
		$object_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
		$object_search->setFilter(array('grp'));
		$this->__storeEntries($object_search->performSearch());

		return true;
	}

	function __performRoleSearch()
	{
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

		$query_string = $_SESSION['rep_query']['role']['title'];
		if(!is_object($query_parser = $this->__parseQueryString($query_string)))
		{
			sendInfo($query_parser,true);
			return false;
		}
		$object_search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
		$object_search->setFilter(array('role'));
		$this->__storeEntries($object_search->performSearch());

		return true;
	}

	/**
	* parse query string, using query parser instance
	* @return object of query parser or error message if an error occured
	* @access public
	*/
	function &__parseQueryString($a_string)
	{
		include_once 'Services/Search/classes/class.ilQueryParser.php';

		$query_parser = new ilQueryParser(ilUtil::stripSlashes($a_string));
		$query_parser->setCombination(QP_COMBINATION_OR);
		$query_parser->setMinWordLength(1);
		$query_parser->parse();

		if(!$query_parser->validate())
		{
			return $query_parser->getMessage();
		}
		return $query_parser;
	}

	// Private
	function __loadQueries()
	{
		if(is_array($_POST['rep_query']))
		{
			$_SESSION['rep_query'] = $_POST['rep_query'];
		}
	}


	function __setSearchType()
	{
		// Update search type. Default to user search
		if($_POST['search_for'])
		{
			#echo 1;
			$_SESSION['rep_search_type'] = $_POST['search_for'];
		}
		if(!$_POST['search_for'] and !$_SESSION['rep_search_type'])
		{
			#echo 2;
			$_SESSION['rep_search_type'] = 'usr';
		}
		
		$this->search_type = $_SESSION['rep_search_type'];
		#echo $this->search_type;

		return true;
	}

	function __fillUserSearch()
	{
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';

		foreach(ilUserSearchOptions::_getSearchableFieldsInfo() as $info)
		{
			switch($info['type'])
			{
				case FIELD_TYPE_UDF_SELECT:
				case FIELD_TYPE_SELECT:
					$this->tpl->setCurrentBlock("select_field");
					$this->tpl->setVariable("SELECT_NAME",$info['lang']);

					$name = $info['db'];
					$this->tpl->setVariable("SELECT_BOX",ilUtil::formSelect($_SESSION['rep_query']['usr'][$name],
																			"rep_query[usr][$name]",
																			$info['values'],
																			false,
																			true));
					$this->tpl->parseCurrentBlock();
					break;

				case FIELD_TYPE_UDF_TEXT:
				case FIELD_TYPE_TEXT:
					$this->tpl->setCurrentBlock("text_field");
					$this->tpl->setVariable("TEXT_NAME",$info['lang']);

					$name = $info['db'];
					$this->tpl->setVariable("USR_NAME","rep_query[usr][$name]");
					$this->tpl->setVariable("USR_VALUE",$_SESSION['rep_query']['usr'][$name]);
					$this->tpl->parseCurrentBlock();
					break;
			}
			$this->tpl->setCurrentBlock("usr_rows");
			$this->tpl->parseCurrentBlock();
		}

	}

	function __updateResults()
	{
		$_SESSION['rep_search'] = array();
		foreach($this->result_obj->getResults() as $result)
		{
			$_SESSION['rep_search'][$this->search_type][] = $result['obj_id'];
		}

		return true;
	}

	function __storeEntries(&$new_res)
	{
		if($this->stored == false)
		{
			$this->result_obj->mergeEntries($new_res);
			$this->stored = true;

			return true;
		}
		else
		{
			$this->result_obj->intersectEntries($new_res);
			
			return true;
		}
	}

	function __showSearchResults()
	{
		$counter = 0;
		$f_result = array();
		switch($this->search_type)
		{
			case "usr":
				if(!is_array($_SESSION['rep_search']['usr']))
				{
					break;
				}
				foreach($_SESSION['rep_search']['usr'] as $usr_id)
				{
					if(!is_object($tmp_obj = ilObjectFactory::getInstanceByObjId($usr_id,false)))
					{
						continue;
					}
					$user_ids[$counter] = $usr_id;
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$usr_id);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$_SESSION['rep_search']['usr']);

				return true;

			case 'grp':
				if(!is_array($_SESSION['rep_search']['grp']))
				{
					break;
				}
				foreach($_SESSION['rep_search']['grp'] as $group_id)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id = end(
																		   $ref_ids = ilObject::_getAllReferences($group_id)),false))
					{
						continue;
					}
					$grp_ids[$counter] = $group_id;
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$ref_id);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchGroupTable($f_result,$grp_ids);
				return true;

			case 'role':
				if(!is_array($_SESSION['rep_search']['role']))
				{
					break;
				}
				foreach($_SESSION['rep_search']['role'] as $role_id)
				{
                    // exclude anonymous role
                    if ($role_id == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }
                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role_id,false))
					{
						continue;
					}
				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }
					$role_ids[$counter] = $role_id;
				
					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role_id);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}

				$this->__showSearchRoleTable($f_result,$role_ids);
				return true;
		}
	}
	function __showSearchUserTable($a_result_set,$a_user_ids = NULL,$a_cmd = "search")
	{
        $return_to  = "searchUser";

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
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("btn_add"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			#$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			#$tpl->setVariable("JS_VARNAME","user");		
			#$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			#$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			#$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			#$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("search_results"),"icon_usr.gif",$this->lng->txt("search_results"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"),
							array("ref_id" => $_GET['ref_id'],
								  "cmd" => 'show',
								  "cmdClass" => "ilrepositorysearchgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("RES_TABLE",$tbl->tpl->get());

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
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_list_users"));
		$tpl->parseCurrentBlock();

		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			#$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			#$tpl->setVariable("JS_VARNAME","group");			
			#$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			#$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			#$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			#$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_grp.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("crs_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $_GET['ref_id'],
								  "cmd" => "show",
								  "cmdClass" => "ilrepositorysearchgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();

		$this->tpl->setVariable("RES_TABLE",$tbl->tpl->get());

		return true;
	}
	function __showSearchRoleTable($a_result_set,$a_role_ids)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			#$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			#$tpl->setVariable("JS_VARNAME","role");			
			#$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			#$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			#$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			#$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_role.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("crs_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $_GET['ref_id'],
								  "cmd" => "show",
								  "cmdClass" => "ilrepositorysearchgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();
		
		$this->tpl->setVariable("RES_TABLE",$tbl->tpl->get());

		return true;
	}


	function listUsersGroup()
	{
		global $rbacsystem,$tree;

		$_SESSION["crs_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["crs_group"];

		if(!is_array($_POST["group"]))
		{
			sendInfo($this->lng->txt("crs_no_groups_selected"));
			$this->show();

			return false;
		}

		$this->__showSearch();

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["group"] as $group_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}
			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}
		$members = array_unique($members);
		#$members = $this->__appendToStoredResults($members);

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
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
	}
	function listUsersRole()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["crs_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["crs_role"];

		if(!is_array($_POST["role"]))
		{
			sendInfo($this->lng->txt("crs_no_roles_selected"));
			$this->show();

			return false;
		}

		$this->__showSearch();

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);
		#$members = $this->__appendToStoredResults($members);

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
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}



	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
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

		#$tbl->disable('sort');
		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}


	function &__initTableGUI()
	{
		include_once "./classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

}
?>
