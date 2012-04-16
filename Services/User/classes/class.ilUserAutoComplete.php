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
	private $result_field;


	/**
	 * Default constructor
	 */
	public function __construct()
	{
		$this->result_field = 'login';
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
	 * Set result field
	 * @param string $a_field
	 */
	public function setResultField($a_field)
	{
		$this->result_field = $a_field;
	}

	/**
	* Get completion list
	*/
	public function getList($a_str)
	{
		global $ilDB;
		
		$search = ' ';
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
		
		// add email only if it is "searchable"
		$add_email = in_array("email", $this->getFields());

		$max = 20;
		$cnt = 0;
		$result = array();
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			$label = $rec["lastname"].", ".$rec["firstname"]." [".$rec["login"]."]";
			
			if ($add_email && $rec["email"])
			{
				$label .= ", ".$rec["email"];
			}
			
			$result[$cnt] = new stdClass();
			$result[$cnt]->value = (string) $rec[$this->result_field];
			$result[$cnt]->label = $label;			
			$cnt++;			
		}
						
		include_once './Services/JSON/classes/class.ilJsonUtil.php';		
		return ilJsonUtil::encode($result);
	}
	
}
?>