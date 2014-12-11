<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Auto completion class for user lists
 */
class ilUserAutoComplete
{
	const MAX_ENTRIES = 1000;
	
	
	/**
	 * @var int
	 */
	const SEARCH_TYPE_LIKE = 1;

	/**
	 * @var int
	 */
	const SEARCH_TYPE_EQUALS = 2;

	/**
	 * @var int
	 */
	const PRIVACY_MODE_RESPECT_USER_SETTING = 1;

	/**
	 * @var int
	 */
	const PRIVACY_MODE_IGNORE_USER_SETTING = 2;

	/**
	 * @var bool
	 */
	private $searchable_check = false;

	/**
	 * @var bool
	 */
	private $user_access_check = true;

	/**
	 * @var array
	 */
	private $possible_fields = array();

	/**
	 * @var string
	 */
	private $result_field;

	/**
	 * @var int
	 */
	private $search_type;

	/**
	 * @var int
	 */
	private $privacy_mode;

	/**
	 * @var ilObjUser
	 */
	private $user;
	
	
	private $limit = 0;

	/**
	 * Default constructor
	 */
	public function __construct()
	{
		$this->result_field = 'login';

		$this->setSearchType(self::SEARCH_TYPE_LIKE);
		$this->setPrivacyMode(self::PRIVACY_MODE_IGNORE_USER_SETTING);
	}
	
	public function setLimit($a_limit)
	{
		$this->limit = $a_limit;
	}
	
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param int $search_type
	 */
	public function setSearchType($search_type)
	{
		$this->search_type = $search_type;
	}

	/**
	 * @return mixed
	 */
	public function getSearchType()
	{
		return $this->search_type;
	}

	/**
	 * @param int $privacy_mode
	 */
	public function setPrivacyMode($privacy_mode)
	{
		$this->privacy_mode = $privacy_mode;
	}

	/**
	 * @return int
	 */
	public function getPrivacyMode()
	{
		return $this->privacy_mode;
	}

	/**
	 * @param ilObjUser $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return ilObjUser
	 */
	public function getUser()
	{
		return $this->user;
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
			include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
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
	 * @param string $a_str
	 * @return string
	 */
	public function getList($a_str)
	{
		/**
		 * @var $ilDB  ilDB
		 * @var $ilLog ilLog
		 */
		global $ilDB, $ilLog;

		$select_part   = $this->getSelectPart();
		$where_part    = $this->getWherePart($a_str);
		$order_by_part = $this->getOrderByPart();
		$query         = implode(" ", array(
			'SELECT ' . $select_part,
			'FROM ' . $this->getFromPart(),
			$where_part ? 'WHERE ' . $where_part : '',
			$order_by_part ? 'ORDER BY ' . $order_by_part : ''
		));

		$ilLog->write(__METHOD__ . ': Query: ' . $query);

		$res = $ilDB->query($query);

		// add email only if it is "searchable"
		$add_email = true;
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		if($this->isFieldSearchableCheckEnabled() && !ilUserSearchOptions::_isEnabled("email"))
		{
			$add_email = false;
		}

		include_once './Services/Search/classes/class.ilSearchSettings.php';
		$max = $this->getLimit() ? $this->getLimit() : ilSearchSettings::getInstance()->getAutoCompleteLength();
		$cnt    = 0;
		$more_results = FALSE;
		$result = array();
		while(($rec = $ilDB->fetchAssoc($res)) && $cnt < ($max + 1))
		{
			if($cnt >= $max)
			{
				$more_results = TRUE;
				break;
			}
			
			// @todo: Open discussion: We should remove all non public fields from result
			$label = $rec['lastname'] . ', ' . $rec['firstname'] . ' [' . $rec['login'] . ']';

			if($add_email && $rec['email'] && (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || 'y' == $rec['email_value']))
			{
				$label .= ', ' . $rec['email'];
			}

			$result[$cnt]['value'] = (string)$rec[$this->result_field];
			$result[$cnt]['label'] = $label;
			$result[$cnt]['id']    = $rec['usr_id'];
			$cnt++;
		}

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';
		
		$result_json['items'] = $result;
		$result_json['hasMoreResults'] = $more_results;
		
		$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($result_json,TRUE));
		
		return ilJsonUtil::encode($result_json);
	}

	/**
	 * @return string
	 */
	protected function getSelectPart()
	{
		$fields = array(
			'ud.usr_id',
			'ud.login',
			'ud.firstname',
			'ud.lastname',
			'ud.email'
		);

		if(self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode())
		{
			$fields[] = 'profpref.value profile_value';
			$fields[] = 'pubemail.value email_value';
		}

		return implode(', ', $fields);
	}

	/**
	 * @return string
	 */
	protected function getFromPart()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$joins = array();

		if(self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode())
		{
			$joins[] = 'LEFT JOIN usr_pref profpref
				ON profpref.usr_id = ud.usr_id
				AND profpref.keyword = ' . $ilDB->quote('public_profile', 'text');

			$joins[] = 'LEFT JOIN usr_pref pubemail
				ON pubemail.usr_id = ud.usr_id
				AND pubemail.keyword = ' . $ilDB->quote('public_email', 'text');
		}

		if($joins)
		{
			return 'usr_data ud ' . implode(' ', $joins);
		}
		else
		{
			return 'usr_data ud';
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	protected function getWherePart($search_query)
	{
		/**
		 * @var $ilDB      ilDB
		 * @var $ilSetting ilSetting
		 */
		global $ilDB, $ilSetting;

		$outer_conditions = array();

		// In 'anonymous' context with respected user privacy, only users with globally published profiles should be found.
		if(self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode() &&
			$this->getUser() instanceof ilObjUser &&
			$this->getUser()->isAnonymous()
		)
		{
			if(!$ilSetting->get('enable_global_profiles', 0))
			{
				// If 'Enable User Content Publishing' is not set in the administration, no user should be found for 'anonymous' context.
				return '1 = 2';
			}
			else
			{
				// Otherwise respect the profile activation setting of every user (as a global (outer) condition in the where clause).
				$outer_conditions[] = 'profpref.value = ' . $ilDB->quote('g', 'text');
			}
		}

		$outer_conditions[] =  'ud.usr_id != ' . $ilDB->quote(ANONYMOUS_USER_ID, 'integer');

		$field_conditions = array();
		foreach($this->getFields() as $field)
		{
			$field_condition = $this->getQueryConditionByFieldAndValue($field, $search_query);

			if('email' == $field && self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode())
			{
				// If privacy should be respected, the profile setting of every user concerning the email address has to be
				// respected (in every user context, no matter if the user is 'logged in' or 'anonymous'). 
				$email_query        = array();
				$email_query[]      = $field_condition;
				$email_query[]      = 'pubemail.value = ' . $ilDB->quote('y', 'text');
				$field_conditions[] = '(' . implode(' AND ', $email_query) . ')';
			}
			else
			{
				$field_conditions[] = $field_condition;
			}
		}

		// If the current user context ist 'logged in' and privacy should be respected, all fields >>>except the login<<<
		// should only be searchable if the users' profile is published (y oder g)
		// In 'anonymous' context we do not need this additional conditions,
		// because we checked the privacy setting in the condition above: profile = 'g' 
		if(self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode() &&
			$this->getUser() instanceof ilObjUser && !$this->getUser()->isAnonymous() &&
			$field_conditions
		)
		{
			$fields = implode(' OR ', $field_conditions);

			$field_conditions[] = '(' . implode(' AND ', array(
				$fields,
				$ilDB->in('profpref.value', array('y', 'g'), false, 'text')
			)) . ')';
		}

		// The login field must be searchable regardless (for 'logged in' users) of any privacy settings.
		// We handled the general condition for 'anonymous' context above: profile = 'g' 
		$field_conditions[] = $this->getQueryConditionByFieldAndValue('login', $search_query);

		include_once 'Services/User/classes/class.ilUserAccountSettings.php';
		if(ilUserAccountSettings::getInstance()->isUserAccessRestricted())
		{
			include_once './Services/User/classes/class.ilUserFilter.php';
			$outer_conditions[] = $ilDB->in('time_limit_owner', ilUserFilter::getInstance()->getFolderIds(), false, 'integer');
		}

		if($field_conditions)
		{
			$outer_conditions[] = '(' . implode(' OR ', $field_conditions) . ')';
		}

		return implode(' AND ', $outer_conditions);
	}

	/**
	 * @return string
	 */
	protected function getOrderByPart()
	{
		return 'login ASC';
	}

	/**
	 * @param string $field
	 * @param mixed  $a_str
	 * @return string
	 */
	protected function getQueryConditionByFieldAndValue($field, $a_str)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;
		
		// #14768
		if(!stristr($a_str, '\\'))
		{
			$a_str = str_replace('%', '\%', $a_str);
			$a_str = str_replace('_', '\_', $a_str);
		}
		
		if(self::SEARCH_TYPE_LIKE == $this->getSearchType())
		{
			return $ilDB->like($field, 'text', $a_str . '%');
		}
		else
		{
			return $ilDB->like($field, 'text', $a_str);
		}
	}
}