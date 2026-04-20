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

declare(strict_types=1);

namespace ILIAS\User\Profile;

use ILIAS\User\Profile\Fields\Standard\FirstName;
use ILIAS\User\Profile\Fields\Standard\LastName;
use ILIAS\User\Profile\Fields\Standard\Email;
use ILIAS\User\Profile\Fields\Standard\SecondEmail;
use ILIAS\User\Profile\Fields\Standard\Genders;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileFieldsConfigurationRepository;
use ILIAS\User\Search\AutocompleteQuery;
use ILIAS\User\Search\DefaultAutocompleteItem;
use ILIAS\User\Settings\DataRepository as SettingsDataRepository;
use ILIAS\ResourceStorage\Services as ResourceStorage;

class DatabaseDataRepository implements DataRepository
{
    private const string USER_BASE_TABLE = 'usr_data';
    public const string USER_VALUES_TABLE = 'usr_profile_data';

    private const string NO_AVATAR_RID = '-';

    private const array SEARCH_FIELDS = [
        'login' => true,
        'firstname' => false,
        'lastname' => false,
        'email' => false,
        'second_email' => false
    ];

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly ResourceStorage $irss
    ) {
    }

    public function getDefault(): Data
    {
        return new Data();
    }

    public function getSingle(int $id): Data
    {
        $base_query = $this->db->query(
            'SELECT * FROM ' . self::USER_BASE_TABLE . " WHERE usr_id={$id}"
        );

        $additional_query = $this->db->query(
            'SELECT field_id, value FROM ' . self::USER_VALUES_TABLE . " WHERE usr_id = {$id}"
        );

        $base_data = $this->db->fetchObject($base_query);
        if ($base_data === null) {
            throw new \InvalidArgumentException(
                'This user does not exist.'
            );
        }

        return $this->buildFromData(
            $base_data,
            $this->db->fetchAll(
                $additional_query,
                \ilDBConstants::FETCHMODE_OBJECT
            )
        );

    }

    public function getMultiple(array $user_ids): \Generator
    {
        $query = $this->db->query(
            'SELECT * FROM ' . self::USER_BASE_TABLE
                . " WHERE {$this->db->in('usr_id', $user_ids, false, \ilDBConstants::T_INTEGER)}"
        );

        $prepared_query = $this->db->prepare('SELECT field_id, value FROM '
            . self::USER_VALUES_TABLE . ' WHERE usr_id = ?');

        while (($base_data = $this->db->fetchObject($query)) !== null) {
            yield $this->buildFromData(
                $base_data,
                $this->db->fetchAll(
                    $this->db->execute($prepared_query, [$base_data->usr_id]),
                    \ilDBConstants::FETCHMODE_OBJECT
                )
            );
        }

        $this->db->free($prepared_query);
    }

    public function store(Data $user_data): void
    {
        $system_information = $user_data->getSystemInformation();
        $this->db->replace(
            self::USER_BASE_TABLE,
            [
                'usr_id' => [
                    \ilDBConstants::T_INTEGER,
                    $user_data->getId()
                ]
            ],
            [
                'login' => [\ilDBConstants::T_TEXT, $user_data->getAlias()],
                'firstname' => [\ilDBConstants::T_TEXT, $user_data->getFirstname()],
                'lastname' => [\ilDBConstants::T_TEXT, $user_data->getLastname()],
                'title' => [\ilDBConstants::T_TEXT, $user_data->getTitle()],
                'gender' => [\ilDBConstants::T_TEXT, $user_data->getGender()?->value],
                'rid' => [\ilDBConstants::T_TEXT, $user_data->getAvatarRid()?->serialize() ?? self::NO_AVATAR_RID],
                'email' => [\ilDBConstants::T_TEXT, $user_data->getEmail()],
                'second_email' => [\ilDBConstants::T_TEXT, $user_data->getSecondEmail()],
                'hobby' => [\ilDBConstants::T_TEXT, $user_data->getHobby()],
                'institution' => [\ilDBConstants::T_TEXT, $user_data->getInstitution()],
                'department' => [\ilDBConstants::T_TEXT, $user_data->getDepartment()],
                'street' => [\ilDBConstants::T_TEXT, $user_data->getStreet()],
                'city' => [\ilDBConstants::T_TEXT, $user_data->getCity()],
                'zipcode' => [\ilDBConstants::T_TEXT, $user_data->getZipcode()],
                'country' => [\ilDBConstants::T_TEXT, $user_data->getCountry()],
                'phone_office' => [\ilDBConstants::T_TEXT, $user_data->getPhoneOffice()],
                'phone_home' => [\ilDBConstants::T_TEXT, $user_data->getPhoneHome()],
                'phone_mobile' => [\ilDBConstants::T_TEXT, $user_data->getPhoneMobile()],
                'fax' => [\ilDBConstants::T_TEXT, $user_data->getFax()],
                'birthday' => [\ilDBConstants::T_DATE, $user_data->getBirthday()?->format('Y-m-d')],
                'referral_comment' => [\ilDBConstants::T_TEXT, $user_data->getReferralComment()],
                'matriculation' => [\ilDBConstants::T_TEXT, $user_data->getMatriculation()],
                'latitude' => [\ilDBConstants::T_TEXT, $user_data->getGeoCoordinates()['latitude'] ?? null],
                'longitude' => [\ilDBConstants::T_TEXT, $user_data->getGeoCoordinates()['longitude'] ?? null],
                'loc_zoom' => [\ilDBConstants::T_INTEGER, $user_data->getGeoCoordinates()['zoom'] ?? 0],
                'last_password_change' => [\ilDBConstants::T_INTEGER, $system_information['last_password_change']],
                'passwd' => [\ilDBConstants::T_TEXT, $system_information['passwd']],
                'passwd_salt' => [\ilDBConstants::T_TEXT, $system_information['passwd_salt']],
                'passwd_enc_type' => [\ilDBConstants::T_TEXT, $system_information['passwd_enc_type']],
                'passwd_policy_reset' => [\ilDBConstants::T_INTEGER, $system_information['passwd_policy_reset'] ? 1 : 0],
                'client_ip' => [\ilDBConstants::T_TEXT, $system_information['client_ip']],
                'last_login' => [
                    \ilDBConstants::T_TIMESTAMP,
                    $system_information['last_login'] !== '' ? $system_information['last_login'] : null
                ],
                'first_login' => [
                    \ilDBConstants::T_TIMESTAMP,
                    $system_information['first_login'] !== '' ? $system_information['first_login'] : null
                ],
                'last_profile_prompt' => [
                    \ilDBConstants::T_TIMESTAMP,
                    $system_information['last_profile_prompt'] !== '' ? $system_information['last_profile_prompt'] : null
                ],
                'active' => [\ilDBConstants::T_INTEGER, $system_information['active']],
                'approve_date' => [\ilDBConstants::T_TIMESTAMP, $system_information['approve_date']],
                'agree_date' => [\ilDBConstants::T_TIMESTAMP, $system_information['agree_date']],
                'inactivation_date' => [\ilDBConstants::T_TIMESTAMP, $system_information['inactivation_date']],
                'time_limit_owner' => [\ilDBConstants::T_INTEGER, $system_information['time_limit_owner']],
                'time_limit_unlimited' => [\ilDBConstants::T_INTEGER, $system_information['time_limit_unlimited'] ? 1 : 0],
                'time_limit_from' => [\ilDBConstants::T_INTEGER, $system_information['time_limit_from']],
                'time_limit_until' => [\ilDBConstants::T_INTEGER, $system_information['time_limit_until']],
                'profile_incomplete' => [\ilDBConstants::T_INTEGER, $system_information['profile_incomplete']],
                'auth_mode' => [\ilDBConstants::T_TEXT, $system_information['auth_mode']],
                'ext_account' => [\ilDBConstants::T_TEXT, $system_information['ext_account']],
                'is_self_registered' => [\ilDBConstants::T_INTEGER, $system_information['is_self_registered'] ? 1 : 0],
                'last_update' => [\ilDBConstants::T_TIMESTAMP, date('Y-m-d H:i:s')],
                'create_date' => [\ilDBConstants::T_TIMESTAMP, $system_information['create_date']],
                'last_visited' => [
                    \ilDBConstants::T_TEXT,
                    $system_information['last_visited'] === [] ? null : serialize($system_information['last_visited'])
                ]
            ]
        );

        $this->storeAdditionalFields($user_data);
    }

    public function deleteForFieldIdentifier(string $identifier): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::USER_VALUES_TABLE
                . " WHERE field_id='{$this->db->quote($identifier, \ilDBConstants::T_TEXT)}'"
        );
    }

    public function deleteForUser(int $usr_id): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::USER_BASE_TABLE
                . " WHERE usr_id='{$this->db->quote($usr_id, \ilDBConstants::T_INTEGER)}'"
        );
        $this->db->manipulate(
            'DELETE FROM ' . self::USER_VALUES_TABLE
                . " WHERE usr_id='{$this->db->quote($usr_id, \ilDBConstants::T_INTEGER)}'"
        );
    }

    public function storePasswordFor(
        int $usr_id,
        string $password,
        string $encoding_type,
        ?string $salt
    ): void {
        $this->db->manipulateF(
            'UPDATE ' . self::USER_BASE_TABLE . ' SET passwd = %s,' . PHP_EOL
            . 'passwd_enc_type = %s, passwd_salt = %s WHERE usr_id = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_INTEGER],
            [$password, $encoding_type, $salt, $usr_id]
        );
    }

    public function storeLoginFor(
        int $usr_id,
        string $login
    ): void {
        $this->db->manipulateF(
            'UPDATE ' . self::USER_BASE_TABLE . ' SET login = %s WHERE usr_id = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_INTEGER],
            [$login, $usr_id]
        );
    }

    public function storeLastVisitedFor(
        int $usr_id,
        array $last_visited
    ): void {
        $this->db->manipulateF(
            'UPDATE ' . self::USER_BASE_TABLE . ' SET last_visited = %s WHERE usr_id = %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_INTEGER],
            [
                $last_visited === [] ? null : serialize($last_visited),
                $usr_id
            ]
        );
    }

    public function searchUsers(
        SettingsDataRepository $settings_data_repository,
        ProfileFieldsConfigurationRepository $profile_fields_config_repo,
        AutocompleteQuery $autocomplete_query
    ): array {
        $where = $this->buildSearchUsersWhereString(
            $profile_fields_config_repo,
            $autocomplete_query
        );

        if ($where === null) {
            return [];
        }

        $query = $this->db->query(
            $settings_data_repository->getSearchSelectConditionalOnVisibility(
                self::USER_BASE_TABLE,
                ...array_keys(self::SEARCH_FIELDS)
            ) . PHP_EOL
            . $where
        );

        $results = [];
        while (($row = $this->db->fetchObject($query)) !== null) {
            $results[] = new DefaultAutocompleteItem(
                $row->login,
                $row->lastname ?? '',
                $row->firstname ?? '',
                $autocomplete_query->getUnprocessedSearchTerm()
            );
        }
        return $results;
    }

    public function getProfileDataQuery(
        array $select_fields
    ): DataQuery {
        return new DataQuery(
            $this->db,
            self::USER_BASE_TABLE,
            self::USER_VALUES_TABLE,
            $select_fields
        );
    }

    public function getCountAndRecordsForQuery(
        DataQuery $query,
        int $offset,
        int $limit
    ): array {
        $prepared_query = $query->withAdditionalSelectAndJoinForUdfAndMultiValueFields();
        $cnt = $this->db->fetchObject(
            $this->db->query($prepared_query->buildCntQueryString())
        )->cnt ?? 0;

        if ($offset >= $cnt) {
            $offset = 0;
        }

        $this->db->setLimit($limit, $offset);

        return [
            'cnt' => $cnt,
            'set' => $this->retrieveRecordsFromQuery($prepared_query)
        ];
    }

    private function buildFromData(
        \stdClass $base_data,
        array $additional_data
    ): Data {
        return (new Data(
            $base_data->usr_id,
            $base_data->login,
            $base_data->rid !== null && $base_data->rid !== self::NO_AVATAR_RID
                ? $this->irss->manage()->find($base_data->rid)
                : null,
            $base_data->firstname ?? '',
            $base_data->lastname ?? '',
            $base_data->title ?? '',
            Genders::tryFrom($base_data->gender ?? ''),
            $base_data->birthday !== null
                ? new \DateTimeImmutable($base_data->birthday, new \DateTimeZone('UTC'))
                : null,
            $base_data->institution ?? '',
            $base_data->department ?? '',
            $base_data->street ?? '',
            $base_data->city ?? '',
            $base_data->zipcode ?? '',
            $base_data->country ?? '',
            $base_data->email ?? '',
            $base_data->second_email,
            $base_data->phone_office ?? '',
            $base_data->phone_home ?? '',
            $base_data->phone_mobile ?? '',
            $base_data->fax ?? '',
            $base_data->matriculation ?? '',
            $base_data->hobby ?? '',
            $base_data->referral_comment ?? '',
            [
                'latitude' => $base_data->latitude,
                'longitude' => $base_data->longitude,
                'zoom' => $base_data->loc_zoom
            ],
            array_reduce(
                $additional_data,
                static function (array $c, \stdClass $v): array {
                    if (!array_key_exists($v->field_id, $c)) {
                        $c[$v->field_id] = [];
                    }
                    $c[$v->field_id][] = $v->value;
                    return $c;
                },
                []
            )
        ))->withSystemInformation([
            'last_password_change' => $base_data->last_password_change,
            'login_attempts' => $base_data->login_attempts,
            'passwd' => $base_data->passwd,
            'passwd_salt' => $base_data->passwd_salt,
            'passwd_enc_type' => $base_data->passwd_enc_type,
            'passwd_policy_reset' => $base_data->passwd_policy_reset === 1,
            'client_ip' => $base_data->client_ip ?? '',
            'last_login' => $base_data->last_login ?? '',
            'first_login' => $base_data->first_login ?? '',
            'last_profile_prompt' => $base_data->last_profile_prompt ?? '',
            'active' => $base_data->active,
            'approve_date' => $base_data->approve_date,
            'agree_date' => $base_data->agree_date,
            'inactivation_date' => $base_data->inactivation_date,
            'time_limit_owner' => $base_data->time_limit_owner,
            'time_limit_unlimited' => $base_data->time_limit_unlimited === 1,
            'time_limit_from' => $base_data->time_limit_from,
            'time_limit_until' => $base_data->time_limit_until,
            'profile_incomplete' => $base_data->profile_incomplete === 1,
            'auth_mode' => $base_data->auth_mode,
            'ext_account' => $base_data->ext_account,
            'is_self_registered' => $base_data->is_self_registered === 1,
            'last_update' => $base_data->last_update ?? '',
            'create_date' => $base_data->create_date ?? '',
            'last_visited' => $this->buildLastVisited($base_data->last_visited)
        ]);
    }

    private function storeAdditionalFields(Data $user_data): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::USER_VALUES_TABLE
            . " WHERE usr_id = {$user_data->getId()}"
        );

        $values_for_storage = $user_data->getAdditionalFieldsStorageValues($this->db);
        if ($values_for_storage === '') {
            return;
        }

        $this->db->manipulate(
            'INSERT INTO ' . self::USER_VALUES_TABLE . ' (usr_id, field_id, value) '
                . 'VALUES ' . $values_for_storage
        );
    }

    private function buildSearchUsersWhereString(
        ProfileFieldsConfigurationRepository $profile_fields_config_repo,
        AutocompleteQuery $autocomplete_query
    ): ?string {
        $available_fields = array_filter(
            $this->getSearchFieldsWithAvailability(
                $profile_fields_config_repo,
                $autocomplete_query
            )
        );

        if ($available_fields === []) {
            return null;
        }

        $outer_conditions = [];
        $outer_conditions[] = 'usr_data.usr_id != ' . $this->db->quote(ANONYMOUS_USER_ID, \ilDBConstants::T_INTEGER);
        $outer_conditions[] = 'usr_data.active != ' . $this->db->quote(0, \ilDBConstants::T_INTEGER);

        if (\ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            $outer_conditions[] = $this->db->in(
                'time_limit_owner',
                \ilUserFilter::getInstance()->getFolderIds(),
                false,
                'integer'
            );
        }

        $outer_conditions[] = '(' . implode(
            ' OR ',
            array_map(
                fn(string $v) => $this->db->like($v, \ilDBConstants::T_TEXT, "%{$available_fields[$v]}%"),
                array_keys($available_fields)
            )
        ) . ')';

        return ' WHERE ' . implode(' AND ', $outer_conditions);
    }

    private function getSearchFieldsWithAvailability(
        ProfileFieldsConfigurationRepository $profile_fields_config_repo,
        AutocompleteQuery $autocomplete_query
    ): array {
        $search_term = $autocomplete_query->getSearchTermQueryString();
        $search_term_long_enough = $autocomplete_query->checkSearchTermLength($search_term);
        $firstname_term = $autocomplete_query->getFirstnameQueryString();
        $lastname_term = $autocomplete_query->getLastnameQueryString();

        return array_merge(
            self::SEARCH_FIELDS,
            [
                'login' => $search_term_long_enough ? $search_term : null,
                'firstname' => $profile_fields_config_repo->getByClass(FirstName::class)->isSearchable()
                    && $autocomplete_query->checkSearchTermLength($firstname_term)
                    ? $firstname_term : null,
                'lastname' => $profile_fields_config_repo->getByClass(LastName::class)->isSearchable()
                    && $autocomplete_query->checkSearchTermLength($lastname_term)
                    ? $autocomplete_query->getLastnameQueryString() : null,
                'email' => $profile_fields_config_repo->getByClass(Email::class)->isSearchable()
                    && $search_term_long_enough
                    ? $autocomplete_query->getSearchTermQueryString() : null,
                'second_email' => $profile_fields_config_repo->getByClass(SecondEmail::class)->isSearchable()
                    && $search_term_long_enough
                    ? $autocomplete_query->getSearchTermQueryString() : null
            ]
        );
    }

    private function buildLastVisited(?string $last_visited): array
    {
        if ($last_visited === null) {
            return [];
        }

        $unserialized = unserialize($last_visited, ['allowed_classes' => false]);

        if (!is_array($unserialized)) {
            return [];
        }

        return $unserialized;
    }

    private function retrieveRecordsFromQuery(DataQuery $query): array
    {
        $statement = $this->db->query($query->buildRecordsQueryString());

        $result = [];
        while (($row = $this->db->fetchAssoc($statement)) !== null) {
            $row['usr_id'] = (int) $row['usr_id'];
            $result[] = $query->explodeArrayValues($row);
        }
        return $result;
    }
}
