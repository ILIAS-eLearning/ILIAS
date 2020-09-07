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
     * @var ilLogger
     */
    private $logger = null;

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

    private $user_limitations = true;

    /**
     * @var bool
     */
    private $respect_min_search_character_count = true;

    /**
     * @var bool
     */
    private $more_link_available = false;
    
    /**
     * @var callable
     */
    protected $user_filter = null;

    /**
     * Default constructor
     */
    public function __construct()
    {
        global $DIC;
        
        $this->result_field = 'login';

        $this->setSearchType(self::SEARCH_TYPE_LIKE);
        $this->setPrivacyMode(self::PRIVACY_MODE_IGNORE_USER_SETTING);
        
        $this->logger = $DIC->logger()->user();
    }

    /**
     * @param bool $a_status
     */
    public function respectMinimumSearchCharacterCount($a_status)
    {
        $this->respect_min_search_character_count = $a_status;
    }

    /**
     * @return bool
     */
    public function getRespectMinimumSearchCharacterCount()
    {
        return $this->respect_min_search_character_count;
    }

    
    /**
     * Closure for filtering users
     * e.g
     * $rep_search_gui->addUserAccessFilterCallable(function($user_ids) use($ref_id,$rbac_perm,$pos_perm)) {
     * // filter users
     * return $filtered_users
     * }
     * @param callable $user_filter
     */
    public function addUserAccessFilterCallable(callable $user_filter)
    {
        $this->user_filter = $user_filter;
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
        if (!$this->isFieldSearchableCheckEnabled()) {
            return $this->getSearchFields();
        }
        $available_fields = array();
        foreach ($this->getSearchFields() as $field) {
            include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
            if (ilUserSearchOptions::_isEnabled($field)) {
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
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $parsed_query = $this->parseQueryString($a_str);

        if (ilStr::strLen($parsed_query['query']) < ilQueryParser::MIN_WORD_LENGTH) {
            $result_json['items'] = [];
            $result_json['hasMoreResults'] = false;
            $this->logger->debug('Autocomplete search rejected: minimum characters count.');
            return json_encode($result_json);
        }


        $select_part = $this->getSelectPart();
        $where_part = $this->getWherePart($parsed_query);
        $order_by_part = $this->getOrderByPart();
        $query = implode(" ", array(
            'SELECT ' . $select_part,
            'FROM ' . $this->getFromPart(),
            $where_part ? 'WHERE ' . $where_part : '',
            $order_by_part ? 'ORDER BY ' . $order_by_part : ''
        ));

        $this->logger->debug('Query: ' . $query);

        $res = $ilDB->query($query);

        // add email only if it is "searchable"
        $add_email = true;
        include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
        if ($this->isFieldSearchableCheckEnabled() && !ilUserSearchOptions::_isEnabled("email")) {
            $add_email = false;
        }
        
        $add_second_email = true;
        if ($this->isFieldSearchableCheckEnabled() && !ilUserSearchOptions::_isEnabled("second_email")) {
            $add_second_email = false;
        }
        
        include_once './Services/Search/classes/class.ilSearchSettings.php';
        $max = $this->getLimit() ? $this->getLimit() : ilSearchSettings::getInstance()->getAutoCompleteLength();
        $cnt = 0;
        $more_results = false;
        $result = array();
        $recs = array();
        $usrIds = array();
        while (($rec = $ilDB->fetchAssoc($res)) && $cnt < ($max + 1)) {
            if ($cnt >= $max && $this->isMoreLinkAvailable()) {
                $more_results = true;
                break;
            }
            $recs[$rec['usr_id']] = $rec;
            $usrIds[] = $rec['usr_id'];
        }
        if (is_callable($this->user_filter, true, $callable_name = '')) {
            $usrIds = call_user_func_array($this->user_filter, [$usrIds]);
        }
        foreach ($usrIds as $usr_id) {
            $rec = $recs[$usr_id];

            if (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || in_array($rec['profile_value'], ['y','g'])) {
                $label = $rec['lastname'] . ', ' . $rec['firstname'] . ' [' . $rec['login'] . ']';
            } else {
                $label = '[' . $rec['login'] . ']';
            }

            if ($add_email && $rec['email'] && (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || 'y' == $rec['email_value'])) {
                $label .= ', ' . $rec['email'];
            }
            
            if ($add_second_email && $rec['second_email'] && (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || 'y' == $rec['second_email_value'])) {
                $label .= ', ' . $rec['second_email'];
            }
            
            $result[$cnt]['value'] = (string) $rec[$this->result_field];
            $result[$cnt]['label'] = $label;
            $result[$cnt]['id'] = $rec['usr_id'];
            $cnt++;
        }

        include_once 'Services/JSON/classes/class.ilJsonUtil.php';
        
        $result_json['items'] = $result;
        $result_json['hasMoreResults'] = $more_results;
        
        $this->logger->dump($result_json, ilLogLevel::DEBUG);
        
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
            'ud.email',
            'ud.second_email'
        );

        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
            $fields[] = 'profpref.value profile_value';
            $fields[] = 'pubemail.value email_value';
            $fields[] = 'pubsecondemail.value second_email_value';
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
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $joins = array();

        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
            $joins[] = 'LEFT JOIN usr_pref profpref
				ON profpref.usr_id = ud.usr_id
				AND profpref.keyword = ' . $ilDB->quote('public_profile', 'text');

            $joins[] = 'LEFT JOIN usr_pref pubemail
				ON pubemail.usr_id = ud.usr_id
				AND pubemail.keyword = ' . $ilDB->quote('public_email', 'text');
            
            $joins[] = 'LEFT JOIN usr_pref pubsecondemail
				ON pubsecondemail.usr_id = ud.usr_id
				AND pubsecondemail.keyword = ' . $ilDB->quote('public_second_email', 'text');
        }

        if ($joins) {
            return 'usr_data ud ' . implode(' ', $joins);
        } else {
            return 'usr_data ud';
        }
    }

    /**
     * @param string
     * @return string
     */
    protected function getWherePart(array $search_query)
    {
        /**
         * @var $ilDB      ilDB
         * @var $ilSetting ilSetting
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];

        $outer_conditions = array();

        // In 'anonymous' context with respected user privacy, only users with globally published profiles should be found.
        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode() &&
            $this->getUser() instanceof ilObjUser &&
            $this->getUser()->isAnonymous()
        ) {
            if (!$ilSetting->get('enable_global_profiles', 0)) {
                // If 'Enable User Content Publishing' is not set in the administration, no user should be found for 'anonymous' context.
                return '1 = 2';
            } else {
                // Otherwise respect the profile activation setting of every user (as a global (outer) condition in the where clause).
                $outer_conditions[] = 'profpref.value = ' . $ilDB->quote('g', 'text');
            }
        }

        $outer_conditions[] = 'ud.usr_id != ' . $ilDB->quote(ANONYMOUS_USER_ID, 'integer');

        $field_conditions = array();
        foreach ($this->getFields() as $field) {
            $field_condition = $this->getQueryConditionByFieldAndValue($field, $search_query);
            
            if ('email' == $field && self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
                // If privacy should be respected, the profile setting of every user concerning the email address has to be
                // respected (in every user context, no matter if the user is 'logged in' or 'anonymous').
                $email_query = array();
                $email_query[] = $field_condition;
                $email_query[] = 'pubemail.value = ' . $ilDB->quote('y', 'text');
                $field_conditions[] = '(' . implode(' AND ', $email_query) . ')';
            } elseif ('second_email' == $field && self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
                // If privacy should be respected, the profile setting of every user concerning the email address has to be
                // respected (in every user context, no matter if the user is 'logged in' or 'anonymous').
                $email_query = array();
                $email_query[] = $field_condition;
                $email_query[] = 'pubsecondemail.value = ' . $ilDB->quote('y', 'text');
                $field_conditions[] = '(' . implode(' AND ', $email_query) . ')';
            } else {
                $field_conditions[] = $field_condition;
            }
        }

        // If the current user context ist 'logged in' and privacy should be respected, all fields >>>except the login<<<
        // should only be searchable if the users' profile is published (y oder g)
        // In 'anonymous' context we do not need this additional conditions,
        // because we checked the privacy setting in the condition above: profile = 'g'
        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode() &&
            $this->getUser() instanceof ilObjUser && !$this->getUser()->isAnonymous() &&
            $field_conditions
        ) {
            $fields = '(' . implode(' OR ', $field_conditions) . ')';

            $field_conditions = [
                '(' . implode(' AND ', array(
                $fields,
                $ilDB->in('profpref.value', array('y', 'g'), false, 'text')
                )) . ')'
            ];
        }

        // The login field must be searchable regardless (for 'logged in' users) of any privacy settings.
        // We handled the general condition for 'anonymous' context above: profile = 'g'
        $field_conditions[] = $this->getQueryConditionByFieldAndValue('login', $search_query);

        include_once 'Services/User/classes/class.ilUserAccountSettings.php';
        if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            include_once './Services/User/classes/class.ilUserFilter.php';
            $outer_conditions[] = $ilDB->in('time_limit_owner', ilUserFilter::getInstance()->getFolderIds(), false, 'integer');
        }

        if ($field_conditions) {
            $outer_conditions[] = '(' . implode(' OR ', $field_conditions) . ')';
        }

        include_once './Services/Search/classes/class.ilSearchSettings.php';
        $settings = ilSearchSettings::getInstance();

        if (!$settings->isInactiveUserVisible() && $this->getUserLimitations()) {
            $outer_conditions[] = "ud.active = " . $ilDB->quote(1, 'integer');
        }

        if (!$settings->isLimitedUserVisible() && $this->getUserLimitations()) {
            $unlimited = "ud.time_limit_unlimited = " . $ilDB->quote(1, 'integer');
            $from = "ud.time_limit_from < " . $ilDB->quote(time(), 'integer');
            $until = "ud.time_limit_until > " . $ilDB->quote(time(), 'integer');

            $outer_conditions[] = '(' . $unlimited . ' OR (' . $from . ' AND ' . $until . '))';
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
     * @param array  $parsed_query
     * @return string
     */
    protected function getQueryConditionByFieldAndValue($field, $query)
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query_strings = array($query['query']);
        
        if (array_key_exists($field, $query)) {
            $query_strings = array($query[$field]);
        } elseif (array_key_exists('parts', $query)) {
            $query_strings = $query['parts'];
        }
        
        $query_condition = '( ';
        $num = 0;
        foreach ($query_strings as $query_string) {
            if ($num++ > 0) {
                $query_condition .= ' OR ';
            }
            if (self::SEARCH_TYPE_LIKE == $this->getSearchType()) {
                $query_condition .= $ilDB->like($field, 'text', $query_string . '%');
            } else {
                $query_condition .= $ilDB->like($field, 'text', $query_string);
            }
        }
        $query_condition .= ')';
        return $query_condition;
    }

    /**
     * allow user limitations like inactive and access limitations
     *
     * @param bool $a_limitations
     */
    public function setUserLimitations($a_limitations)
    {
        $this->user_limitations = (bool) $a_limitations;
    }

    /**
     * allow user limitations like inactive and access limitations
     * @return bool
     */
    public function getUserLimitations()
    {
        return $this->user_limitations;
    }

    /**
     * @return boolean
     */
    public function isMoreLinkAvailable()
    {
        return $this->more_link_available;
    }

    /**
     * IMPORTANT: remember to read request parameter 'fetchall' to use this function
     *
     * @param boolean $more_link_available
     */
    public function setMoreLinkAvailable($more_link_available)
    {
        $this->more_link_available = $more_link_available;
    }
    
    /**
     * Parse query string
     * @param string $a_query
     * @return $query
     */
    public function parseQueryString($a_query)
    {
        $query = array();
        
        if (!stristr($a_query, '\\')) {
            $a_query = str_replace('%', '\%', $a_query);
            $a_query = str_replace('_', '\_', $a_query);
        }

        $query['query'] = trim($a_query);
        
        // "," means fixed search for lastname, firstname
        if (strpos($a_query, ',')) {
            $comma_separated = (array) explode(',', $a_query);
            
            if (count($comma_separated) == 2) {
                if (trim($comma_separated[0])) {
                    $query['lastname'] = trim($comma_separated[0]);
                }
                if (trim($comma_separated[1])) {
                    $query['firstname'] = trim($comma_separated[1]);
                }
            }
        } else {
            $whitespace_separated = (array) explode(' ', $a_query);
            foreach ($whitespace_separated as $part) {
                if (trim($part)) {
                    $query['parts'][] = trim($part);
                }
            }
        }
        
        $this->logger->dump($query, ilLogLevel::DEBUG);
        
        return $query;
    }
}
