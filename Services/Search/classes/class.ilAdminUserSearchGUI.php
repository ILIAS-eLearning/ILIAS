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
* Class ilAdminUserSearchGUI
*
* GUI class for user, group, role search
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilAdminUserSearchGUI: ilObjUserGUI
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilSearchResult.php';
include_once 'Services/Search/classes/class.ilSearchSettings.php';

class ilAdminUserSearchGUI
{
	var $search_type = 'usr';

	/**
	* Constructor
	* @access public
	*/
	function ilAdminUserSearchGUI()
	{
		global $ilCtrl,$tpl,$lng;

		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('search');
		$this->lng->loadLanguageModule('crs');

		$this->__setSearchType();
		$this->__loadQueries();

		$this->result_obj = new ilSearchResult();
		$this->result_obj->setMaxHits(1000000);
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
			case "ilobjusergui":	// never called, see workaround in objuserfoldergui
				include_once('./Services/User/classes/class.ilObjUserGUI.php');
				$this->gui_obj = new ilObjUserGUI("",(int) $_GET["obj_id"], false, false);
				$this->gui_obj->setBackTarget($this->lng->txt("search_results"),
					$this->ctrl->getLinkTarget($this, "show"));
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				break;

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

	function __clearSession()
	{
		unset($_SESSION['rep_search']);
		unset($_SESSION['append_results']);
		unset($_SESSION['rep_query']);
		unset($_SESSION['rep_search_type']);
		unset($_SESSION['usr_query_active']);
	}

	function start()
	{
		// delete all session info
		//$this->__clearSession();
		$this->showExtended();

		return true;
	}
	
	function startSimple()
	{
		// delete all session info
		$this->__clearSession();
		$_SESSION["usr_search_mode"] = "simple";
		$this->performSearchSimple();

		return true;
	}

	function startExtended()
	{
		// delete all session info
		$this->__clearSession();
		$_SESSION["usr_search_mode"] = "extended";
		$this->showExtended();

		return true;
	}
	
	function showExtended()
	{
		$_SESSION["usr_search_mode"] = "extended";

		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// search extended
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'show'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_user_simple"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->callback_class->ctrl->getLinkTarget($this->callback_class, "importUserForm"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("import_users"));
		$this->tpl->parseCurrentBlock();
		$this->__showSearch();
		$this->__showSearchResults();
	}
	
	function show()
	{
		$_SESSION["usr_search_mode"] = "simple";
		
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// search extended
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'showExtended'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_user_extended"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->callback_class->ctrl->getLinkTarget($this->callback_class, "importUserForm"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("import_users"));
		$this->tpl->parseCurrentBlock();
		$this->__showSearchSimple();
		$this->__showSearchResults();
	}
	
	function __showSearchSimple()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usr_search_simple.html','Services/Search');

		$this->ctrl->setParameter($this,'search','1');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'startSimple'));
		$this->tpl->setVariable("TXT_SEARCH",$this->lng->txt("search"));
		if(count($_SESSION['rep_search']['usr']))
			$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt('append_results'));
			
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search_user"));
	}

	function __showSearch()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.rep_search.html','Services/Search');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'startExtended'));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_usr'));
		$this->tpl->setVariable("SEARCH_MEMBERS_HEADER",$this->lng->txt("search_user_extended"));


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
		if(count($_SESSION['rep_search']['usr']))
		{
			$this->tpl->setVariable("BTN3_VALUE",$this->lng->txt('append_results'));
		}
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
	}

	function appendSearch()
	{
		$_SESSION['search_append'] = true;
		$this->performSearch();
	}
	
	function appendSearchSimple()
	{
		$_SESSION['search_append'] = true;
		$this->performSearchSimple();
	}

	function performSearchSimple()
	{
		$_SESSION["usr_search_mode"] = "simple";
		
		// unset search_append if called directly
		if($_POST['cmd']['performSearchSimple'])
		{
			unset($_SESSION['search_append']);
		}

		// fields to search in
		$_POST["search_fields"] = array ("username","firstname","lastname","email");
		
		// search only for active users
		$_POST['active'] = 1;
		
		$_POST["search_string"] = trim($_POST["search_string"]) ? trim($_POST["search_string"]) : trim(urldecode($_GET["search_string"]));
 		$_SESSION['us_active'] = isset($_POST['active']) ? $_POST['active'] : $_SESSION['us_active'];
		$_SESSION['us_active'] = $_SESSION["user_filter"];
		
		if (empty($_POST["search_string"]))
		{
			$_POST["search_string"] = "%";
		}

		// return if no user found
		if (count($search_result = ilObjUser::searchUsers($_POST["search_string"],$_SESSION['us_active'],true, $_GET["user_filter"])) == 0)
		{
			if ($_POST["search_string"] == "%")
			{
				$_POST["search_string"] = "";
			}

			$msg = $this->lng->txt("msg_no_search_result");

			if ($_POST["search_string"] != "")
			{
				$msg .= " ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'";
			}
	        
			if ($_GET['search'])
			{
				global $ilErr;
				$ilErr->raiseError($msg,$ilErr->MESSAGE);
			}
			else
			{
				ilUtil::sendInfo($msg, true);
				$this->ctrl->setParameterByClass("ilobjuserfoldergui", "user_filter", $_GET["user_filter"]);
				$this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
			}
		}
		
		if(!$_SESSION['search_append'])
		{
			$_SESSION['rep_search']['usr'] = array();
		}
		
		foreach($search_result as $result)
		{
			$_SESSION['rep_search']['usr'][] = $result;
		}

		if(!$_SESSION['rep_search'][$this->search_type])
		{
			$_SESSION['rep_search'][$this->search_type] = array();
		}
		else
		{
			// remove duplicate entries
			$_SESSION['rep_search'][$this->search_type] = array_unique($_SESSION['rep_search'][$this->search_type]);
		}
		
		$this->show();
	}

	function performSearch()
	{
		// unset search_append if called directly
		if($_POST['cmd']['performSearch'])
		{
			unset($_SESSION['search_append']);
		}

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
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
			#global $ilErr;
			#$ilErr->raiseError($this->lng->txt('search_no_match'),$ilErr->MESSAGE);
		}
		$this->__updateResults();
		if($this->result_obj->isLimitReached())
		{
			$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
			ilUtil::sendInfo($message);
		}
		// show results
		if ($_SESSION["usr_search_mode"] == "extended")
			$this->showExtended();
		else
			$this->show();
	}

	function __performUserSearch()
	{
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
		
		foreach(ilUserSearchOptions::_getSearchableFieldsInfo(true) as $info)
		{
			$name = $info['db'];
			$query_string = $_SESSION['rep_query']['usr'][$name];
			$active_status = $_SESSION['usr_query_active'];

			// continue if no query string is given
			if(!$query_string)
			{
				continue;
			}
		
			if(!is_object($query_parser = $this->__parseQueryString($query_string)))
			{
				ilUtil::sendInfo($query_parser);
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
					
					if ($active_status == 1)
						$user_search->enableActiveCheck(true);
					elseif ($active_status == 2)
						$user_search->enableInactiveCheck(true);

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
			ilUtil::sendInfo($query_parser,true);
			return false;
		}

		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);
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
			ilUtil::sendInfo($query_parser,true);
			return false;
		}
		
		// Perform like search
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);
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
			$_SESSION['usr_query_active'] = ($_POST['usr_query_active']) ? $_POST['usr_query_active'] : 0;
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
		global $lng;

		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		
		// active flag
		$values = array('0' => $lng->txt('please_choose'),
						'1' => $lng->txt('active'),
						'2' => $lng->txt('inactive'));
						
		$this->tpl->setCurrentBlock("select_field");
		$this->tpl->setVariable("SELECT_NAME",$lng->txt('active'));

		$this->tpl->setVariable("SELECT_BOX",ilUtil::formSelect($_SESSION['usr_query_active'],
																"usr_query_active",
																$values,
																false,
															true));
		$this->tpl->parseCurrentBlock();
		
		foreach(ilUserSearchOptions::_getSearchableFieldsInfo(true) as $info)
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
		if(!$_SESSION['search_append'])
		{
			$_SESSION['rep_search'] = array();
		}
		foreach($this->result_obj->getResults() as $result)
		{
			$_SESSION['rep_search'][$this->search_type][] = $result['obj_id'];
		}
		if(!$_SESSION['rep_search'][$this->search_type])
		{
			$_SESSION['rep_search'][$this->search_type] = array();
		}
		else
		{
			// remove duplicate entries
			$_SESSION['rep_search'][$this->search_type] = array_unique($_SESSION['rep_search'][$this->search_type]);
		}
		return true;
	}

	function __appendToStoredResults($a_usr_ids)
	{
		if(!$_SESSION['search_append'])
		{
			return $_SESSION['rep_search']['usr'] = $a_usr_ids;
		}
		foreach($a_usr_ids as $usr_id)
		{
			$_SESSION['rep_search']['usr'][] = $usr_id;
		}
		return $_SESSION['rep_search']['usr'] ? array_unique($_SESSION['rep_search']['usr']) : array();
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

	function __fillUserTable($user_ids)
	{
		$user_ids = $user_ids ? $user_ids : array();

		$counter = 0;
		foreach($user_ids as $usr_id)
		{
			if(!is_object($tmp_obj = ilObjectFactory::getInstanceByObjId($usr_id,false)))
			{
				continue;
			}

			$user_ids[$counter] = $usr_id;
			
            // determine txt_access
			// Note: If you do changes here, you have to change the corresponding
			//       code in ilObjUserFolderGUI::viewObject as well.
			if ($tmp_obj->getActive())
			{
				if ($tmp_obj->getTimeLimitUnlimited())
				{
					$txt_access = $this->lng->txt("access_unlimited");
				}
				elseif ($tmp_obj->getTimeLimitUntil() < time())
				{
					$txt_access = $this->lng->txt("access_expired");
				}
				else
				{
					$txt_access = ilDatePresentation::formatDate(new ilDateTime($tmp_obj->getTimeLimitUntil(),IL_CAL_UNIX));
				}
			}
			else
			{
				$txt_access = $this->lng->txt("inactive");
			}
					
			$f_result[$counter][] = ilUtil::formCheckbox(0,"id[]",$usr_id);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getEmail();
			$f_result[$counter][] = $txt_access;
			$f_result[$counter][] = $tmp_obj->getLastLogin();
			$f_result[$counter][] = $usr_id;

			unset($tmp_obj);
			++$counter;
		}
		return $f_result ? $f_result : array();
	}

	function __fillGroupTable($group_ids)
	{
		$group_ids = $group_ids ? $group_ids : array();

		$counter = 0;
		foreach($group_ids as $group_id)
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
		return $f_result ? $f_result : array();
	}
		
	function __fillRoleTable($role_ids)
	{
		$role_ids = $role_ids ? $role_ids : array();

		$counter = 0;
		foreach($role_ids as $role_id)
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
		return $f_result ? $f_result : array();
	}


	function __showSearchResults()
	{
		$counter = 0;
		$f_result = array();
		switch($this->search_type)
		{
			case "usr":
				$result = $this->__fillUserTable($_SESSION['rep_search']['usr']);
				$this->__showSearchUserTable($result,$_SESSION['rep_search']['usr']);
				break;

			case 'grp':
				$result = $this->__fillGroupTable($_SESSION['rep_search']['grp']);
				$this->__showSearchGroupTable($result,$_SESSION['rep_search']['grp']);
				break;

			case 'role':
				$result = $this->__fillRoleTable($_SESSION['rep_search']['role']);
				$this->__showSearchRoleTable($result,$_SESSION['rep_search']['role']);
				break;
		}

		// Finally fill user table, if search type append
		if($_SESSION['search_append'] and $this->search_type != 'usr')
		{
			$result = $this->__fillUserTable($_SESSION['rep_search']['usr']);
			$this->__showSearchUserTable($result,$_SESSION['rep_search']['usr'],'search','APPEND_TABLE');
			
		}
	}
	function __showSearchUserTable($a_result_set,$a_user_ids = NULL,$a_cmd = "performSearch",$tpl_var = 'RES_TABLE')
	{
		if(!$a_result_set)
		{
			return false;
		}

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$operations = array(
			array("name" => "delete", "lng" => "delete"),
			array("name" => "activate", "lng" => "activate"),
			array("name" => "deactivate", "lng" => "deactivate"),
			array("name" => "accessRestrict", "lng" => "accessRestrict"),
			array("name" => "accessFree", "lng" => "accessFree"),
			array("name" => "export", "lng" => "export")
			);

		if (count($operations) > 0)
		{
			$select = "<select name=\"selectedAction\">\n";
			foreach ($operations as $val)
			{
				$select .= "<option value=\"" . $val["name"] . "\"";
				if (strcmp($_POST["selectedAction"], $val["name"]) == 0)
				{
					$select .= " selected=\"selected\"";
				}
				$select .= ">";
				$select .= $this->lng->txt($val["lng"]);
				$select .= "</option>";
			}
			$select .= "</select>";
			$tpl->setCurrentBlock("tbl_action_select");
			$tpl->setVariable("SELECT_ACTION", $select);
			$tpl->setVariable("BTN_NAME", "userAction");
			$tpl->setVariable("BTN_VALUE", $this->lng->txt("submit"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",7);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("search_results"),"icon_usr.gif",$this->lng->txt("search_results"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("email"),
								   $this->lng->txt("access_until"),
								   $this->lng->txt("last_login")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "email",
								  "access_until",
								  "last_login"),
							array("ref_id" => $_GET['ref_id'],
								  "cmd" => ($_SESSION['usr_search_mode'] == 'extended') ? 'showExtended' : 'show',
								  "cmdClass" => "iladminusersearchgui",
								  "cmdNode" => $_GET["cmdNode"],
								  "baseClass" => "ilAdministrationGUI"));

		$tbl->setColumnWidth(array("","15%","15%","15%","20%","15%","20%"));

		$this->__setTableGUIUserData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable($tpl_var,$tbl->tpl->get());

		return true;
	}

	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
		if(!$a_result_set)
		{
			return false;
		}

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
			$tpl->setCurrentBlock("tbl_select_all");
			$tpl->setVariable("COLUMN_COUNTS",4);
			$tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));
			$tpl->parseCurrentBlock();
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
								  "cmdClass" => "ilsearchgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();

		$this->tpl->setVariable("RES_TABLE",$tbl->tpl->get());

		return true;
	}
	function __showSearchRoleTable($a_result_set,$a_role_ids)
	{
		if(!$a_result_set)
		{
			return false;
		}

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
			$tpl->setCurrentBlock("tbl_select_all");
			$tpl->setVariable("COLUMN_COUNTS",4);
			$tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_role.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("objs_role"),
								   $this->lng->txt("crs_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $_GET['ref_id'],
								  "cmd" => "show",
								  "cmdClass" => "iladminusersearchgui",
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
			ilUtil::sendInfo($this->lng->txt("crs_no_groups_selected"));
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
		$this->__appendToStoredResults($members);

		$result = $this->__fillUserTable($_SESSION['rep_search']['usr']);
		$this->__showSearchUserTable($result,$_SESSION['rep_search']['usr'],"listUsersGroup");

		return true;
	}
	function listUsersRole()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["crs_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["crs_role"];

		if(!is_array($_POST["role"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_roles_selected"));
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
		$members = $this->__appendToStoredResults($members);

		$result = $this->__fillUserTable($_SESSION['rep_search']['usr']);
		$this->__showSearchUserTable($result,$user_ids,"listUsersRole");

		return true;
	}



	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
		global $ilUser;

        switch($from)
		{
			case "group":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				$tbl->setLimit(1000000);
				break;
				
   			case "role":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				$tbl->setLimit(1000000);
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
				$tbl->setLimit($ilUser->getPref('hits_per_page'));
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}
	
	function __setTableGUIUserData(&$tbl,&$result_set,$from = "")
	{
		global $ilUser;
		
		// remember link target to admin search gui (this)
		$_SESSION["usr_search_link"] = $this->ctrl->getLinkTarget($this,'show');
		
		$tbl->disable("auto_sort");
		$tbl->enable("select_all");
		$tbl->setFormName("cmd");
		$tbl->setSelectAllCheckbox("id");

		$offset = $_GET["offset"];
		// init sort_by (unfortunatly sort_by is preset with 'title'
       	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
        {
            $_GET["sort_by"] = "login";
        }
        $order = $_GET["sort_by"];
		$direction = $_GET["sort_order"];
		$tbl->setLimit($ilUser->getPref('hits_per_page'));

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$order_key = 1;
		
		foreach ($tbl->header_vars as $key => $val)
		{
			if ($order == $val)
			{
				$order_key = $key;
				break;
			}
		}
		
		// sorting array
		$result_set = ilUtil::sortArray($result_set,$order_key,$direction);
		$result_set = array_slice($result_set,$offset,$ilUser->getPref('hits_per_page'));

		// add user link and access info color
		foreach ($result_set as $i => $row)
		{
			foreach ($row as $key => $cell)
			{
				if ($key == 1)
				{
					//build link
					// alex: this resulted in bug #5206 -> no cmdNode
					//$link = "ilias.php?ref_id=7&admin_mode=settings&obj_id=".$row[7]."&search=1&cmd=view&cmdClass=ilobjusergui&cmdNode=".$_SESSION["usergui_cmd_node"]."&baseClass=ilAdministrationGUI";
					$this->ctrl->setParameterByClass("ilobjusergui", "ref_id", "7");
					$this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $row[7]);
					$this->ctrl->setParameterByClass("ilobjusergui", "search", "1");
					$link = $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjusergui"), "view");
					$result_set[$i][$key] = "<a href=\"".$link."\">".$cell."</a>";
				}
				
				if ($key == "5")
				{
					if ($cell == $this->lng->txt("access_unlimited"))
					{
						$result_set[$i][$key] = "<span class=\"smallgreen\">".$cell."</span>";
					}
					elseif (in_array($cell, array($this->lng->txt("access_expired"),  $this->lng->txt("inactive"))))
					{
						$result_set[$i][$key] = "<span class=\"smallred\">".$cell."</span>";
					}
					else
					{
						$result_set[$i][$key] = "<span class=\"small\">".$cell."</span>";
					}
				}
				
				unset($result_set[$i][7]);
			}
		}
		
		$tbl->setData($result_set);
	}
	
	function setCallbackClass(&$class)
	{
		$this->callback_class = $class;
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function cancelDelete()
	{
		session_unregister("saved_post");
		$class =& $this->callback_class;
		
		$script = $class->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show');
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		ilUtil::redirect($script);
	}

	function userAction()
	{
		$method = $_POST["selectedAction"];
		$_SESSION['user_" . $method . "_search'] = true;
		$class =& $this->callback_class;
		$class->showActionConfirmation($method);
	}

	function delete()
	{
		$_SESSION['user_delete_search'] = true;
		
		// call callback if that function does give a return value => show error message
		$class =& $this->callback_class;
		// listener redirects if everything is ok.
		$class->deleteObject();
	}

	function cancel()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		
		$this->ctrl->returnToParent($this);
	}
}
?>