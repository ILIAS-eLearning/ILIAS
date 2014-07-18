<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/User/classes/class.ilUserAutoComplete.php';
require_once 'Services/Mail/classes/class.ilMailAutoCompleteRecipientProvider.php';

/**
 * Class ilMailAutoCompleteUserProvider
 */
class ilMailAutoCompleteUserProvider extends ilMailAutoCompleteRecipientProvider
{
	/**
	 * @param string $quoted_term
	 * @param string $term
	 */
	public function __construct($quoted_term, $term)
	{
		parent::__construct($quoted_term, $term);
	}

	/**
	 * "Valid" implementation of iterator interface
	 * @return  boolean true/false
	 */
	public function valid()
	{
		$this->data = $this->db->fetchAssoc($this->res);

		return is_array($this->data);
	}

	/**
	 * "Current" implementation of iterator interface
	 * @return  array
	 */
	public function current()
	{
		return array(
			'login'     => $this->data['login'],
			'firstname' => $this->data['firstname'],
			'lastname'  => $this->data['lastname']
		);
	}

	/**
	 * "Key" implementation of iterator interface
	 * @return  boolean true/false
	 */
	public function key()
	{
		return $this->data['login'];
	}

	/**
	 * "Rewind "implementation of iterator interface
	 */
	public function rewind()
	{
		if($this->res)
		{
			$this->db->free($this->res);
			$this->res = null;
		}
		$select_part   = $this->getSelectPart();
		$where_part    = $this->getWherePart($this->quoted_term);
		$order_by_part = $this->getOrderByPart();
		$query         = implode(" ", array(
			'SELECT ' . $select_part,
			'FROM ' . $this->getFromPart(),
			$where_part ? 'WHERE ' . $where_part : '',
			$order_by_part ? 'ORDER BY ' . $order_by_part : ''
		));

		$this->res = $this->db->query($query);
	}

	/**
	 * @return string
	 */
	protected function getSelectPart()
	{
		$fields = array(
			'login',
			'firstname',
			'lastname',
			'email'
		);

		$fields[] = 'profpref.value profile_value';
		$fields[] = 'pubemail.value email_value';

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

		$joins[] = '
			LEFT JOIN usr_pref profpref
			ON profpref.usr_id = usr_data.usr_id
			AND profpref.keyword = ' . $ilDB->quote('public_profile', 'text');

		$joins[] = '
			LEFT JOIN usr_pref pubemail
			ON pubemail.usr_id = usr_data.usr_id
			AND pubemail.keyword = ' . $ilDB->quote('public_email', 'text');

		if($joins)
		{
			return 'usr_data ' . implode(' ', $joins);
		}
		else
		{
			return 'usr_data ';
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	protected function getWherePart($search_query)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$outer_conditions   = array();
		$outer_conditions[] = 'usr_data.usr_id != ' . $ilDB->quote(ANONYMOUS_USER_ID, 'integer');

		$field_conditions = array();
		foreach($this->getFields() as $field)
		{
			$field_condition = $this->getQueryConditionByFieldAndValue($field, $search_query);

			if('email' == $field)
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
		if($field_conditions)
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

		return $ilDB->like($field, 'text', $a_str . '%');
	}

	/**
	 * Get searchable fields
	 * @return array
	 */
	protected function getFields()
	{
		$available_fields = array();
		foreach(array('login', 'firstname', 'lastname') as $field)
		{
			include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
			if(ilUserSearchOptions::_isEnabled($field))
			{
				$available_fields[] = $field;
			}
		}
		return $available_fields;
	}
}