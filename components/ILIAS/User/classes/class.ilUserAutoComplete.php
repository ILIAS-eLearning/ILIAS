<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Auto completion class for user lists
 */
class ilUserAutoComplete
{
    public const MAX_ENTRIES = 1000;
    public const SEARCH_TYPE_LIKE = 1;
    public const SEARCH_TYPE_EQUALS = 2;
    public const PRIVACY_MODE_RESPECT_USER_SETTING = 1;
    public const PRIVACY_MODE_IGNORE_USER_SETTING = 2;

    private ilLogger $logger;
    private ilDBInterface $db;
    private ilSetting $settings;

    private bool $searchable_check = false;
    private bool $user_access_check = true;
    private array $possible_fields = []; // Missing array type.
    private string $result_field;
    private int $search_type;
    private int $privacy_mode;
    private ?ilObjUser $user = null;
    private int $limit = 0;
    private bool $user_limitations = true;
    private bool $respect_min_search_character_count = true;
    private bool $more_link_available = false;
    protected ?Closure $user_filter = null;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->result_field = 'login';

        $this->setSearchType(self::SEARCH_TYPE_LIKE);
        $this->setPrivacyMode(self::PRIVACY_MODE_IGNORE_USER_SETTING);

        $this->logger = $DIC->logger()->user();
        $this->db = $DIC['ilDB'];
        $this->settings = $DIC['ilSetting'];
    }

    public function respectMinimumSearchCharacterCount(bool $a_status): void
    {
        $this->respect_min_search_character_count = $a_status;
    }

    public function getRespectMinimumSearchCharacterCount(): bool
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
     */
    public function addUserAccessFilterCallable(Closure $user_filter): void
    {
        $this->user_filter = $user_filter;
    }

    public function setLimit(int $a_limit): void
    {
        $this->limit = $a_limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setSearchType(int $search_type): void
    {
        $this->search_type = $search_type;
    }

    public function getSearchType(): int
    {
        return $this->search_type;
    }

    public function setPrivacyMode(int $privacy_mode): void
    {
        $this->privacy_mode = $privacy_mode;
    }

    public function getPrivacyMode(): int
    {
        return $this->privacy_mode;
    }

    public function setUser(ilObjUser $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?ilObjUser
    {
        return $this->user;
    }

    /**
     * Enable the check whether the field is searchable in Administration -> Settings -> Standard Fields
     */
    public function enableFieldSearchableCheck(bool $a_status): void
    {
        $this->searchable_check = $a_status;
    }

    public function isFieldSearchableCheckEnabled(): bool
    {
        return $this->searchable_check;
    }

    /**
     * Enable user access check.
     * @see Administration -> User Accounts -> Settings -> General Settings
     */
    public function enableUserAccessCheck(bool $a_status): void
    {
        $this->user_access_check = $a_status;
    }

    /**
     * Check if user access check is enabled
     */
    public function isUserAccessCheckEnabled(): bool
    {
        return $this->user_access_check;
    }

    /**
     * Set searchable fields
     */
    public function setSearchFields(array $a_fields): void // Missing array type.
    {
        $this->possible_fields = $a_fields;
    }

    /**
     * get possible search fields
     */
    public function getSearchFields(): array // Missing array type.
    {
        return $this->possible_fields;
    }

    /**
     * Get searchable fields
     */
    protected function getFields(): array // Missing array type.
    {
        if (!$this->isFieldSearchableCheckEnabled()) {
            return $this->getSearchFields();
        }
        $available_fields = [];
        foreach ($this->getSearchFields() as $field) {
            if (ilUserSearchOptions::_isEnabled($field)) {
                $available_fields[] = $field;
            }
        }
        return $available_fields;
    }

    /**
     * Set result field
     */
    public function setResultField(string $a_field): void
    {
        $this->result_field = $a_field;
    }

    /**
     * Get completion list
     */
    public function getList(string $a_str): string
    {
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
        $query = implode(" ", [
            'SELECT ' . $select_part,
            'FROM ' . $this->getFromPart(),
            $where_part ? 'WHERE ' . $where_part : '',
            $order_by_part ? 'ORDER BY ' . $order_by_part : ''
        ]);

        $this->logger->debug('Query: ' . $query);

        $res = $this->db->query($query);

        // add email only if it is "searchable"
        $add_email = true;
        if ($this->isFieldSearchableCheckEnabled() && !ilUserSearchOptions::_isEnabled("email")) {
            $add_email = false;
        }

        $add_second_email = true;
        if ($this->isFieldSearchableCheckEnabled() && !ilUserSearchOptions::_isEnabled("second_email")) {
            $add_second_email = false;
        }

        $max = $this->getLimit() ?: ilSearchSettings::getInstance()->getAutoCompleteLength();
        $more_results = false;
        $result = [];
        $records = [];
        $usr_ids = [];

        while (count($usr_ids) <= $max) {
            $next_records = $this->fetchNextRecords($res, $max);
            $records = array_merge($records, $next_records);
            $usr_ids = array_keys($records);

            $callable_name = null;
            if (is_callable($this->user_filter, true, $callable_name)) {
                $usr_ids = call_user_func($this->user_filter, $usr_ids);
            }

            if (count($next_records) <= $max) {
                break;
            }
        }

        if (count($usr_ids) >= $max && $this->isMoreLinkAvailable()) {
            $more_results = true;
        }

        foreach (array_slice($usr_ids, 0, $max) as $usr_id) {
            $record = $records[$usr_id];

            if (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || in_array($record['profile_value'], ['y','g'])) {
                $label = $record['lastname'] . ', ' . $record['firstname'] . ' [' . $record['login'] . ']';
            } else {
                $label = '[' . $record['login'] . ']';
            }

            if ($add_email && $record['email'] && (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || 'y' == $record['email_value'])) {
                $label .= ', ' . $record['email'];
            }

            if ($add_second_email && $record['second_email'] && (self::PRIVACY_MODE_RESPECT_USER_SETTING != $this->getPrivacyMode() || 'y' == $record['second_email_value'])) {
                $label .= ', ' . $record['second_email'];
            }

            $result[] = [
                'value' => (string) $record[$this->result_field],
                'label' => $label,
                'id' => $record['usr_id']
            ];
        }

        $result_json['items'] = $result;
        $result_json['hasMoreResults'] = $more_results;

        $this->logger->dump($result_json, ilLogLevel::DEBUG);

        return json_encode($result_json, JSON_THROW_ON_ERROR);
    }

    private function fetchNextRecords(
        ilDBStatement $res,
        int $max
    ): array {
        $cnt = 0;
        $recs = [];
        while (($rec = $this->db->fetchAssoc($res)) && $cnt <= $max) {
            $recs[$rec['usr_id']] = $rec;
            $cnt++;
        }
        return $recs;
    }

    protected function getSelectPart(): string
    {
        $fields = [
            'ud.usr_id',
            'ud.login',
            'ud.firstname',
            'ud.lastname',
            'ud.email',
            'ud.second_email'
        ];

        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
            $fields[] = 'profpref.value profile_value';
            $fields[] = 'pubemail.value email_value';
            $fields[] = 'pubsecondemail.value second_email_value';
        }

        return implode(', ', $fields);
    }

    protected function getFromPart(): string
    {
        $joins = [];

        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
            $joins[] = 'LEFT JOIN usr_pref profpref
				ON profpref.usr_id = ud.usr_id
				AND profpref.keyword = ' . $this->db->quote('public_profile', 'text');

            $joins[] = 'LEFT JOIN usr_pref pubemail
				ON pubemail.usr_id = ud.usr_id
				AND pubemail.keyword = ' . $this->db->quote('public_email', 'text');

            $joins[] = 'LEFT JOIN usr_pref pubsecondemail
				ON pubsecondemail.usr_id = ud.usr_id
				AND pubsecondemail.keyword = ' . $this->db->quote('public_second_email', 'text');
        }

        if ($joins) {
            return 'usr_data ud ' . implode(' ', $joins);
        } else {
            return 'usr_data ud';
        }
    }

    protected function getWherePart(array $search_query): string // Missing array type.
    {
        $outer_conditions = [];

        // In 'anonymous' context with respected user privacy, only users with globally published profiles should be found.
        if (self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode() &&
            $this->getUser() instanceof ilObjUser &&
            $this->getUser()->isAnonymous()
        ) {
            if (!$this->settings->get('enable_global_profiles', '0')) {
                // If 'Enable User Content Publishing' is not set in the administration, no user should be found for 'anonymous' context.
                return '1 = 2';
            } else {
                // Otherwise respect the profile activation setting of every user (as a global (outer) condition in the where clause).
                $outer_conditions[] = 'profpref.value = ' . $this->db->quote('g', 'text');
            }
        }

        $outer_conditions[] = 'ud.usr_id != ' . $this->db->quote(ANONYMOUS_USER_ID, 'integer');

        $field_conditions = [];
        foreach ($this->getFields() as $field) {
            $field_condition = $this->getQueryConditionByFieldAndValue($field, $search_query);

            if ($field === 'email' && self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
                // If privacy should be respected, the profile setting of every user concerning the email address has to be
                // respected (in every user context, no matter if the user is 'logged in' or 'anonymous').
                $email_query = [];
                $email_query[] = $field_condition;
                $email_query[] = 'pubemail.value = ' . $this->db->quote('y', 'text');
                $field_conditions[] = '(' . implode(' AND ', $email_query) . ')';
            } elseif ($field === 'second_email' && self::PRIVACY_MODE_RESPECT_USER_SETTING == $this->getPrivacyMode()) {
                // If privacy should be respected, the profile setting of every user concerning the email address has to be
                // respected (in every user context, no matter if the user is 'logged in' or 'anonymous').
                $email_query = [];
                $email_query[] = $field_condition;
                $email_query[] = 'pubsecondemail.value = ' . $this->db->quote('y', 'text');
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
                '(' . implode(' AND ', [
                $fields,
                $this->db->in('profpref.value', ['y', 'g'], false, 'text')
                ]) . ')'
            ];
        }

        // The login field must be searchable regardless (for 'logged in' users) of any privacy settings.
        // We handled the general condition for 'anonymous' context above: profile = 'g'
        $field_conditions[] = $this->getQueryConditionByFieldAndValue('login', $search_query);

        if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            $outer_conditions[] = $this->db->in('time_limit_owner', ilUserFilter::getInstance()->getFolderIds(), false, 'integer');
        }

        if ($field_conditions) {
            $outer_conditions[] = '(' . implode(' OR ', $field_conditions) . ')';
        }

        $search_settings = ilSearchSettings::getInstance();

        if (!$search_settings->isInactiveUserVisible() && $this->getUserLimitations()) {
            $outer_conditions[] = "ud.active = " . $this->db->quote(1, 'integer');
        }

        if (!$search_settings->isLimitedUserVisible() && $this->getUserLimitations()) {
            $unlimited = "ud.time_limit_unlimited = " . $this->db->quote(1, 'integer');
            $from = "ud.time_limit_from < " . $this->db->quote(time(), 'integer');
            $until = "ud.time_limit_until > " . $this->db->quote(time(), 'integer');

            $outer_conditions[] = '(' . $unlimited . ' OR (' . $from . ' AND ' . $until . '))';
        }

        return implode(' AND ', $outer_conditions);
    }

    protected function getOrderByPart(): string
    {
        return 'login ASC';
    }

    protected function getQueryConditionByFieldAndValue(string $field, array $query): string // Missing array type.
    {
        $query_strings = [$query['query']];

        if (array_key_exists($field, $query)) {
            $query_strings = [$query[$field]];
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
                $query_condition .= $this->db->like($field, 'text', $query_string . '%');
            } else {
                $query_condition .= $this->db->like($field, 'text', $query_string);
            }
        }
        $query_condition .= ')';
        return $query_condition;
    }

    /**
     * allow user limitations like inactive and access limitations
     */
    public function setUserLimitations(bool $a_limitations): void
    {
        $this->user_limitations = $a_limitations;
    }

    /**
     * allow user limitations like inactive and access limitations
     */
    public function getUserLimitations(): bool
    {
        return $this->user_limitations;
    }

    public function isMoreLinkAvailable(): bool
    {
        return $this->more_link_available;
    }

    /**
     * IMPORTANT: remember to read request parameter 'fetchall' to use this function
     */
    public function setMoreLinkAvailable(bool $more_link_available): void
    {
        $this->more_link_available = $more_link_available;
    }

    /**
     * Parse query string
     */
    public function parseQueryString(string $a_query): array // Missing array type.
    {
        $query = [];

        if (strpos($a_query, '\\') === false) {
            $a_query = str_replace(['%', '_'], ['\%', '\_'], $a_query);
        }

        $query['query'] = trim($a_query);

        // "," means fixed search for lastname, firstname
        if (strpos($a_query, ',')) {
            $comma_separated = explode(',', $a_query);

            if (count($comma_separated) == 2) {
                if (trim($comma_separated[0])) {
                    $query['lastname'] = trim($comma_separated[0]);
                }
                if (trim($comma_separated[1])) {
                    $query['firstname'] = trim($comma_separated[1]);
                }
            }
        } else {
            $whitespace_separated = explode(' ', $a_query);
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
