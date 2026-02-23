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
use ILIAS\User\UserGUIRequest;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\Fields\Field as ProfileField;
use ILIAS\User\Context as ProfileContext;

/**
 * TableGUI class for user administration
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilUserTableGUI: ilFormPropertyDispatchGUI
 */
class ilUserTableGUI extends ilTable2GUI
{
    public const MODE_USER_FOLDER = 1;
    public const MODE_LOCAL_USER = 2;

    private ?int $mode = null;
    private int $user_folder_id = 0;

    private bool $with_write_access = false;
    protected Profile $user_profile;
    protected UserGUIRequest $user_request;
    protected array $udf_fields = [];
    protected array $filter = [];

    private ilRbacReview $rbac_review;
    private ilObjUser $current_user;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_mode = self::MODE_USER_FOLDER,
        bool $a_load_items = true
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->rbac_review = $DIC['rbacreview'];
        $this->current_user = $DIC['ilUser'];

        $this->user_folder_id = $a_parent_obj->getObject()->getRefId();
        $this->user_profile = LocalDIC::dic()[Profile::class];

        if ($DIC['ilAccess']->checkPositionAccess(ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, $this->user_folder_id)
            || $DIC['rbacsystem']->checkAccess('write', $this->user_folder_id)
            || $DIC['rbacsystem']->checkAccess('cat_administrate_users', $this->user_folder_id)) {
            $this->with_write_access = true;
        }

        $this->setMode($a_mode);
        $this->setId("user{$this->getUserFolderId()}");

        [
            'selectable_columns' => $this->selectable_columns,
            'udfs' => $this->udf_fields
        ] = $this->buildSelectableColumnsAndUdfs();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn('', '', '1', true);
        $this->addColumn($this->lng->txt('login'), 'username');

        foreach ($this->getSelectedColumns() as $c) {
            $this->addColumn(
                $this->selectable_columns[$c]['txt'] ?? $this->lng->txt($c),
                (string) $c
            );
        }

        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $this->addColumn($this->lng->txt('context'), 'time_limit_owner');
            $this->addColumn($this->lng->txt('role_assignment'));
        }

        $this->setShowRowsSelector(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);

        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj, 'applyFilter'));
        $this->setRowTemplate('tpl.user_list_row.html', 'components/ILIAS/User');
        $this->setEnableTitle(true);
        $this->initFilter();
        $this->setFilterCommand('applyFilter');
        $this->setDefaultOrderField('username');
        $this->setDefaultOrderDirection('asc');

        $this->setSelectAllCheckbox('id[]');
        $this->setTopCommands(true);

        $this->user_request = new UserGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        if ($this->getMode() == self::MODE_USER_FOLDER) {
            $this->setEnableAllCommand(true);

            $cmds = $a_parent_obj->getUserMultiCommands();
            foreach ($cmds as $cmd => $caption) {
                $this->addMultiCommand($cmd, $caption);
            }
        } else {
            $this->addMultiCommand('deleteUsers', $this->lng->txt('delete'));
        }

        if ($a_load_items) {
            $this->getItems();
        }
    }

    protected function setMode(int $a_mode): void
    {
        $this->mode = $a_mode;
    }

    protected function getMode(): int
    {
        return $this->mode;
    }

    protected function getUserFolderId(): int
    {
        return $this->user_folder_id;
    }

    public function getSelectableColumns(): array
    {
        return $this->selectable_columns;
    }

    private function buildSelectableColumnsAndUdfs(): array
    {


        $ufs = $this->user_profile->getVisibleFields($this->getFormContext());

        $udfs = [];
        $cols = array_reduce(
            $ufs,
            function (array $c, ProfileField $v) use (&$udfs): array {
                $identifier = $v->getIdentifier();
                if ($v->isCustom()) {
                    $udfs[] = $v;
                    $identifier = "udf_{$identifier}";
                    $c[$identifier] = null;
                }

                if (!array_key_exists($identifier, $c)) {
                    return $c;
                }

                $c[$identifier] = [
                    'txt' => $v->getLabel($this->lng),
                    'default' => false
                ];
                return $c;
            },
            [
                'firstname' => null,
                'lastname' => null,
                'time_limit_until' => [
                    'txt' => $this->lng->txt('access_until'),
                    'default' => false
                ],
                'last_login' => [
                    'txt' => $this->lng->txt('last_access'),
                    'default' => true
                ],
                'create_date' => [
                    'txt' => $this->lng->txt('create_date'),
                    'default' => false
                ],
                'approve_date' => [
                    'txt' => $this->lng->txt('approve_date'),
                    'default' => false
                ],
                'agree_date' => [
                    'txt' => $this->lng->txt('agree_date'),
                    'default' => false
                ],
                'dpro_agreed_on' => [
                    'txt' => $this->lng->txt('dpro_agreed_on'),
                    'default' => false
                ],
                'email' => null,
                'second_email' => null,
                'title' => null,
                'birthday' => null,
                'gender' => null,
                'institution' => null,
                'department' => null,
                'street' => null,
                'zipcode' => null,
                'city' => null,
                'country' => null,
                'phone_office' => null,
                'phone_home' => null,
                'phone_mobile' => null,
                'fax' => null,
                'matriculation' => null,
                'interests_general' => null,
                'interests_help_offered' => null,
                'interests_help_looking' => null,
                'auth_mode' => [
                    'txt' => $this->lng->txt('auth_mode'),
                    'default' => false
                ],
                'org_units' => null
            ]
        );

        return [
            'selectable_columns' => array_filter($cols),
            'udfs' => $udfs
        ];
    }

    protected function buildUserQuery(): ilUserQuery
    {
        $query = new ilUserQuery();
        $query->setOffset($this->getOffset());
        $query->setLimit($this->getLimit());
        $query->setTextFilter($this->filter['query'] ?? '');
        $query->setActionFilter($this->filter['activation'] ?? '');
        $query->setLastLogin($this->filter['last_login'] ?? null);
        $query->setLimitedAccessFilter($this->filter['limited_access'] ?? false);
        $query->setNoCourseFilter($this->filter['no_courses'] ?? false);
        $query->setNoGroupFilter($this->filter['no_groups'] ?? false);
        $query->setCourseGroupFilter($this->filter['course_group'] ?? 0);
        $query->setRoleFilter((int) ($this->filter['global_role'] ?? 0));
        $query->setUserFilter($this->filter['user_ids'] ?? []);
        $query->setFirstLetterLastname($this->user_request->getLetter());
        $query->setAuthenticationFilter($this->filter['authentication'] ?? '');
        return $query;
    }

    public function getItems(): void
    {
        $this->determineOffsetAndOrder();
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // All accessible users
            $user_filter = ilLocalUser::_getFolderIds(true);
        } else {
            if ($this->filter['time_limit_owner'] ?? null) {
                $user_filter = [$this->filter['time_limit_owner']];
            } else {
                // All accessible users
                $user_filter = ilLocalUser::_getFolderIds();
            }
        }

        if ($user_filter === []) {
            $this->setMaxCount(0);
            $this->setData([]);
            return;
        }

        if (isset($this->filter['user_ids']) && is_array($this->filter['user_ids']) && $this->filter['user_ids'] === []) {
            $this->setMaxCount(0);
            $this->setData([]);
            return;
        }

        $additional_fields = $this->getSelectedColumns();
        unset(
            $additional_fields['firstname'],
            $additional_fields['lastname'],
            $additional_fields['email'],
            $additional_fields['second_email'],
            $additional_fields['last_login'],
            $additional_fields['access_until'],
            $additional_fields['org_units']
        );

        $udf_filter = [];
        foreach ($this->filter as $k => $v) {
            if (strpos($k, 'udf_') === 0) {
                $udf_filter[$k] = $v;
            }
        }

        $query = $this->buildUserQuery();
        $order_field = $this->getOrderField();
        if (strpos($order_field, 'udf_') !== 0 || isset($additional_fields[$order_field])) {
            $query->setOrderField($order_field);
            $query->setOrderDirection($this->getOrderDirection());
        }
        $query->setAdditionalFields($additional_fields);
        $query->setUserFolder($user_filter);
        $query->setUdfFilter($udf_filter);
        $usr_data = $query->query();

        if (count($usr_data['set']) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $query->setOffset($this->getOffset());
            $usr_data = $query->query();
        }

        foreach ($usr_data['set'] as $k => $user) {
            if (in_array('org_units', $this->getSelectedColumns())) {
                $usr_data['set'][$k]['org_units'] = ilObjUser::lookupOrgUnitsRepresentation($user['usr_id']);
            }


            $current_time = time();
            if ($user['active']) {
                if ($user['time_limit_unlimited']) {
                    $txt_access = $this->lng->txt('access_unlimited');
                    $usr_data['set'][$k]['access_class'] = 'smallgreen';
                } elseif ($user['time_limit_until'] < $current_time) {
                    $txt_access = $this->lng->txt('access_expired');
                    $usr_data['set'][$k]['access_class'] = 'smallred';
                } else {
                    $txt_access = ilDatePresentation::formatDate(new ilDateTime($user['time_limit_until'], IL_CAL_UNIX));
                    $usr_data['set'][$k]['access_class'] = 'small';
                }
            } else {
                $txt_access = $this->lng->txt('inactive');
                $usr_data['set'][$k]['access_class'] = 'smallred';
            }
            $usr_data['set'][$k]['access_until'] = $txt_access;
        }

        $this->setMaxCount($usr_data['cnt']);
        $this->setData($usr_data['set']);
    }

    public function addFilterItemValue($filter, $value): void // Missing parameter types.
    {
        $this->filter[$filter] = $value;
    }

    public function getUserIdsForFilter(): array // Missing array type.
    {
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // All accessible users
            $user_filter = ilLocalUser::_getFolderIds(true);
        } else {
            if ($this->filter['time_limit_owner']) {
                $user_filter = [$this->filter['time_limit_owner']];
            } else {
                // All accessible users
                $user_filter = ilLocalUser::_getFolderIds();
            }
        }

        if (!isset($this->filter['user_ids'])) {
            $this->filter['user_ids'] = null;
        }

        $query = $this->buildUserQuery();
        $query->setUserFolder($user_filter);
        if ($this->getOrderField()) {
            $query->setOrderField(ilUtil::stripSlashes($this->getOrderField()));
            $query->setOrderDirection(ilUtil::stripSlashes($this->getOrderDirection()));
        }
        $usr_data = $query->query();

        $user_ids = [];

        foreach ($usr_data['set'] as $item) {
            // #11632
            if ($item['usr_id'] != SYSTEM_USER_ID) {
                $user_ids[] = $item['usr_id'];
            }
        }
        return $user_ids;
    }

    public function initFilter(): void
    {
        // Show context filter
        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $parent_ids = ilLocalUser::_getFolderIds();

            if (count($parent_ids) > 1) {
                $co = new ilSelectInputGUI($this->lng->txt('context'), 'time_limit_owner');

                $ref_id = $this->getUserFolderId();

                $opt[0] = $this->lng->txt('all_users');
                $opt[$this->getUserFolderId()] = $this->lng->txt('users') . ' (' . ilObject::_lookupTitle(ilObject::_lookupObjId($this->getUserFolderId())) . ')';

                foreach ($parent_ids as $parent_id) {
                    if ($parent_id == $this->getUserFolderId()) {
                        continue;
                    }
                    switch ($parent_id) {
                        case USER_FOLDER_ID:
                            $opt[USER_FOLDER_ID] = $this->lng->txt('global_user');
                            break;

                        default:
                            $opt[$parent_id] = $this->lng->txt('users') . ' (' . ilObject::_lookupTitle(ilObject::_lookupObjId($parent_id)) . ')';
                            break;
                    }
                }
                $co->setOptions($opt);
                $this->addFilterItem($co);
                $co->readFromSession();
                $this->filter['time_limit_owner'] = $co->getValue();
            }
        }

        // User name, login, email filter
        $ul = new ilTextInputGUI($this->lng->txt('login') . '/' . $this->lng->txt('email') . '/' .
            $this->lng->txt('name'), 'query');
        $ul->setDataSource($this->ctrl->getLinkTarget(
            $this->getParentObject(),
            'addUserAutoComplete',
            '',
            true
        ));
        $ul->setSize(20);
        $ul->setSubmitFormOnEnter(true);
        $this->addFilterItem($ul);
        $ul->readFromSession();
        $this->filter['query'] = $ul->getValue();


        // activation
        $options = [
            '' => $this->lng->txt('user_all'),
            'active' => $this->lng->txt('active'),
            'inactive' => $this->lng->txt('inactive'),
            ];
        $si = new ilSelectInputGUI($this->lng->txt('user_activation'), 'activation');
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter['activation'] = $si->getValue();

        // limited access
        $cb = new ilCheckboxInputGUI($this->lng->txt('user_limited_access'), 'limited_access');
        $this->addFilterItem($cb);
        $cb->readFromSession();
        $this->filter['limited_access'] = $cb->getChecked();

        // last login
        $di = new ilDateTimeInputGUI($this->lng->txt('user_last_login_before'), 'last_login');
        $default_date = new ilDateTime(time(), IL_CAL_UNIX);
        $default_date->increment(IL_CAL_DAY, 1);
        $di->setDate($default_date);
        $this->addFilterItem($di);
        $di->readFromSession();
        $this->filter['last_login'] = $di->getDate();

        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // no assigned courses
            $cb = new ilCheckboxInputGUI($this->lng->txt('user_no_courses'), 'no_courses');
            $this->addFilterItem($cb);
            $cb->readFromSession();
            $this->filter['no_courses'] = $cb->getChecked();

            // no assigned groups
            $ng = new ilCheckboxInputGUI($this->lng->txt('user_no_groups'), 'no_groups');
            $this->addFilterItem($ng);
            $ng->readFromSession();
            $this->filter['no_groups'] = $ng->getChecked();

            // course/group members
            $rs = new ilRepositorySelectorInputGUI($this->lng->txt('user_member_of_course_group'), 'course_group');
            $rs->setSelectText($this->lng->txt('user_select_course_group'));
            $rs->setHeaderMessage($this->lng->txt('user_please_select_course_group'));
            $rs->setClickableTypes(['crs', 'grp']);
            $this->addFilterItem($rs);
            $rs->readFromSession();
            $this->filter['course_group'] = $rs->getValue();
        }

        // global roles
        $options = [
            '' => $this->lng->txt('user_any'),
        ];
        foreach ($this->rbac_review->getRolesByFilter(2, $this->current_user->getId()) as $role) {
            $options[$role['rol_id']] = $role['title'];
        }
        $si = new ilSelectInputGUI($this->lng->txt('user_global_role'), 'global_role');
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter['global_role'] = $si->getValue();

        // authentication mode
        $auth_methods = ilAuthUtils::_getActiveAuthModes();
        $options = [
            '' => $this->lng->txt('user_any'),
        ];
        foreach ($auth_methods as $method => $value) {
            if ($method == 'default') {
                $options[$method] = $this->lng->txt('auth_' . $method) . ' (' . $this->lng->txt('auth_' . ilAuthUtils::_getAuthModeName($value)) . ')';
            } else {
                $options[$method] = ilAuthUtils::getAuthModeTranslation((string) $value);
            }
        }
        $si = new ilSelectInputGUI($this->lng->txt('auth_mode'), 'authentication_method');
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter['authentication'] = $si->getValue();

        // udf fields
        foreach ($this->udf_fields as $f) {
            $this->addFilterItemByUdfType($f);
        }
    }

    /**
     * Add filter by standard type
     */
    private function addFilterItemByUdfType(
        ProfileField $field
    ): ?ilFormPropertyGUI {
        $id = "udf_{$field->getIdentifier()}";
        $item = $field->getLegacyInput($this->lng, $this->getFormContext());
        $item->setRequired(false);
        if (!($item instanceof ilTableFilterItem)) {
            $item = new ilTextInputGUI($field->getLabel($this->lng));
        }
        $item->setPostVar($id);
        $this->addFilterItem($item, true);
        $item->readFromSession();
        $this->filter[$id] = $item->getValue();
        return $item;
    }

    protected function fillRow(array $a_set): void // Missing array type.
    {
        $this->ctrl->setParameterByClass('ilobjusergui', 'letter', $this->user_request->getLetter());

        foreach ($this->getSelectedColumns() as $c) {
            if ($c == 'time_limit_until') {
                $this->tpl->setCurrentBlock('access_until');
                $this->tpl->setVariable('VAL_ACCESS_UNTIL', $a_set['access_until']);
                $this->tpl->setVariable('CLASS_ACCESS_UNTIL', $a_set['access_class']);
            } elseif ($c == 'last_login') {
                $this->tpl->setCurrentBlock('last_login');
                $this->tpl->setVariable(
                    'VAL_LAST_LOGIN',
                    ilDatePresentation::formatDate(new ilDateTime($a_set['last_login'], IL_CAL_DATETIME))
                );
            } elseif (in_array($c, ['firstname', 'lastname'])) {
                $this->tpl->setCurrentBlock($c);
                $this->tpl->setVariable('VAL_' . strtoupper($c), (string) $a_set[$c]);
            } elseif ($c == 'auth_mode') {
                $this->tpl->setCurrentBlock('user_field');
                $this->tpl->setVariable('VAL_UF', ilAuthUtils::getAuthModeTranslation((string) ilAuthUtils::_getAuthMode($a_set['auth_mode'])));
                $this->tpl->parseCurrentBlock();
            } else {	// all other fields
                $this->tpl->setCurrentBlock('user_field');
                $this->tpl->setVariable('VAL_UF', $this->buildUserFieldValue($c, $a_set[$c] ?? ''));
            }

            $this->tpl->parseCurrentBlock();
        }

        if ($a_set['usr_id'] != 6
            && ($this->getMode() == self::MODE_USER_FOLDER || $a_set['time_limit_owner'] == $this->getUserFolderId())) {
            $this->tpl->setCurrentBlock('checkb');
            $this->tpl->setVariable('ID', $a_set['usr_id']);
            $this->tpl->parseCurrentBlock();
        }

        if ($this->with_write_access
            && ($this->getMode() === self::MODE_USER_FOLDER
                || $a_set['time_limit_owner'] == $this->getUserFolderId())) {
            $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
            $this->ctrl->setParameterByClass('ilobjusergui', 'obj_id', $a_set['usr_id']);
            $this->tpl->setVariable(
                'HREF_LOGIN',
                $this->ctrl->getLinkTargetByClass('ilobjusergui', 'view')
            );
            $this->ctrl->setParameterByClass('ilobjusergui', 'obj_id', '');
        } else {
            $this->tpl->setVariable('VAL_LOGIN_PLAIN', $a_set['login']);
        }

        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $this->tpl->setCurrentBlock('context');
            $this->tpl->setVariable(
                'VAL_CONTEXT',
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_set['time_limit_owner']))
            );
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('roles');
            $this->ctrl->setParameter($this->getParentObject(), 'obj_id', $a_set['usr_id']);
            $this->tpl->setVariable('ROLE_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'assignRoles'));
            $this->tpl->setVariable('TXT_ROLES', $this->lng->txt('edit'));
            $this->ctrl->clearParameters($this->getParentObject());
            $this->tpl->parseCurrentBlock();
        }
    }

    private function buildUserFieldValue(string $key, array|string $value): string
    {
        switch ($key) {
            case 'birthday':
                return ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATE));

            case 'gender':
                return $value === '' ? '' : $this->lng->txt('gender_' . $value);

            case 'create_date':
            case 'agree_date':
            case 'approve_date':
                return ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATE));

            case 'dpro_agreed_on':
                return ilDatePresentation::formatDate(new ilDate($value, IL_CAL_UNIX));
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (trim($value) === '') {
            return ' ';
        }

        return $value;
    }

    private function getFormContext(): ProfileContext
    {
        if ($this->getMode() === self::MODE_USER_FOLDER) {
            return ProfileContext::UserAdministration;
        }
        return ProfileContext::LocalUserAdministration;
    }
}
