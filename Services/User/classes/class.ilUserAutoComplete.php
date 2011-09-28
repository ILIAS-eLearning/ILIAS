<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Auto completion class for user lists
*
*/
class ilUserAutoComplete
{
	private $searchable_check = false;
	private $user_access_check = true;
	private $possible_fields = array();


	/**
	 * Default constructor
	 */
	public function __construct()
	{
		
	}

	/**
	 * Enable the check whether the field is searchable in Administration -> Settings -> Standard Fields
	 * @param bool $a_status
	 */
	public function enableFieldSearchableCheck($a_status)
	{
		$this->searchable_check = $a_status;
	}

	/**
	 * Searchable check enabled
	 * @return bool
	 */
	public function isFieldSearchableCheckEnabled()
	{
		return $this->searchable_check;
	}

	/**
	 * Enable user access check.
	 * @see Administration -> User Accounts -> Settings -> General Settings
	 * @param bool $a_status
	 */
	public function enableUserAccessCheck($a_status)
	{
		$this->user_access_check = $a_status;
	}

	/**
	 * Check if user access check is enabled
	 * @return bool
	 */
	public function isUserAccessCheckEnabled()
	{
		return $this->user_access_check;
	}

	/**
	 * Set searchable fields
	 * @param array $a_fields
	 */
	public function setSearchFields($a_fields)
	{
		$this->possible_fields = $a_fields;
	}

	/**
	 * get possible search fields
	 * @return array
	 */
	public function getSearchFields()
	{
		return $this->possible_fields;
	}

	/**
	 * Get searchable fields
	 * @return array
	 */
	protected function getFields()
	{
		if(!$this->isFieldSearchableCheckEnabled())
		{
			return $this->getSearchFields();
		}
		$available_fields = array();
		foreach($this->getSearchFields() as $field)
		{
			include_once './Services/Search/classes/class.ilUserSearchOptions.php';
			if(ilUserSearchOptions::_isEnabled($field))
			{
				$available_fields[] = $field;
			}
		}
		return $available_fields;
	}


	/**
	* Get completion list
	*/
	public function getList($a_str)
	{
		global $ilDB;
		
		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		$result = new stdClass();
		$result->response = new stdClass();
		$result->response->results = array();
		if (strlen($a_str) < 3)
		{
			return ilJsonUtil::encode($result);
		}

		$serach = ' ';
		foreach($this->getFields() as $field)
		{
			$search .= $ilDB->like($field,'text',$a_str.'%').' OR ';
		}
		$search .= $ilDB->like("login", "text", $a_str."%")." ";
		
		include_once './Services/User/classes/class.ilUserAccountSettings.php';
		if(ilUserAccountSettings::getInstance()->isUserAccessRestricted())
		{
			include_once './Services/User/classes/class.ilUserFilter.php';
			$query = "SELECT login, firstname, lastname, email FROM usr_data ".
				"WHERE (".
				$search.
				") ".
				"AND " . $ilDB->in('time_limit_owner', ilUserFilter::getInstance()->getFolderIds(), false, 'integer') . " " .
				"ORDER BY login ";
			$set = $ilDB->query($query);
		}
		else
		{
			$query = "SELECT login, firstname, lastname, email FROM usr_data WHERE ".
				$search.
				" ORDER BY login";
			$set = $ilDB->query($query);
		}

		$GLOBALS['ilLog']->write(__METHOD__.': Query: '.$query);

		$max = 20;
		$cnt = 0;
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			$result->response->results[$cnt] = new stdClass();
			$result->response->results[$cnt]->login = (string) $rec["login"];
			$result->response->results[$cnt]->firstname = (string) $rec["firstname"];
			$result->response->results[$cnt]->lastname = (string) $rec["lastname"];
			$result->response->results[$cnt]->email = (string) $rec["email"];
			$cnt++;
		}
		
		return ilJsonUtil::encode($result);
	}
	
}
?>