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
 * User query class. Put any complex that queries for a set of users into
 * this class and keep ilObjUser "small".
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilUserQuery
{
    public const DEFAULT_ORDER_FIELD = 'login';

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
    private ?array $user_folder = null; // Missing array type.
    private array $additional_fields = array(); // Missing array type.
    private array $users = array(); // Missing array type.
    private string $first_letter = '';
    private bool $has_access = false;
    private string $authentication_method = '';
    protected array $udf_filter = array(); // Missing array type.
    /** @var string[] */
    private array $default_fields = array(
        "usr_id",
        "login",
        "firstname",
        "lastname",
        "email",
        "second_email",
        "time_limit_until",
        "time_limit_unlimited",
        "time_limit_owner",
        "last_login",
        "active"
    );

    public function __construct()
    {
    }

    /**
     * Set udf filter
     * @param array $a_val udf filter array
     */
    public function setUdfFilter(array $a_val): void // Missing array type.
    {
        $valid_udfs = [];

        $definitions = \ilUserDefinedFields::_getInstance()->getDefinitions();
        foreach ($a_val as $udf_name => $udf_value) {
            [$udf_string, $udf_id] = explode('_', $udf_name);
            if (array_key_exists((int) $udf_id, $definitions)) {
                $valid_udfs[$udf_name] = $udf_value;
            }
        }
        $this->udf_filter = $valid_udfs;
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
    public function setLastLogin(ilDateTime $dt = null): void
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
    public function setUserFolder(?array $a_fold_id): void // Missing array type.
    {
        $this->user_folder = $a_fold_id;
    }

    /**
     * Set additional fields (columns in usr_data or 'online_time')
     */
    public function setAdditionalFields(array $a_add): void // Missing array type.
    {
        $this->additional_fields = $a_add;
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
        global $DIC;

        $ilDB = $DIC['ilDB'];


        $udf_fields = array();
        $usr_ids = [];

        $join = "";

        if (is_array($this->additional_fields)) {
            foreach ($this->additional_fields as $f) {
                if (!in_array($f, $this->default_fields)) {
                    if ($f === "online_time") {
                        $this->default_fields[] = "ut_online.online_time";
                        $join = " LEFT JOIN ut_online ON (usr_data.usr_id = ut_online.usr_id) ";
                    } elseif (substr($f, 0, 4) === "udf_") {
                        $udf_fields[] = (int) substr($f, 4);
                    } else {
                        $this->default_fields[] = $f;
                    }
                }
            }
        }

        // if udf fields are involved we need the definitions
        $udf_def = array();
        if (count($udf_fields) > 0) {
            $udf_def = ilUserDefinedFields::_getInstance()->getDefinitions();
        }

        // join udf table
        foreach ($udf_fields as $id) {
            $udf_table = ($udf_def[$id]["field_type"] != UDF_TYPE_WYSIWYG)
                ? "udf_text"
                : "udf_clob";
            $join .= " LEFT JOIN " . $udf_table . " ud_" . $id . " ON (ud_" . $id . ".field_id=" . $ilDB->quote($id) . " AND ud_" . $id . ".usr_id = usr_data.usr_id) ";
        }

        // count query
        $count_query = "SELECT count(usr_data.usr_id) cnt" .
            " FROM usr_data";

        $all_multi_fields = array("interests_general", "interests_help_offered", "interests_help_looking");
        $multi_fields = array();

        $sql_fields = array();
        foreach ($this->default_fields as $idx => $field) {
            if (!$field) {
                continue;
            }

            if (in_array($field, $all_multi_fields)) {
                $multi_fields[] = $field;
            } elseif (strpos($field, ".") === false) {
                $sql_fields[] = "usr_data." . $field;
            } else {
                $sql_fields[] = $field;
            }
        }

        // udf fields
        foreach ($udf_fields as $id) {
            $sql_fields[] = "ud_" . $id . ".value udf_" . $id;
        }

        // basic query
        $query = "SELECT " . implode(",", $sql_fields) .
            " FROM usr_data" .
            $join;

        $count_query .= " " . $join;

        // filter
        $query .= " WHERE usr_data.usr_id <> " . $ilDB->quote(ANONYMOUS_USER_ID, "integer");

        // User filter
        $count_query .= " WHERE 1 = 1 ";
        $count_user_filter = "usr_data.usr_id != " . $ilDB->quote(ANONYMOUS_USER_ID, "integer");
        if ($this->users and is_array(($this->users))) {
            $query .= ' AND ' . $ilDB->in('usr_data.usr_id', $this->users, false, 'integer');
            $count_user_filter = $ilDB->in('usr_data.usr_id', $this->users, false, 'integer');
        }

        $count_query .= " AND " . $count_user_filter . " ";
        $where = " AND";

        if ($this->first_letter != "") {
            $add = $where . " (" . $ilDB->upper($ilDB->substr("usr_data.lastname", 1, 1)) . " = " . $ilDB->upper($ilDB->quote($this->first_letter, "text")) . ") ";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        if ($this->text_filter != "") {		// email, name, login
            $add = $where . " (" . $ilDB->like("usr_data.login", "text", "%" . $this->text_filter . "%") . " " .
                "OR " . $ilDB->like("usr_data.firstname", "text", "%" . $this->text_filter . "%") . " " .
                "OR " . $ilDB->like("usr_data.lastname", "text", "%" . $this->text_filter . "%") . " " .
                "OR " . $ilDB->like("usr_data.second_email", "text", "%" . $this->text_filter . "%") . " " .
                "OR " . $ilDB->like("usr_data.email", "text", "%" . $this->text_filter . "%") . ") ";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        if ($this->activation != "") {		// activation
            if ($this->activation === "inactive") {
                $add = $where . " usr_data.active = " . $ilDB->quote(0, "integer") . " ";
            } else {
                $add = $where . " usr_data.active = " . $ilDB->quote(1, "integer") . " ";
            }
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        if ($this->last_login instanceof ilDateTime) {	// last login
            if (ilDateTime::_before($this->last_login, new ilDateTime(time(), IL_CAL_UNIX), IL_CAL_DAY)) {
                $add = $where . " usr_data.last_login < " .
                    $ilDB->quote($this->last_login->get(IL_CAL_DATETIME), "timestamp");
                $query .= $add;
                $count_query .= $add;
                $where = " AND";
            }
        }
        if ($this->limited_access) {		// limited access
            $add = $where . " usr_data.time_limit_unlimited= " . $ilDB->quote(0, "integer");
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        // udf filter
        foreach ($this->getUdfFilter() as $k => $f) {
            if ($f != "") {
                $udf_id = explode("_", $k)[1];
                if ($udf_def[$udf_id]["field_type"] == UDF_TYPE_TEXT) {
                    $add = $where . " " . $ilDB->like("ud_" . $udf_id . ".value", "text", "%" . $f . "%");
                } else {
                    $add = $where . " ud_" . $udf_id . ".value = " . $ilDB->quote($f, "text");
                }
                $query .= $add;
                $count_query .= $add;
                $where = " AND";
            }
        }

        if ($this->has_access) { //user is limited but has access
            $unlimited = "time_limit_unlimited = " . $ilDB->quote(1, 'integer');
            $from = "time_limit_from < " . $ilDB->quote(time(), 'integer');
            $until = "time_limit_until > " . $ilDB->quote(time(), 'integer');

            $add = $where . ' (' . $unlimited . ' OR (' . $from . ' AND ' . $until . '))';
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }
        if ($this->no_courses) {		// no courses assigned
            $add = $where . " usr_data.usr_id NOT IN (" .
                "SELECT DISTINCT ud.usr_id " .
                "FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) " .
                "JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) " .
                "WHERE od.title LIKE 'il_crs_%')";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }
        if ($this->no_groups) {		// no groups assigned
            $add = $where . " usr_data.usr_id NOT IN (" .
                "SELECT DISTINCT ud.usr_id " .
                "FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) " .
                "JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) " .
                "WHERE od.title LIKE 'il_grp_%')";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }
        if ($this->crs_grp > 0) {		// members of course/group
            $cgtype = ilObject::_lookupType($this->crs_grp, true);
            $add = $where . " usr_data.usr_id IN (" .
                "SELECT DISTINCT ud.usr_id " .
                "FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) " .
                "JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) " .
                "WHERE od.title = " . $ilDB->quote("il_" . $cgtype . "_member_" . $this->crs_grp, "text") . ")";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }
        if ($this->role > 0) {		// global role
            $add = $where . " usr_data.usr_id IN (" .
                "SELECT DISTINCT ud.usr_id " .
                "FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) " .
                "WHERE rbac_ua.rol_id = " . $ilDB->quote($this->role, "integer") . ")";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        if ($this->user_folder) {
            $add = $where . " " . $ilDB->in('usr_data.time_limit_owner', $this->user_folder, false, 'integer');
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        if ($this->authentication_method != "") {		// authentication
            $add = $where . " usr_data.auth_mode = " . $ilDB->quote($this->authentication_method, "text") . " ";
            $query .= $add;
            $count_query .= $add;
            $where = " AND";
        }

        // order by
        switch ($this->order_field) {
            case  "access_until":
                if ($this->order_dir === "desc") {
                    $query .= " ORDER BY usr_data.active DESC, usr_data.time_limit_unlimited DESC, usr_data.time_limit_until DESC";
                } else {
                    $query .= " ORDER BY usr_data.active ASC, usr_data.time_limit_unlimited ASC, usr_data.time_limit_until ASC";
                }
                break;

            case "online_time":
                if ($this->order_dir === "desc") {
                    $query .= " ORDER BY ut_online.online_time DESC";
                } else {
                    $query .= " ORDER BY ut_online.online_time ASC";
                }
                break;

            default:
                if ($this->order_dir !== "asc" && $this->order_dir !== "desc") {
                    $this->order_dir = "asc";
                }
                if (substr($this->order_field, 0, 4) === "udf_") {
                    // #25311 check if order field is in field list
                    if (is_array($this->getUdfFilter()) && array_key_exists($this->order_field, $this->getUdfFilter())) {
                        $query .= " ORDER BY ud_" . ((int) substr($this->order_field, 4)) . ".value " . strtoupper($this->order_dir);
                    } else {
                        $query .= ' ORDER BY ' . self::DEFAULT_ORDER_FIELD . ' ' . strtoupper($this->order_dir);
                    }
                } else {
                    if (!in_array($this->order_field, $this->default_fields)) {
                        $this->order_field = "login";
                    }
                    $query .= " ORDER BY usr_data." . $this->order_field . " " . strtoupper($this->order_dir);
                }
                break;
        }

        // count query
        $set = $ilDB->query($count_query);
        $cnt = 0;
        if ($rec = $ilDB->fetchAssoc($set)) {
            $cnt = $rec["cnt"];
        }

        $offset = $this->offset;
        $limit = $this->limit;

        // #9866: validate offset against rowcount
        if ($offset >= $cnt) {
            $offset = 0;
        }

        $ilDB->setLimit($limit, $offset);

        if (count($multi_fields)) {
            $usr_ids = array();
        }

        // set query
        $set = $ilDB->query($query);
        $result = array();

        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[] = $rec;
            if (count($multi_fields)) {
                $usr_ids[] = (int) $rec["usr_id"];
            }
        }

        // add multi-field-values to user-data
        if (count($multi_fields) && count($usr_ids)) {
            $usr_multi = array();
            $set = $ilDB->query("SELECT * FROM usr_data_multi" .
                " WHERE " . $ilDB->in("usr_id", $usr_ids, "", "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $usr_multi[(int) $row["usr_id"]][$row["field_id"]][] = $row["value"];
            }
            foreach ($result as $idx => $item) {
                if (isset($usr_multi[$item["usr_id"]])) {
                    $result[$idx] = array_merge($item, $usr_multi[(int) $item["usr_id"]]);
                }
            }
        }
        return array("cnt" => $cnt, "set" => $result);
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
        array $a_user_folder_filter = null,
        array $a_additional_fields = null,
        array $a_user_filter = null,
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
