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

use ILIAS\User\LocalDIC;
use ILIAS\User\Profile\DataQuery;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileFieldsConfigurationRepository;
use ILIAS\User\Profile\DataRepository as ProfileDataRepository;
use ILIAS\Language\Language;

/**
 * User query class. Put any complex that queries for a set of users into
 * this class and keep ilObjUser "small".
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilUserQuery
{
    public const DEFAULT_ORDER_FIELD = 'login';

    private const array DEFAULT_MULTI_FIELDS = [
        'interests_general',
        'interests_help_offered',
        'interests_help_looking'
    ];

    private const array DEFAULT_FIELDS = [
        'usr_id',
        'login',
        'firstname',
        'lastname',
        'email',
        'second_email',
        'time_limit_until',
        'time_limit_unlimited',
        'time_limit_owner',
        'last_login',
        'active'
    ];

    private Language $lng;
    private ilDBInterface $db;

    private string $order_field = self::DEFAULT_ORDER_FIELD;
    private string $order_dir = 'asc';
    private int $offset = 0;
    private int $limit = 50;
    private string $text_filter = '';
    private string $activation = '';
    private ?ilDateTime $last_login = null;
    private bool $limited_access = false;
    private bool $no_courses = false;
    private bool $no_groups = false;
    private int $crs_grp = 0;
    private int $role = 0;
    private array $user_folder = [];
    private array $additional_fields = [];
    private array $users = [];
    private string $first_letter = '';
    private bool $has_access = false;
    private string $authentication_method = '';
    protected array $udf_filter = [];

    private ProfileFieldsConfigurationRepository $profile_fields_repository;
    private ProfileDataRepository $profile_data_repository;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->db = $DIC['ilDB'];

        $local_dic = LocalDIC::dic();
        $this->profile_fields_repository = $local_dic[ProfileFieldsConfigurationRepository::class];
        $this->profile_data_repository = $local_dic[ProfileDataRepository::class];
    }

    /**
     * Set udf filter
     * @param array $a_val udf filter array
     */
    public function setUdfFilter(array $filter_array): void // Missing array type.
    {
        $this->udf_filter = array_reduce(
            array_keys($filter_array),
            function (array $c, string $v) use ($filter_array): array {
                if ($filter_array[$v] === '') {
                    return $c;
                }
                $c[mb_substr($v, 4)] = $filter_array[$v];
                return $c;
            },
            []
        );
    }

    /**
     * Get udf filter
     * @return array udf filter array
     */
    public function getUdfFilter(): array // Missing array type.
    {
        return $this->udf_filter;
    }

    /**
     * Set order field (column in usr_data)
     * Default order is 'login'
     */
    public function setOrderField(string $a_order): void
    {
        $this->order_field = $a_order;
    }

    /**
     * Set order direction
     * 'asc' or 'desc'
     * Default is 'asc'
     */
    public function setOrderDirection(string $a_dir): void
    {
        $this->order_dir = $a_dir;
    }

    public function setOffset(int $a_offset): void
    {
        $this->offset = $a_offset;
    }

    public function setLimit(int $a_limit): void
    {
        $this->limit = $a_limit;
    }

    /**
     * Text (like) filter in login, firstname, lastname or email
     */
    public function setTextFilter(string $a_filter): void
    {
        $this->text_filter = $a_filter;
    }

    /**
     * Set activation filter
     * 'active' or 'inactive' or empty
     */
    public function setActionFilter(string $a_activation): void
    {
        $this->activation = $a_activation;
    }

    /**
     * Set last login filter
     */
    public function setLastLogin(?ilDateTime $dt = null): void
    {
        $this->last_login = $dt;
    }

    /**
     * Enable limited access filter
     */
    public function setLimitedAccessFilter(bool $a_status): void
    {
        $this->limited_access = $a_status;
    }

    public function setNoCourseFilter(bool $a_no_course): void
    {
        $this->no_courses = $a_no_course;
    }

    public function setNoGroupFilter(bool $a_no_group): void
    {
        $this->no_groups = $a_no_group;
    }

    /**
     * Set course / group filter
     * object_id of course or group
     */
    public function setCourseGroupFilter(int $a_cg_id): void
    {
        $this->crs_grp = $a_cg_id;
    }

    /**
     * Set role filter
     * obj_id of role
     */
    public function setRoleFilter(int $a_role_id): void
    {
        $this->role = $a_role_id;
    }

    /**
     * Set user folder filter
     * reference id of user folder or category (local user administration)
     */
    public function setUserFolder(?array $user_folder_id): void
    {
        $this->user_folder = $user_folder_id ?? [];
    }

    /**
     * Set additional fields (columns in usr_data or 'online_time')
     */
    public function setAdditionalFields(array $additional_fields): void
    {
        $this->additional_fields = $additional_fields;
    }

    /**
     * Array with user ids to query against
     */
    public function setUserFilter(array $a_filter): void // Missing array type.
    {
        $this->users = $a_filter;
    }

    /**
     * set first letter lastname filter
     */
    public function setFirstLetterLastname(string $a_fll): void
    {
        $this->first_letter = $a_fll;
    }

    /**
     * set filter for user that are limited but has access
     */
    public function setAccessFilter(bool $a_access): void
    {
        $this->has_access = $a_access;
    }

    /**
     * Set authentication filter
     * @param string $a_authentication 'default', 'local' or 'lti'
     */
    public function setAuthenticationFilter(string $a_authentication): void
    {
        $this->authentication_method = $a_authentication;
    }

    /**
     * Query usr_data
     * @return array ('cnt', 'set')
     */
    public function query(): array
    {
        /** @var \ILIAS\User\Profile\DataQuery $query */
        $query = $this->addUdfFilterToQuery(
            $this->addOrderToQuery(
                array_reduce(
                    $this->additional_fields,
                    function (DataQuery $c, string $v): DataQuery {
                        if (in_array($v, self::DEFAULT_FIELDS)) {
                            return $c;
                        }

                        if (in_array($v, self::DEFAULT_MULTI_FIELDS)) {
                            return $c->withAdditionalMultiField($v);
                        }

                        if ($v === 'online_time') {
                            return $c->withAdditionalAdditionalTableSelectField('ut_online.online_time')
                                ->withAdditionalJoin('LEFT JOIN ut_online ON (usr_data.usr_id = ut_online.usr_id)');
                        }

                        if ($v === 'dpro_agreed_on') {
                            return $c->withAdditionalAdditionalTableSelectField('dpro.dpro_agreed_on')
                                ->withAdditionalJoin(
                                    'LEFT JOIN (SELECT value AS dpro_agreed_on, usr_id' . PHP_EOL
                                    . 'FROM usr_pref WHERE keyword = "dpro_agree_date") AS dpro' . PHP_EOL
                                    . 'ON (usr_data.usr_id = dpro.usr_id)'
                                );
                        }

                        if (str_starts_with($v, 'udf_')) {
                            return $c->withAdditionalUdfField(
                                $this->profile_fields_repository->getByIdentifier(mb_substr($v, 4))
                            );
                        }

                        return $c->withAdditionalDefaultTableSelectField($v);
                    },
                    $this->profile_data_repository->getProfileDataQuery(self::DEFAULT_FIELDS)
                )
            )->withLimitedUsers($this->users)
        );

        if ($this->first_letter !== '') {
            $query = $query->withAdditionalWhere(
                "({$this->db->upper($this->db->substr('usr_data.lastname', 1, 1))})"
            );
        }

        if ($this->text_filter !== '') {		// email, name, login
            $query = $query->withAdditionalWhere(
                "({$this->db->like('usr_data.login', ilDBConstants::T_TEXT, '%' . $this->text_filter . '%')} "
                . "OR {$this->db->like('usr_data.firstname', ilDBConstants::T_TEXT, '%' . $this->text_filter . '%')} "
                . "OR {$this->db->like('usr_data.lastname', ilDBConstants::T_TEXT, '%' . $this->text_filter . '%')} "
                . "OR {$this->db->like('usr_data.second_email', ilDBConstants::T_TEXT, '%' . $this->text_filter . '%')} "
                . "OR {$this->db->like('usr_data.email', ilDBConstants::T_TEXT, '%' . $this->text_filter . '%')})"
            );
        }

        if ($this->activation === 'inactive') {
            $query = $query->withAdditionalWhere(
                "usr_data.active = {$this->db->quote(0, ilDBConstants::T_INTEGER)}"
            );
        }

        if ($this->activation === 'active') {
            $query = $query->withAdditionalWhere(
                "usr_data.active = {$this->db->quote(1, ilDBConstants::T_INTEGER)}"
            );
        }

        if ($this->last_login instanceof ilDateTime) {	// last login
            if (ilDateTime::_before($this->last_login, new ilDateTime(time() + (60 * 60 * 24), IL_CAL_UNIX), IL_CAL_DAY)) {
                $query = $query->withAdditionalWhere(
                    "usr_data.last_login < {$this->db->quote($this->last_login->get(IL_CAL_DATETIME), ilDBConstants::T_TIMESTAMP)}"
                );
            }
        }
        if ($this->limited_access) {
            $query = $query->withAdditionalWhere(
                "usr_data.time_limit_unlimited= {$this->db->quote(0, ilDBConstants::T_INTEGER)}"
            );
        }

        if ($this->has_access) {
            $query = $query->withAdditionalWhere(
                "(time_limit_unlimited = {$this->db->quote(1, ilDBConstants::T_INTEGER)} "
                . "OR (time_limit_from < {$this->db->quote(time(), ilDBConstants::T_INTEGER)} "
                . "AND time_limit_until > {$this->db->quote(time(), ilDBConstants::T_INTEGER)}))"
            );
        }

        if ($this->no_courses) {
            $query = $query->withAdditionalWhere(
                'usr_data.usr_id NOT IN ('
                . 'SELECT DISTINCT ud.usr_id '
                . 'FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) '
                . 'JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) '
                . 'JOIN rbac_fa ON (rbac_ua.rol_id = rbac_fa.rol_id) '
                . 'JOIN tree ON (rbac_fa.parent = tree.child) '
                . 'WHERE od.title LIKE "il_crs_%" '
                . 'AND rbac_fa.assign = "y" '
                . 'AND tree.tree > 0)'
            );
        }

        if ($this->no_groups) {
            $query = $query->withAdditionalWhere(
                'usr_data.usr_id NOT IN ('
                . 'SELECT DISTINCT ud.usr_id '
                . 'FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) '
                . 'JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) '
                . 'JOIN rbac_fa ON (rbac_ua.rol_id = rbac_fa.rol_id) '
                . 'JOIN tree ON (rbac_fa.parent = tree.child) '
                . 'WHERE od.title LIKE "il_grp_%" '
                . 'AND rbac_fa.assign = "y" '
                . 'AND tree.tree > 0)'
            );
        }

        if ($this->crs_grp > 0) {
            $cgtype = ilObject::_lookupType($this->crs_grp, true);
            $query = $query->withAdditionalWhere(
                'usr_data.usr_id IN ('
                . 'SELECT DISTINCT ud.usr_id '
                . 'FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) '
                . 'JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) '
                . "WHERE od.title = {$this->db->quote("il_{$cgtype}_member_{$this->crs_grp}", ilDBConstants::T_TEXT)})"
            );
        }

        if ($this->role > 0) {
            $query = $query->withAdditionalWhere(
                'usr_data.usr_id IN ('
                . 'SELECT DISTINCT ud.usr_id '
                . 'FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) '
                . "WHERE rbac_ua.rol_id = {$this->db->quote($this->role, ilDBConstants::T_INTEGER)})"
            );
        }

        if ($this->user_folder !== []) {
            $query = $query->withAdditionalWhere(
                $this->db->in('usr_data.time_limit_owner', $this->user_folder, false, ilDBConstants::T_INTEGER)
            );
        }

        if ($this->authentication_method !== '') {
            $query = $query->withAdditionalWhere(
                "usr_data.auth_mode = {$this->db->quote($this->authentication_method, ilDBConstants::T_TEXT)}"
            );
        }

        return $this->profile_data_repository->getCountAndRecordsForQuery(
            $query,
            $this->offset,
            $this->limit
        );
    }

    private function addOrderToQuery(DataQuery $query): DataQuery
    {
        $direction = $this->order_dir === 'desc' ? 'DESC' : 'ASC';
        switch ($this->order_field) {
            case 'time_limit_until':
                return $query->withDefaultTableOrderFields(
                    ['active', 'time_limit_unlimited', 'time_limit_until'],
                    $direction
                );

            case 'online_time':
                return $query->withAdditionalTableOrder("ORDER BY ut_online.online_time {$direction}");

            default:
                if (!in_array($this->order_field, array_merge(self::DEFAULT_FIELDS, $this->additional_fields))) {
                    $this->order_field = 'login';
                }

                if (in_array($this->order_field, self::DEFAULT_MULTI_FIELDS)
                    || str_starts_with($this->order_field, 'udf_')) {
                    return $query->withMultiDataTableOrder($this->order_field, $this->order_dir);
                }

                return $query->withDefaultTableOrderFields([$this->order_field], $this->order_dir);
        }
    }

    private function addUdfFilterToQuery(DataQuery $query): DataQuery
    {
        if ($this->getUdfFilter() === []) {
            return $query;
        }

        $udf_filter = $this->getUdfFilter();
        return array_reduce(
            array_keys(
                array_filter($udf_filter)
            ),
            fn(DataQuery $c, string $v): DataQuery => $c->withAdditionalMultiDataWhere(
                $v,
                $udf_filter[$v]
            ),
            $query->withJoinedMultiDataTable()
        );
    }


    /**
     * Get data for user administration list.
     * @deprecated
     */
    public static function getUserListData(
        string $a_order_field,
        string $a_order_dir,
        int $a_offset,
        int $a_limit,
        string $a_string_filter = "",
        string $a_activation_filter = "",
        ?ilDateTime $a_last_login_filter = null,
        bool $a_limited_access_filter = false,
        bool $a_no_courses_filter = false,
        int $a_course_group_filter = 0,
        int $a_role_filter = 0,
        ?array $a_user_folder_filter = null,
        ?array $a_additional_fields = null,
        ?array $a_user_filter = null,
        string $a_first_letter = "",
        string $a_authentication_filter = ""
    ): array {
        $query = new ilUserQuery();
        $query->setOrderField($a_order_field);
        $query->setOrderDirection($a_order_dir);
        $query->setOffset($a_offset);
        $query->setLimit($a_limit);
        $query->setTextFilter($a_string_filter);
        $query->setActionFilter($a_activation_filter);
        $query->setLastLogin($a_last_login_filter);
        $query->setLimitedAccessFilter($a_limited_access_filter);
        $query->setNoCourseFilter($a_no_courses_filter);
        $query->setCourseGroupFilter($a_course_group_filter);
        $query->setRoleFilter($a_role_filter);
        $query->setUserFolder($a_user_folder_filter);
        $query->setAdditionalFields($a_additional_fields ?? []);
        $query->setUserFilter($a_user_filter ?? []);
        $query->setFirstLetterLastname($a_first_letter);
        $query->setAuthenticationFilter($a_authentication_filter);
        return $query->query();
    }
}
