<?php

declare(strict_types=1);


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
 * Base class for attendance lists
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMembership
 */
class ilAttendanceList
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected object $parent_gui;
    protected ilObject $parent_obj;
    protected ?ilParticipants $participants;
    protected ?ilWaitingList $waiting_list;
    /**
     * @var ?callable
     */
    protected $callback;
    protected array $presets = [];
    protected array $role_data = [];
    protected array $roles = [];
    protected bool $has_local_role = false;
    protected array $blank_columns = [];
    protected string $title = '';
    protected string $description = '';
    protected array $pre_blanks = [];
    protected string $id = '';
    protected bool $include_waiting_list = false;
    protected bool $include_subscribers = false;
    protected array $user_filters = [];

    public function __construct(
        object $a_parent_gui,
        ilObject $a_parent_obj,
        ?ilParticipants $a_participants_object = null,
        ?ilWaitingList $a_waiting_list = null
    ) {
        global $DIC;

        $this->logger = $DIC->logger()->mmbr();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->parent_gui = $a_parent_gui;
        $this->parent_obj = $a_parent_obj;
        $this->participants = $a_participants_object;
        $this->waiting_list = $a_waiting_list;

        // always available
        $this->presets['name'] = array($DIC->language()->txt('name'), true);
        $this->presets['login'] = array($DIC->language()->txt('login'), true);

        // add exportable fields
        $this->readOrderedExportableFields();

        $DIC->language()->loadLanguageModule('crs');

        // roles
        $roles = $this->participants->getRoles();

        foreach ($roles as $role_id) {
            $title = ilObject::_lookupTitle($role_id);
            switch (substr($title, 0, 8)) {
                case 'il_crs_a':
                case 'il_grp_a':
                case 'il_lso_a':
                    $this->addRole($role_id, $DIC->language()->txt('event_tbl_admin'), 'admin');
                    break;

                case 'il_crs_t':
                    $this->addRole($role_id, $DIC->language()->txt('event_tbl_tutor'), 'tutor');
                    break;

                case 'il_crs_m':
                case 'il_grp_m':
                case 'il_sess_':
                case 'il_lso_m':
                    $this->addRole($role_id, $DIC->language()->txt('event_tbl_member'), 'member');
                    break;

                    // local
                default:
                    $this->has_local_role = true;
                    $this->addRole($role_id, $title, 'local');
                    break;
            }
        }
    }

    /**
     * read object export fields
     */
    protected function readOrderedExportableFields(): bool
    {
        $field_info = ilExportFieldsInfo::_getInstanceByType($this->parent_obj->getType());
        $field_info->sortExportFields();

        foreach ($field_info->getExportableFields() as $field) {
            switch ($field) {
                case 'username':
                case 'firstname':
                case 'lastname':
                    continue 2;
            }

            // Check if default enabled
            $this->presets[$field] = array(
                $GLOBALS['DIC']['lng']->txt($field),
                false
            );
        }

        // add udf fields
        $udf = ilUserDefinedFields::_getInstance();
        foreach ($udf->getExportableFields($this->parent_obj->getId()) as $field_id => $udf_data) {
            $this->presets['udf_' . $field_id] = array(
                $udf_data['field_name'],
                false
            );
        }

        // add cdf fields
        foreach (ilCourseDefinedFieldDefinition::_getFields($this->parent_obj->getId()) as $field_obj) {
            $this->presets['cdf_' . $field_obj->getId()] = array(
                $field_obj->getName(),
                false
            );
        }
        return true;
    }

    /**
     * Add user field
     */
    public function addPreset(string $a_id, string $a_caption, bool $a_selected = false): void
    {
        $this->presets[$a_id] = array($a_caption, $a_selected);
    }

    /**
     * Add blank column preset
     */
    public function addBlank(string $a_caption): void
    {
        $this->pre_blanks[] = $a_caption;
    }

    /**
     * Set titles
     */
    public function setTitle(string $a_title, ?string $a_description = null): void
    {
        $this->title = $a_title;
        $this->description = (string) $a_description;
    }

    /**
     * Add role
     */
    protected function addRole(int $a_id, string $a_caption, string $a_type): void
    {
        $this->role_data[$a_id] = array($a_caption, $a_type);
    }

    protected function setRoleSelection(array $a_role_ids): void
    {
        $this->roles = $a_role_ids;
    }

    /**
     * Add user filter
     */
    public function addUserFilter(string $a_id, string $a_caption, bool $a_checked = false): void
    {
        $this->user_filters[$a_id] = array($a_caption, $a_checked);
    }

    /**
     * Get user data for subscribers and waiting list
     */
    public function getNonMemberUserData(array &$a_res): void
    {
        $subscriber_ids = $this->participants->getSubscribers();
        $user_ids = $subscriber_ids;
        if ($this->waiting_list) {
            $user_ids = array_merge($user_ids, $this->waiting_list->getUserIds());
        }

        // Finally read user profile data
        $profile_data = ilObjUser::_readUsersProfileData($user_ids);
        foreach ($profile_data as $user_id => $fields) {
            foreach ((array) $fields as $field => $value) {
                $a_res[$user_id][$field] = $value;
            }
        }

        $udf = ilUserDefinedFields::_getInstance();

        foreach ($udf->getExportableFields($this->parent_obj->getId()) as $field_id => $udf_data) {
            foreach ($profile_data as $user_id => $field) {
                $udf_data = new ilUserDefinedData($user_id);
                $a_res[$user_id]['udf_' . $field_id] = $udf_data->get('f_' . $field_id);
            }
        }

        if (count($user_ids)) {
            // object specific user data
            $cdfs = ilCourseUserData::_getValuesByObjId($this->parent_obj->getId());
            foreach (array_unique($user_ids) as $user_id) {
                if ($tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                    $a_res[$user_id]['login'] = $tmp_obj->getLogin();
                    $a_res[$user_id]['name'] = $tmp_obj->getLastname() . ', ' . $tmp_obj->getFirstname();

                    if (in_array($user_id, $subscriber_ids)) {
                        $a_res[$user_id]['status'] = $this->lng->txt('crs_subscriber');
                    } else {
                        $a_res[$user_id]['status'] = $this->lng->txt('crs_waiting_list');
                    }

                    foreach ((array) $cdfs[$user_id] as $field_id => $value) {
                        $a_res[$user_id]['cdf_' . $field_id] = (string) $value;
                    }
                }
            }
        }
    }

    /**
     * Add blank columns
     */
    public function setBlankColumns(array $a_values): void
    {
        if (!implode("", $a_values)) {
            $a_values = array();
        } else {
            foreach ($a_values as $idx => $value) {
                $a_values[$idx] = trim($value);
                if ($a_values[$idx] == "") {
                    unset($a_values[$idx]);
                }
            }
        }
        $this->blank_columns = $a_values;
    }

    /**
     * Set participant detail callback
     */
    public function setCallback(callable $a_callback): void
    {
        $this->callback = $a_callback;
    }

    public function setId(string $a_value): void
    {
        $this->id = $a_value;
    }

    /**
     * Init form
     */
    public function initForm(string $a_cmd = ""): ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('crs');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this->parent_gui, $a_cmd));
        $form->setPreventDoubleSubmission(false);
        $form->setTitle($this->lng->txt('sess_gen_attendance_list'));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValue($this->title);
        $form->addItem($title);

        $desc = new ilTextInputGUI($this->lng->txt('description'), 'desc');
        $desc->setValue($this->description);
        $form->addItem($desc);

        if (count($this->presets)) {
            $preset = new ilCheckboxGroupInputGUI($this->lng->txt('user_detail'), 'preset');
            $preset_value = array();
            foreach ($this->presets as $id => $item) {
                $preset->addOption(new ilCheckboxOption($item[0], $id));
                if ($item[1]) {
                    $preset_value[] = $id;
                }
            }
            $preset->setValue($preset_value);
            $form->addItem($preset);
        }

        $blank = new ilTextInputGUI($this->lng->txt('event_blank_columns'), 'blank');
        $blank->setMulti(true);
        $form->addItem($blank);

        if ($this->pre_blanks) {
            $blank->setValue($this->pre_blanks);
        }

        $checked = array();

        $chk_grp = new ilCheckboxGroupInputGUI($this->lng->txt('event_user_selection'), 'selection_of_users');

        // participants by roles
        foreach ($this->role_data as $role_id => $role_data) {
            $title = ilObject::_lookupTitle($role_id);

            $role_name = $role_id;
            if (strpos($title, 'il_' . $this->parent_obj->getType() . '_adm') === 0) {
                $role_name = 'adm';
            }
            if (strpos($title, 'il_' . $this->parent_obj->getType() . '_mem') === 0) {
                $role_name = 'mem';
            }
            if (strpos($title, 'il_' . $this->parent_obj->getType() . '_tut') === 0) {
                $role_name = 'tut';
            }

            $chk = new ilCheckboxOption(
                sprintf($this->lng->txt('event_user_selection_include_role'), $role_data[0]),
                'role_' . $role_name
            );
            $checked[] = 'role_' . $role_name;
            $chk_grp->addOption($chk);
        }

        if ($this->waiting_list) {
            $chk = new ilCheckboxOption($this->lng->txt('event_user_selection_include_requests'), 'subscr');
            $chk_grp->addOption($chk);

            $chk = new ilCheckboxOption($this->lng->txt('event_user_selection_include_waiting_list'), 'wlist');
            $chk_grp->addOption($chk);
        }

        if ($this->user_filters) {
            foreach ($this->user_filters as $sub_id => $sub_item) {
                $chk = new ilCheckboxOption(
                    sprintf($this->lng->txt('event_user_selection_include_filter'), $sub_item[0]),
                    'members_' . $sub_id
                );
                if ($sub_item[1]) {
                    $checked[] = 'members_' . $sub_id;
                }
                $chk_grp->addOption($chk);
            }
        }
        $chk_grp->setValue($checked);
        $form->addItem($chk_grp);

        $form->addCommandButton($a_cmd, $this->lng->txt('sess_print_attendance_list'));

        if ($this->id && $a_cmd) {
            $settings = new ilUserFormSettings($this->id);
            if (!$settings->hasStoredEntry()) {
                $settings = new ilUserFormSettings($this->parent_obj->getType() . 's_pview', -1);
            }

            $settings->deleteValue('desc'); // #11340
            $settings->exportToForm($form);
        } elseif ($a_cmd === 'printForMembersOutput') {
            $settings = new ilUserFormSettings(
                $this->parent_obj->getType() . 's_pview_' . $this->parent_obj->getId(),
                -1
            );
            if (!$settings->hasStoredEntry()) {
                // init from global defaults
                $settings = new ilUserFormSettings($this->parent_obj->getType() . 's_pview', -1);
            }

            $settings->deleteValue('desc'); // #11340
            $settings->exportToForm($form, true);
        }
        return $form;
    }

    /**
     * Set list attributes from post values
     */
    public function initFromForm(): void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            foreach (array_keys($this->presets) as $id) {
                $this->presets[$id][1] = false;
            }
            foreach ((array) $form->getInput('preset') as $value) {
                if (isset($this->presets[$value])) {
                    $this->presets[$value][1] = true;
                } else {
                    $this->addPreset($value, $value, true);
                }
            }

            $this->setTitle($form->getInput('title'), $form->getInput('desc'));
            $this->setBlankColumns($form->getInput('blank'));

            $selection_of_users = (array) $form->getInput('selection_of_users'); // #18238

            $roles = array();
            foreach (array_keys($this->role_data) as $role_id) {
                $title = ilObject::_lookupTitle($role_id);
                $role_name = $role_id;
                if (strpos($title, 'il_' . $this->parent_obj->getType() . '_adm') === 0) {
                    $role_name = 'adm';
                }
                if (strpos($title, 'il_' . $this->parent_obj->getType() . '_mem') === 0) {
                    $role_name = 'mem';
                }
                if (strpos($title, 'il_' . $this->parent_obj->getType() . '_tut') === 0) {
                    $role_name = 'tut';
                }

                if (in_array('role_' . $role_name, $selection_of_users)) {
                    $roles[] = $role_id;
                }
            }
            $this->setRoleSelection($roles);

            // not in sessions
            if ($this->waiting_list) {
                $this->include_subscribers = in_array('subscr', $selection_of_users);
                $this->include_waiting_list = in_array('wlist', $selection_of_users);
            }

            if ($this->user_filters) {
                foreach (array_keys($this->user_filters) as $msub_id) {
                    $this->user_filters[$msub_id][2] = in_array("members_" . $msub_id, $selection_of_users);
                }
            }

            if ($this->id) {
                #$form->setValuesByPost();

                #$settings = new ilUserFormSettings($this->id);
                #$settings->deleteValue('desc'); // #11340
                #$settings->importFromForm($form);
                #$settings->store();
            }
        }
    }

    /**
     * render list in fullscreen mode
     */
    public function getFullscreenHTML(): void
    {
        $this->tpl->setContent($this->getHTML());
    }

    /**
     * render attendance list
     */
    public function getHTML(): string
    {
        $tpl = new ilTemplate('tpl.attendance_list_print.html', true, true, 'Services/Membership');
        ilDatePresentation::setUseRelativeDates(false);
        $time = ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX));

        $tpl->setVariable('TXT_TITLE', $this->title);
        if ($this->description) {
            $tpl->setVariable('TXT_DESCRIPTION', $this->description . " (" . $time . ")");
        } else {
            $tpl->setVariable('TXT_DESCRIPTION', $time);
        }

        $tpl->setCurrentBlock('head_item');
        foreach ($this->presets as $item) {
            if ($item[1]) {
                $tpl->setVariable('TXT_HEAD', $item[0]);
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->blank_columns) {
            foreach ($this->blank_columns as $blank) {
                $tpl->setVariable('TXT_HEAD', $blank);
                $tpl->parseCurrentBlock();
            }
        }

        // handle members

        $valid_user_ids = $filters = array();

        if ($this->roles) {
            if ($this->has_local_role) {
                $members = array();
                foreach ($this->participants->getMembers() as $member_id) {
                    foreach ($this->participants->getAssignedRoles($member_id) as $role_id) {
                        $members[$role_id][] = $member_id;
                    }
                }
            } else {
                $members = $this->participants->getMembers();
            }

            foreach ($this->roles as $role_id) {
                switch ($this->role_data[$role_id][1]) {
                    case "admin":
                        $valid_user_ids = array_merge($valid_user_ids, $this->participants->getAdmins());
                        break;

                    case "tutor":
                        $valid_user_ids = array_merge($valid_user_ids, $this->participants->getTutors());
                        break;

                        // member/local
                    default:
                        if (!$this->has_local_role) {
                            $valid_user_ids = array_merge($valid_user_ids, $members);
                        } else {
                            $valid_user_ids = array_merge($valid_user_ids, (array) $members[$role_id]);
                        }
                        break;
                }
            }
        }

        if ($this->include_subscribers) {
            $valid_user_ids = array_merge($valid_user_ids, $this->participants->getSubscribers());
        }

        if ($this->include_waiting_list) {
            $valid_user_ids = array_merge($valid_user_ids, $this->waiting_list->getUserIds());
        }

        if ($this->user_filters) {
            foreach ($this->user_filters as $sub_id => $sub_item) {
                $filters[$sub_id] = (bool) ($sub_item[2] ?? false);
            }
        }

        $valid_user_ids = ilUtil::_sortIds(array_unique($valid_user_ids), 'usr_data', 'lastname', 'usr_id');
        foreach ($valid_user_ids as $user_id) {
            if ($this->callback) {
                $user_data = call_user_func_array($this->callback, [(int) $user_id, $filters]);
                if (!$user_data) {
                    continue;
                }

                $tpl->setCurrentBlock("row_preset");
                foreach ($this->presets as $id => $item) {
                    if ($item[1]) {
                        switch ($id) {
                            case 'org_units':
                                $value = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
                                break;

                            case "name":
                                if (!($user_data[$id] ?? null)) {
                                    $name = ilObjUser::_lookupName((int) $user_id);
                                    $value = $name["lastname"] . ", " . $name["firstname"];
                                    break;
                                }
                                // no break
                            case "login":
                                if (!($user_data[$id] ?? false)) {
                                    $value = ilObjUser::_lookupLogin((int) $user_id);
                                    break;
                                }

                                // no break
                            default:
                                $value = (string) ($user_data[$id] ?? '');
                                break;
                        }
                        $tpl->setVariable("TXT_PRESET", $value);
                        $tpl->parseCurrentBlock();
                    }
                }
            }

            if ($this->blank_columns) {
                for ($loop = 0, $loopMax = count($this->blank_columns); $loop < $loopMax; $loop++) {
                    $tpl->touchBlock('row_blank');
                }
            }

            $tpl->touchBlock("member_row");
        }
        return $tpl->get();
    }
}
