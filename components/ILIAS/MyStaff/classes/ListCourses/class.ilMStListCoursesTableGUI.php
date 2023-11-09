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
 ********************************************************************
 */
declare(strict_types=1);

use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStListCoursesTableGUI
 * @author Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_Calls      ilMStListCoursesTableGUI: ilFormPropertyDispatchGUI
 */
class ilMStListCoursesTableGUI extends ilTable2GUI
{
    protected array $filter = [];
    protected array $cached_selectable_columns = [];
    protected ?array $orgu_names = null;
    protected array $usr_orgu_names = [];
    protected ilMyStaffAccess $access;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;

    /**
     * @param ilMStListCoursesGUI $parent_obj
     * @param string              $parent_cmd
     */
    public function __construct(ilMStListCoursesGUI $parent_obj, $parent_cmd = ilMStListCoursesGUI::CMD_INDEX)
    {
        global $DIC;

        $this->access = ilMyStaffAccess::getInstance();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();

        $this->setPrefix('myst_lc');
        $this->setFormName('myst_lc');
        $this->setId('myst_lc');

        parent::__construct($parent_obj, $parent_cmd, '');

        $this->setRowTemplate('tpl.list_courses_row.html', "components/ILIAS/MyStaff");
        $this->setFormAction($DIC->ctrl()->getFormAction($parent_obj));
        $this->setDefaultOrderDirection('desc');

        $this->setShowRowsSelector(true);

        $this->setEnableTitle(true);
        $this->setDisableFilterHiding(true);
        $this->setEnableNumInfo(true);

        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));

        $this->setFilterCols(5);
        $this->initFilter();

        $this->addColumns();

        $this->parseData();
    }

    protected function parseData()
    {
        global $DIC;

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('crs_title');

        $this->determineLimit();
        $this->determineOffsetAndOrder();

        $options = array(
            'filters' => $this->filter,
            'limit' => array(
                'start' => $this->getOffset(),
                'end' => $this->getLimit(),
            ),
            'count' => true,
            'sort' => array(
                'field' => $this->getOrderField(),
                'direction' => $this->getOrderDirection(),
            ),
        );

        $arr_usr_id = $this->access->getUsersForUserOperationAndContext(
            $DIC->user()->getId(),
            ilMyStaffAccess::ACCESS_ENROLMENTS_ORG_UNIT_OPERATION,
            ilMyStaffAccess::COURSE_CONTEXT
        );

        $list_courses_fetcher = new \ILIAS\MyStaff\ListCourses\ilMStListCourses($DIC);
        $result = $list_courses_fetcher->getData($arr_usr_id, $options);
        $this->setMaxCount($result->getTotalDatasetCount());
        $data = $result->getDataset();

        // Workaround because the fillRow Method only accepts arrays
        $data = array_map(function (\ILIAS\MyStaff\ListCourses\ilMStListCourse $it): array {
            return [$it];
        }, $data);
        $this->setData($data);
    }

    final public function initFilter(): void
    {
        global $DIC;

        $item = new ilTextInputGUI($DIC->language()->txt("crs_title"), "crs_title");
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['crs_title'] = $item->getValue();

        // course members
        $item = new ilRepositorySelectorInputGUI($DIC->language()->txt("usr_filter_coursemember"), "course");
        //$item->setParent($this->getParentObject());
        $item->setSelectText($DIC->language()->txt("mst_select_course"));
        $item->setHeaderMessage($DIC->language()->txt("mst_please_select_course"));
        $item->setClickableTypes(array(ilMyStaffAccess::COURSE_CONTEXT));
        $this->addFilterItem($item);
        $item->readFromSession();
        //$item->setParent($this->getParentObject());
        $this->filter["course"] = $item->getValue();

        //membership status
        $item = new ilSelectInputGUI($DIC->language()->txt('member_status'), 'memb_status');
        $item->setOptions(array(
            "" => $DIC->language()->txt("mst_opt_all"),
            \ILIAS\MyStaff\ListCourses\ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED => $DIC->language()->txt('mst_memb_status_requested'),
            \ILIAS\MyStaff\ListCourses\ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST => $DIC->language()->txt('mst_memb_status_waitinglist'),
            \ILIAS\MyStaff\ListCourses\ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED => $DIC->language()->txt('mst_memb_status_registered'),
        ));
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter["memb_status"] = $item->getValue();

        if (ilObjUserTracking::_enabledLearningProgress() && $this->access->hasCurrentUserAccessToCourseLearningProgressForAtLeastOneUser()) {
            //learning progress status
            $item = new ilSelectInputGUI($DIC->language()->txt('learning_progress'), 'lp_status');
            //+1 because LP_STATUS_NOT_ATTEMPTED_NUM is 0.
            $item->setOptions(array(
                "" => $DIC->language()->txt("mst_opt_all"),
                ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM + 1 => $DIC->language()->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED),
                ilLPStatus::LP_STATUS_IN_PROGRESS_NUM + 1 => $DIC->language()->txt(ilLPStatus::LP_STATUS_IN_PROGRESS),
                ilLPStatus::LP_STATUS_COMPLETED_NUM + 1 => $DIC->language()->txt(ilLPStatus::LP_STATUS_COMPLETED),
                ilLPStatus::LP_STATUS_FAILED_NUM + 1 => $DIC->language()->txt(ilLPStatus::LP_STATUS_FAILED),
            ));
            $this->addFilterItem($item);
            $item->readFromSession();
            $this->filter["lp_status"] = $item->getValue();
            $this->filter["lp_status"] = (int) $this->filter["lp_status"] - 1;
        }



        //user
        $item = new ilTextInputGUI(
            $DIC->language()->txt("login") . "/" . $DIC->language()->txt("email") . "/" . $DIC->language()
                                                                                                                     ->txt("name"),
            "user"
        );

        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['user'] = $item->getValue();

        if (ilUserSearchOptions::_isEnabled('org_units')) {
            $paths = $this->getTextRepresentationOfOrgUnits();
            $options[0] = $DIC->language()->txt('mst_opt_all');
            foreach ($paths as $org_ref_id => $path) {
                $options[$org_ref_id] = $path;
            }
            $item = new ilSelectInputGUI($DIC->language()->txt('obj_orgu'), 'org_unit');
            $item->setOptions($options);
            $this->addFilterItem($item);
            $item->readFromSession();
            $this->filter['org_unit'] = $item->getValue();
        }
    }

    protected function getTextRepresentationOfOrgUnits(): array
    {
        if (isset($this->orgu_names)) {
            return $this->orgu_names;
        }

        return $this->orgu_names = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
    }

    protected function getTextRepresentationOfUsersOrgUnits(int $user_id): string
    {
        if (isset($this->usr_orgu_names[$user_id])) {
            return $this->usr_orgu_names[$user_id];
        }

        return $this->usr_orgu_names[$user_id] = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
    }

    final public function getSelectableColumns(): array
    {
        if ($this->cached_selectable_columns) {
            return $this->cached_selectable_columns;
        }

        return $this->cached_selectable_columns = $this->initSelectableColumns();
    }

    protected function initSelectableColumns(): array
    {
        global $DIC;

        $cols = array();

        $arr_searchable_user_columns = ilUserSearchOptions::getSelectableColumnInfo();

        $cols['crs_title'] = array(
            'txt' => $DIC->language()->txt('crs_title'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'crs_title',
        );
        $cols['usr_reg_status'] = array(
            'txt' => $DIC->language()->txt('member_status'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'reg_status',
        );
        if (ilObjUserTracking::_enabledLearningProgress() && $this->access->hasCurrentUserAccessToCourseLearningProgressForAtLeastOneUser()) {
            $cols['usr_lp_status'] = array(
                'txt' => $DIC->language()->txt('learning_progress'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'lp_status',
            );
        }

        if ($arr_searchable_user_columns['login'] ?? false) {
            $cols['usr_login'] = array(
                'txt' => $DIC->language()->txt('login'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'usr_login',
            );
        }
        if ($arr_searchable_user_columns['firstname'] ?? false) {
            $cols['usr_firstname'] = array(
                'txt' => $DIC->language()->txt('firstname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'usr_firstname',
            );
        }
        if ($arr_searchable_user_columns['lastname'] ?? false) {
            $cols['usr_lastname'] = array(
                'txt' => $DIC->language()->txt('lastname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'usr_lastname',
            );
        }

        if ($arr_searchable_user_columns['email'] ?? false) {
            $cols['usr_email'] = array(
                'txt' => $DIC->language()->txt('email'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'usr_email',
            );
        }

        if ($arr_searchable_user_columns['org_units'] ?? false) {
            $cols['usr_assinged_orgus'] = array(
                'txt' => $DIC->language()->txt('objs_orgu'),
                'default' => true,
                'width' => 'auto',
            );
        }

        return $cols;
    }

    private function addColumns(): void
    {
        global $DIC;

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                $sort = $v['sort_field'] ?? "";
                $this->addColumn($v['txt'], $sort);
            }
        }

        //Actions
        if (!$this->getExportMode()) {
            $this->addColumn($DIC->language()->txt('actions'));
        }
    }

    /**
     * @param array<\ILIAS\MyStaff\ListCourses\ilMStListCourse> $a_set
     * @return void
     * @throws \ilCtrlException
     * @throws \ilTemplateException
     */
    final protected function fillRow(array $a_set): void
    {
        global $DIC;

        $set = array_pop($a_set);

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $set, $set);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'usr_assinged_orgus':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable(
                            'VALUE',
                            $this->getTextRepresentationOfUsersOrgUnits($set->getUsrId())
                        );
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'usr_reg_status':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable(
                            'VALUE',
                            \ILIAS\MyStaff\ListCourses\ilMStListCourse::getMembershipStatusText($set->getUsrRegStatus())
                        );
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'usr_lp_status':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', ilMyStaffGUI::getUserLpStatusAsHtml($set));
                        $this->tpl->parseCurrentBlock();
                        break;
                    default:
                        if ($propGetter($k) !== null) {
                            $this->tpl->setCurrentBlock('td');
                            $this->tpl->setVariable(
                                'VALUE',
                                (is_array($propGetter($k)) ? implode(", ", $propGetter($k)) : $propGetter($k))
                            );
                            $this->tpl->parseCurrentBlock();
                        } else {
                            $this->tpl->setCurrentBlock('td');
                            $this->tpl->setVariable('VALUE', '&nbsp;');
                            $this->tpl->parseCurrentBlock();
                        }
                        break;
                }
            }
        }

        $mst_lco_usr_id = $set->getUsrId();
        $mst_lco_crs_ref_id = $set->getCrsRefId();

        $actions = [];

        if ($DIC->access()->checkAccess("visible", "", $mst_lco_crs_ref_id)) {
            $link = ilLink::_getStaticLink($mst_lco_crs_ref_id, ilMyStaffAccess::COURSE_CONTEXT);
            $actions[] = $this->ui_fac->link()->standard(
                ilObject2::_lookupTitle(ilObject2::_lookupObjectId($mst_lco_crs_ref_id)),
                $link
            );
        };

        foreach (array_unique(ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($mst_lco_usr_id)) as $orgu_id) {
            if ($DIC->access()->checkAccess("read", "", $orgu_id) && !ilObject::_isInTrash($orgu_id)) {
                $org_units = $this->getTextRepresentationOfOrgUnits();
                $link = ilLink::_getStaticLink($orgu_id, 'orgu');
                $actions[] = $this->ui_fac->link()->standard($org_units[$orgu_id], $link);
            }
        }

        $DIC->ctrl()->setParameterByClass(ilMStListCoursesGUI::class, 'mst_lco_usr_id', $mst_lco_usr_id);
        $DIC->ctrl()->setParameterByClass(ilMStListCoursesGUI::class, 'mst_lco_crs_ref_id', $mst_lco_crs_ref_id);

        $actions[] = \ilMyStaffGUI::extendActionMenuWithUserActions(
            $mst_lco_usr_id,
            rawurlencode($this->ctrl->getLinkTargetByClass(
                "ilMStListCoursesGUI",
                ilMStListCoursesGUI::CMD_INDEX
            ))
        );

        $dropdown = $this->ui_fac->dropdown()->standard($actions)->withLabel($this->lng->txt("actions"));
        $this->tpl->setVariable("ACTIONS", $this->ui_ren->render($dropdown));
        $this->tpl->parseCurrentBlock();
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        $set = array_pop($a_set);

        $col = 0;
        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $set = array_pop($a_set);

        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }

    protected function getFieldValuesForExport(\ILIAS\MyStaff\ListCourses\ilMStListCourse $my_staff_course): array
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $my_staff_course, $my_staff_course);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_assinged_orgus':
                    $field_values[$k] = $this->getTextRepresentationOfUsersOrgUnits($my_staff_course->getUsrId());
                    break;
                case 'usr_reg_status':
                    $field_values[$k] = \ILIAS\MyStaff\ListCourses\ilMStListCourse::getMembershipStatusText($my_staff_course->getUsrRegStatus());
                    break;
                case 'usr_lp_status':
                    $field_values[$k] = ilMyStaffGUI::getUserLpStatusAsText($my_staff_course);
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k) ?? "");
                    break;
            }
        }

        return $field_values;
    }
}
