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
	protected $add_options = array();
	
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
			default:
				if(!$cmd)
				{
					$cmd = "showSearch";
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
	}

	function cancel()
	{
		$this->ctrl->returnToParent($this);
	}

	function start()
	{
		// delete all session info
		$this->__clearSession();
		$this->showSearch();

		return true;
	}


	public function addUser()
	{
		// call callback if that function does give a return value => show error message
		$class =& $this->callback['class'];
		$method = $this->callback['method'];

		// listener redirects if everything is ok.
		$class->$method($_POST['user']);
		$this->showSearchResults();
	}

	function setCallback(&$class,$method,$a_add_options = array())
	{
		$this->callback = array('class' => $class,'method' => $method);
		$this->add_options = $a_add_options ? $a_add_options : array();
	}
	
	public function showSearch()
	{
		$this->initFormSearch();
		$this->tpl->setContent($this->form->getHTML());
	}
	
	public function initFormSearch()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form =  new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'search'));
		$this->form->setTitle($this->lng->txt('add_members_header'));
		$this->form->addCommandButton('performSearch', $this->lng->txt('search'));
		$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		
		$kind = new ilRadioGroupInputGUI($this->lng->txt('search_type'),'search_for');
		$kind->setValue($this->search_type);
		$this->form->addItem($kind);
		
			// Users
			$users = new ilRadioOption($this->lng->txt('search_for_users'),'usr');
			
			// UDF
			include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
			foreach(ilUserSearchOptions::_getSearchableFieldsInfo() as $info)
			{
				switch($info['type'])
				{
					case FIELD_TYPE_UDF_SELECT:
					case FIELD_TYPE_SELECT:
						
						$sel = new ilSelectInputGUI($info['lang'],"rep_query[usr][".$info['db']."]");
						$sel->setOptions($info['values']);
						$users->addSubItem($sel);
						break;
	
					case FIELD_TYPE_UDF_TEXT:
					case FIELD_TYPE_TEXT:

						$txt = new ilTextInputGUI($info['lang'],"rep_query[usr][".$info['db']."]");
						$txt->setSize(30);
						$txt->setMaxLength(120);
						$users->addSubItem($txt);
						break;
				}
			}
		$kind->addOption($users);

			// Role
			$roles = new ilRadioOption($this->lng->txt('search_for_role_members'),'role');
				$role = new ilTextInputGUI($this->lng->txt('search_role_title'),'rep_query[role][title]');
				$role->setSize(30);
				$role->setMaxLength(120);
			$roles->addSubItem($role);
		$kind->addOption($roles);
			
			// Course
			$groups = new ilRadioOption($this->lng->txt('search_for_crs_members'),'crs');
				$group = new ilTextInputGUI($this->lng->txt('search_crs_title'),'rep_query[crs][title]');
				$group->setSize(30);
				$group->setMaxLength(120);
			$groups->addSubItem($group);
		$kind->addOption($groups);

			// Group
			$groups = new ilRadioOption($this->lng->txt('search_for_grp_members'),'grp');
				$group = new ilTextInputGUI($this->lng->txt('search_grp_title'),'rep_query[grp][title]');
				$group->setSize(30);
				$group->setMaxLength(120);
			$groups->addSubItem($group);
		$kind->addOption($groups);
		
		
	}
	

	function show()
	{
		$this->showSearchResults();
	}

	function appendSearch()
	{
		$_SESSION['search_append'] = true;
		$this->performSearch();
	}

	/**
	 * Perform a search
	 * @return 
	 */
	function performSearch()
	{
		$found_query = false;
		foreach((array) $_POST['rep_query'][$_POST['search_for']] as $field => $value)
		{
			if(trim(ilUtil::stripSlashes($value)))
			{
				$found_query = true;
				break;
			}
		}
		if(!$found_query)
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
			$this->start();
			return false;
		}
	
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

			case 'crs':
				$this->__performCourseSearch();
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
			ilUtil::sendFailure($this->lng->txt('search_no_match'));
			$this->showSearch();
			return true;
		}
		$this->__updateResults();
		if($this->result_obj->isLimitReached())
		{
			$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
			ilUtil::sendInfo($message);
			return true;
		}
		// show results
		$this->show();
		return true;
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
					$result_obj = $user_search->performSearch();

					// store entries
					$this->__storeEntries($result_obj);
					break;
			}
		}
	}

	/**
	 * Search groups
	 * @return 
	 */
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

	/**
	 * Search courses
	 * @return 
	 */
	protected function __performCourseSearch()
	{
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

		$query_string = $_SESSION['rep_query']['crs']['title'];
		if(!is_object($query_parser = $this->__parseQueryString($query_string)))
		{
			ilUtil::sendInfo($query_parser,true);
			return false;
		}

		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($query_parser);
		$object_search->setFilter(array('crs'));
		$this->__storeEntries($object_search->performSearch());

		return true;
	}

	/**
	 * Search roles
	 * @return 
	 */
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

	/**
	 * Add new search button
	 * @return 
	 */
	protected function addNewSearchButton()
	{
		include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton(
			$this->lng->txt('search_new'), 
			$this->ctrl->getLinkTarget($this,'showSearch')
		);
		$this->tpl->setVariable('ACTION_BUTTONS',$toolbar->getHTML());
	}
	
	public function showSearchResults()
	{
		$counter = 0;
		$f_result = array();
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.rep_search_result.html','Services/Search');
		$this->addNewSearchButton();
		
		switch($this->search_type)
		{
			case "usr":
				$this->showSearchUserTable($_SESSION['rep_search']['usr']);
				break;

			case 'grp':
				$this->showSearchGroupTable($_SESSION['rep_search']['grp']);
				break;

			case 'crs':
				$this->showSearchCourseTable($_SESSION['rep_search']['crs']);
				break;

			case 'role':
				$this->showSearchRoleTable($_SESSION['rep_search']['role']);
				break;
		}

		// Finally fill user table, if search type append
		if($_SESSION['search_append'] and $this->search_type != 'usr')
		{
			$result = $this->__fillUserTable($_SESSION['rep_search']['usr']);
			$this->__showSearchUserTable($result,$_SESSION['rep_search']['usr'],'search','APPEND_TABLE');
			
		}
	}

	/**
	 * Show usr table
	 * @return 
	 * @param object $a_usr_ids
	 */
	protected function showSearchUserTable($a_usr_ids,$tpl_var = 'RES_TABLE')
	{
		include_once './Services/Search/classes/class.ilRepositoryUserResultTableGUI.php';
		
		$table = new ilRepositoryUserResultTableGUI($this,'showSearchResults');
		$table->initMultiCommands($this->add_options);
		$table->parseUserIds($a_usr_ids);
		
		$this->tpl->setVariable($tpl_var,$table->getHTML());
	}
	
	/**
	 * Show usr table
	 * @return 
	 * @param object $a_usr_ids
	 */
	protected function showSearchRoleTable($a_obj_ids)
	{
		include_once './Services/Search/classes/class.ilRepositoryObjectResultTableGUI.php';
		
		$table = new ilRepositoryObjectResultTableGUI($this,'showSearchResults');
		$table->parseObjectIds($a_obj_ids);
		
		$this->tpl->setVariable('RES_TABLE',$table->getHTML());
	}

	/**
	 * 
	 * @return 
	 * @param array $a_obj_ids
	 */
	protected function showSearchGroupTable($a_obj_ids)
	{
		include_once './Services/Search/classes/class.ilRepositoryObjectResultTableGUI.php';
		
		$table = new ilRepositoryObjectResultTableGUI($this,'showSearchResults');
		$table->parseObjectIds($a_obj_ids);
		
		$this->tpl->setVariable('RES_TABLE',$table->getHTML());
	}
	
	/**
	 * 
	 * @return 
	 * @param array $a_obj_ids
	 */
	protected function showSearchCourseTable($a_obj_ids)
	{
		include_once './Services/Search/classes/class.ilRepositoryObjectResultTableGUI.php';
		
		$table = new ilRepositoryObjectResultTableGUI($this,'showSearchResults');
		$table->parseObjectIds($a_obj_ids);
		
		$this->tpl->setVariable('RES_TABLE',$table->getHTML());
	}

	/**
	 * List users of course/group/roles
	 * @return 
	 */
	protected function listUsers()
	{
		if(!is_array($_POST['obj']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showSearchResults();
			return false;
		}
		
		// Get all members
		$members = array();
		foreach($_POST['obj'] as $obj_id)
		{
			$type = ilObject::_lookupType($obj_id);
			switch($type)
			{
				case 'crs':
					include_once './Modules/Course/classes/class.ilCourseParticipants.php';
					$part = ilCourseParticipants::_getInstanceByObjId($obj_id);
					$members = array_merge($members, $part->getParticipants());
					break;
					
				case 'grp':
					include_once './Modules/Group/classes/class.ilGroupParticipants.php';
					$part = ilGroupParticipants::_getInstanceByObjId($obj_id);
					$members = array_merge($members, $part->getParticipants());
					break;
					
				case 'role':
					global $rbacreview;
					
					$members = array_merge($members, $rbacreview->assignedUsers($obj_id));
					break;
			}
		}
		$members = array_unique((array) $members);
		$this->__appendToStoredResults($members);
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.rep_search_result.html','Services/Search');
		
		$this->addNewSearchButton();
		$this->showSearchUserTable($_SESSION['rep_search']['usr']);
		return true;
	}


}
?>
