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
 * Class for generation of member export files
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup Modules/Course
 */
class ilMemberExport
{
    public const EXPORT_CSV = 1;
    public const EXPORT_EXCEL = 2;

    private int $ref_id;
    private int $obj_id;
    private string $type;
    private int $export_type;
    private ilParticipants $members;
    private array $groups = [];
    private array $groups_participants = [];
    private array $groups_rights = [];
    private ?string $filename = null;

    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilAccessHandler $access;
    protected array $agreement = [];

    protected ilUserFormSettings $settings;
    protected ilPrivacySettings $privacy;
    protected ?ilCSVWriter $csv = null;
    protected ?ilExcel $worksheet = null;

    private array $user_ids = array();
    private array $user_course_data = array();
    private array $user_course_fields = array();
    private array $user_profile_data = array();

    public function __construct(int $a_ref_id, int $a_type = self::EXPORT_CSV)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();

        $this->export_type = $a_type;
        $this->ref_id = $a_ref_id;
        $this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);

        $this->initMembers();
        $this->initGroups();

        $this->agreement = ilMemberAgreement::_readByObjId($this->obj_id);
        $this->settings = new ilUserFormSettings('memexp');
        $this->privacy = ilPrivacySettings::getInstance();
    }

    /**
     * @param int[] $a_usr_ids
     * @return int[]
     */
    public function filterUsers(array $a_usr_ids): array
    {
        return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            'manage_members',
            $this->ref_id,
            $a_usr_ids
        );
    }

    public function setFilename(string $a_file): void
    {
        $this->filename = $a_file;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExportType(): int
    {
        return $this->export_type;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function create(): void
    {
        $this->fetchUsers();
        switch ($this->getExportType()) {
            case self::EXPORT_CSV:
                $this->createCSV();
                break;

            case self::EXPORT_EXCEL:
                $this->createExcel();
                break;
        }
    }

    public function getCSVString(): ?string
    {
        if ($this->csv instanceof ilCSVWriter) {
            return $this->csv->getCSVString();
        }
        return null;
    }

    public function createExcel(): void
    {
        $this->worksheet = new ilExcel();
        $this->worksheet->addSheet($this->lng->txt("members"));
        $this->write();

        $this->worksheet->writeToFile($this->getFilename());
    }

    public function createCSV(): void
    {
        $this->csv = new ilCSVWriter();
        $this->write();
    }

    /**
     * Write one column
     */
    protected function addCol(string $a_value, int $a_row, int $a_col): void
    {
        switch ($this->getExportType()) {
            case self::EXPORT_CSV:
                $this->csv->addColumn($a_value);
                break;

            case self::EXPORT_EXCEL:
                $this->worksheet->setCell($a_row + 1, $a_col, $a_value);
                break;
        }
    }

    protected function addRow(): void
    {
        switch ($this->getExportType()) {
            case self::EXPORT_CSV:
                $this->csv->addRow();
                break;

            case self::EXPORT_EXCEL:
                break;
        }
    }

    protected function getOrderedExportableFields(): array
    {
        $field_info = ilExportFieldsInfo::_getInstanceByType(ilObject::_lookupType($this->obj_id));
        $field_info->sortExportFields();
        $fields[] = 'role';
        // Append agreement info
        $privacy = ilPrivacySettings::getInstance();
        if ($privacy->courseConfirmationRequired()) {
            $fields[] = 'agreement';
        }

        foreach ($field_info->getExportableFields() as $field) {
            if ($this->settings->enabled($field)) {
                $fields[] = $field;
            }
        }

        $udf = ilUserDefinedFields::_getInstance();
        foreach ($udf->getCourseExportableFields() as $field_id => $udf_data) {
            if ($this->settings->enabled('udf_' . $field_id)) {
                $fields[] = 'udf_' . $field_id;
            }
        }

        // Add course specific fields
        foreach (ilCourseDefinedFieldDefinition::_getFields($this->obj_id) as $field_obj) {
            if ($this->settings->enabled('cdf_' . $field_obj->getId())) {
                $fields[] = 'cdf_' . $field_obj->getId();
            }
        }
        if ($this->settings->enabled('group_memberships')) {
            $fields[] = 'crs_members_groups';
        }
        return $fields;
    }

    protected function write(): void
    {
        // Add header line
        $row = 0;
        $col = 0;
        foreach ($all_fields = $this->getOrderedExportableFields() as $field) {
            switch ($field) {
                case 'role':
                    $this->addCol($this->lng->txt($this->getType() . '_role_status'), $row, $col++);
                    break;
                case 'agreement':
                    $this->addCol($this->lng->txt('ps_agreement_accepted'), $row, $col++);
                    break;
                case 'consultation_hour':
                    $this->lng->loadLanguageModule('dateplaner');
                    $this->addCol($this->lng->txt('cal_ch_field_ch'), $row, $col++);
                    break;

                case 'org_units':
                    $this->addCol($this->lng->txt('org_units'), $row, $col++);
                    break;

                default:
                    if (strpos($field, 'udf_') === 0) {
                        $field_id = explode('_', $field);
                        $udf = ilUserDefinedFields::_getInstance();
                        $def = $udf->getDefinition((int) $field_id[1]);
                        #$this->csv->addColumn($def['field_name']);
                        $this->addCol($def['field_name'], $row, $col++);
                    } elseif (strpos($field, 'cdf_') === 0) {
                        $field_id = explode('_', $field);
                        #$this->csv->addColumn(ilCourseDefinedFieldDefinition::_lookupName($field_id[1]));
                        $this->addCol(ilCourseDefinedFieldDefinition::_lookupName((int) $field_id[1]), $row, $col++);
                    } elseif ($field === "username") {//User Name Presentation Guideline; username should be named login
                        $this->addCol($this->lng->txt("login"), $row, $col++);
                    } else {
                        #$this->csv->addColumn($this->lng->txt($field));
                        $this->addCol($this->lng->txt($field), $row, $col++);
                    }
                    break;
            }
        }
        $this->addRow();
        // Add user data
        foreach ($this->user_ids as $usr_id) {
            $row++;
            $col = 0;
            $usr_id = (int) $usr_id;

            $udf_data = new ilUserDefinedData($usr_id);
            foreach ($all_fields as $field) {
                // Handle course defined fields
                if ($this->addUserDefinedField($udf_data, $field, $row, $col)) {
                    $col++;
                    continue;
                }

                if ($this->addCourseField($usr_id, $field, $row, $col)) {
                    $col++;
                    continue;
                }

                switch ($field) {
                    case 'role':
                        switch ($this->user_course_data[$usr_id]['role']) {
                            case ilParticipants::IL_CRS_ADMIN:
                                $this->addCol($this->lng->txt('crs_admin'), $row, $col++);
                                break;

                            case ilParticipants::IL_CRS_TUTOR:
                                $this->addCol($this->lng->txt('crs_tutor'), $row, $col++);
                                break;

                            case ilParticipants::IL_CRS_MEMBER:
                                $this->addCol($this->lng->txt('crs_member'), $row, $col++);
                                break;

                            case ilParticipants::IL_GRP_ADMIN:
                                $this->addCol($this->lng->txt('il_grp_admin'), $row, $col++);
                                break;

                            case ilParticipants::IL_GRP_MEMBER:
                                $this->addCol($this->lng->txt('il_grp_member'), $row, $col++);
                                break;

                            case 'subscriber':
                                $this->addCol($this->lng->txt($this->getType() . '_subscriber'), $row, $col++);
                                break;

                            default:
                                $this->addCol($this->lng->txt('crs_waiting_list'), $row, $col++);
                                break;

                        }
                        break;

                    case 'agreement':
                        if (isset($this->agreement[$usr_id])) {
                            if ($this->agreement[$usr_id]['accepted']) {
                                $dt = new ilDateTime($this->agreement[$usr_id]['acceptance_time'], IL_CAL_UNIX);
                                $this->addCol($dt->get(IL_CAL_DATETIME), $row, $col++);
                            } else {
                                $this->addCol($this->lng->txt('ps_not_accepted'), $row, $col++);
                            }
                        } else {
                            $this->addCol($this->lng->txt('ps_not_accepted'), $row, $col++);
                        }
                        break;

                    // These fields are always enabled
                    case 'username':
                        $this->addCol($this->user_profile_data[$usr_id]['login'], $row, $col++);
                        break;

                    case 'firstname':
                    case 'lastname':
                        $this->addCol($this->user_profile_data[$usr_id][$field], $row, $col++);
                        break;

                    case 'consultation_hour':
                        $bookings = ilBookingEntry::lookupManagedBookingsForObject(
                            $this->obj_id,
                            $GLOBALS['DIC']['ilUser']->getId()
                        );

                        $uts = array();
                        foreach ((array) $bookings[$usr_id] as $ut) {
                            ilDatePresentation::setUseRelativeDates(false);
                            $tmp = ilDatePresentation::formatPeriod(
                                new ilDateTime($ut['dt'], IL_CAL_UNIX),
                                new ilDateTime($ut['dtend'], IL_CAL_UNIX)
                            );
                            if (strlen($ut['explanation'])) {
                                $tmp .= ' ' . $ut['explanation'];
                            }
                            $uts[] = $tmp;
                        }
                        $uts_str = implode(',', $uts);
                        $this->addCol($uts_str, $row, $col++);
                        break;
                    case 'crs_members_groups':
                        $groups = array();

                        foreach (array_keys($this->groups) as $grp_ref) {
                            if (in_array($usr_id, $this->groups_participants[$grp_ref])
                                && $this->groups_rights[$grp_ref]) {
                                $groups[] = $this->groups[$grp_ref];
                            }
                        }
                        $this->addCol(implode(", ", $groups), $row, $col++);
                        break;

                    case 'org_units':
                        $this->addCol(ilObjUser::lookupOrgUnitsRepresentation($usr_id), $row, $col++);
                        break;

                    default:
                        // Check aggreement
                        if (!$this->privacy->courseConfirmationRequired() or $this->agreement[$usr_id]['accepted']) {
                            #$this->csv->addColumn($this->user_profile_data[$usr_id][$field]);
                            $this->addCol($this->user_profile_data[$usr_id][$field], $row, $col++);
                        } else {
                            #$this->csv->addColumn('');
                            $this->addCol('', $row, $col++);
                        }
                        break;

                }
            }
            $this->addRow();
        }
    }

    private function fetchUsers(): void
    {
        $this->readCourseSpecificFieldsData();

        if ($this->settings->enabled('admin')) {
            $this->user_ids = $tmp_ids = $this->members->getAdmins();
            $this->readCourseData($tmp_ids);
        }
        if ($this->settings->enabled('tutor')) {
            $this->user_ids = array_merge($tmp_ids = $this->members->getTutors(), $this->user_ids);
            $this->readCourseData($tmp_ids);
        }
        if ($this->settings->enabled('member')) {
            $this->user_ids = array_merge($tmp_ids = $this->members->getMembers(), $this->user_ids);
            $this->readCourseData($tmp_ids);
        }
        if ($this->settings->enabled('subscribers')) {
            $this->user_ids = array_merge($tmp_ids = $this->members->getSubscribers(), $this->user_ids);
            $this->readCourseData($tmp_ids);
        }
        if ($this->settings->enabled('waiting_list')) {
            $waiting_list = new ilCourseWaitingList($this->obj_id);
            $this->user_ids = array_merge($waiting_list->getUserIds(), $this->user_ids);
        }
        $this->user_ids = $this->filterUsers($this->user_ids);

        // Sort by lastname
        $this->user_ids = ilUtil::_sortIds($this->user_ids, 'usr_data', 'lastname', 'usr_id');

        // Finally read user profile data
        $this->user_profile_data = ilObjUser::_readUsersProfileData($this->user_ids);
    }

    /**
     * Read All User related course data
     * @param int[]
     * @param string
     */
    private function readCourseData(array $a_user_ids): void
    {
        foreach ($a_user_ids as $user_id) {
            // Read course related data
            if ($this->members->isAdmin($user_id)) {
                $this->user_course_data[$user_id]['role'] = $this->getType() === 'crs' ? ilParticipants::IL_CRS_ADMIN : ilParticipants::IL_GRP_ADMIN;
            } elseif ($this->members->isTutor($user_id)) {
                $this->user_course_data[$user_id]['role'] = ilParticipants::IL_CRS_TUTOR;
            } elseif ($this->members->isMember($user_id)) {
                $this->user_course_data[$user_id]['role'] = $this->getType() === 'crs' ? ilParticipants::IL_CRS_MEMBER : ilParticipants::IL_GRP_MEMBER;
            } else {
                $this->user_course_data[$user_id]['role'] = 'subscriber';
            }
        }
    }

    private function readCourseSpecificFieldsData(): void
    {
        $this->user_course_fields = ilCourseUserData::_getValuesByObjId($this->obj_id);
    }

    /**
     * Fill course specific fields
     */
    private function addCourseField(int $a_usr_id, string $a_field, int $row, int $col): bool
    {
        if (strpos($a_field, 'cdf_') !== 0) {
            return false;
        }
        if (!$this->privacy->courseConfirmationRequired() or $this->agreement[$a_usr_id]['accepted']) {
            $field_info = explode('_', $a_field);
            $field_id = $field_info[1] ?? 0;
            $value = '';
            if (isset($this->user_course_fields[$a_usr_id][$a_field])) {
                $value = $this->user_course_fields[$a_usr_id][$field_id];
            }
            $this->addCol((string) $value, $row, $col);
            return true;
        }
        #$this->csv->addColumn('');
        $this->addCol('', $row, $col);
        return true;
    }

    /**
     * Add user defined fields
     */
    private function addUserDefinedField(ilUserDefinedData $udf_data, string $a_field, int $row, int $col): bool
    {
        if (strpos($a_field, 'udf_') !== 0) {
            return false;
        }

        if (
            !$this->privacy->courseConfirmationRequired() ||
            $this->agreement[$udf_data->getUserId()]['accepted']
        ) {
            $field_info = explode('_', $a_field);
            $field_id = $field_info[1];
            $value = $udf_data->get('f_' . $field_id);
            #$this->csv->addColumn($value);
            $this->addCol($value, $row, $col);
            return true;
        }

        $this->addCol('', $row, $col);
        return true;
    }

    /**
     * Init member object
     */
    protected function initMembers(): void
    {
        if ($this->getType() === 'crs') {
            $this->members = ilCourseParticipants::_getInstanceByObjId($this->getObjId());
        }
        if ($this->getType() === 'grp') {
            $this->members = ilGroupParticipants::_getInstanceByObjId($this->getObjId());
        }
    }

    protected function initGroups(): void
    {
        $parent_node = $this->tree->getNodeData($this->ref_id);
        $groups = $this->tree->getSubTree($parent_node, true, ['grp']);
        if (is_array($groups) && count($groups)) {
            $this->groups_rights = [];
            foreach ($groups as $idx => $group_data) {
                // check for group in group
                if (
                    $group_data["parent"] != $this->ref_id &&
                    $this->tree->checkForParentType($group_data["ref_id"], "grp", true)
                ) {
                    unset($groups[$idx]);
                } else {
                    $this->groups[$group_data["ref_id"]] = $group_data["title"];
                    //TODO: change permissions from write to manage_members plus "|| ilObjGroup->getShowMembers()"----- uncomment below; testing required
                    $this->groups_rights[$group_data["ref_id"]] =
                        $this->access->checkAccess("write", "", $group_data["ref_id"]);
                    $gobj = ilGroupParticipants::_getInstanceByObjId($group_data["obj_id"]);
                    $this->groups_participants[$group_data["ref_id"]] = $gobj->getParticipants();
                }
            }
        }
    }
}
