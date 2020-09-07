<?php

/**
 * Class ilMStShowUserCoursesTableGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStShowUserCoursesTableGUI extends ilTable2GUI
{

    /**
     * @var int
     */
    protected $usr_id;
    /**
     * @var array
     */
    protected $filter = array();
    /**
     * @var ilMyStaffAccess
     */
    protected $access;


    /**
     * @param ilMStShowUserGUI $parent_obj
     * @param string           $parent_cmd
     */
    public function __construct(ilMStShowUserGUI $parent_obj, $parent_cmd = ilMStShowUserGUI::CMD_INDEX)
    {
        global $DIC;

        $this->access = ilMyStaffAccess::getInstance();

        $this->usr_id = $DIC->http()->request()->getQueryParams()['usr_id'];

        $this->setPrefix('myst_su');
        $this->setFormName('myst_su');
        $this->setId('myst_su');

        parent::__construct($parent_obj, $parent_cmd, '');
        $this->setRowTemplate('tpl.list_user_courses_row.html', "Services/MyStaff");
        $this->setFormAction($DIC->ctrl()->getFormAction($parent_obj));
        ;
        $this->setDefaultOrderDirection('desc');

        $this->setShowRowsSelector(true);

        $this->setEnableTitle(true);
        $this->setDisableFilterHiding(true);
        $this->setEnableNumInfo(true);

        $this->setExportFormats(array( self::EXPORT_EXCEL, self::EXPORT_CSV ));

        $this->setFilterCols(5);
        $this->initFilter();
        $this->addColumns();

        $this->parseData();
    }


    /**
     *
     */
    protected function parseData()
    {
        global $DIC;

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('crs_title');

        $this->determineLimit();
        $this->determineOffsetAndOrder();

        $arr_usr_id = $this->access->getUsersForUserOperationAndContext($DIC->user()
            ->getId(), ilOrgUnitOperation::OP_ACCESS_ENROLMENTS, ilMyStaffAccess::DEFAULT_CONTEXT);

        $this->filter['usr_id'] = $this->usr_id;
        $options = array(
            'filters' => $this->filter,
            'limit' => array(),
            'count' => true,
            'sort' => array(
                'field' => $this->getOrderField(),
                'direction' => $this->getOrderDirection(),
            ),
        );
        $count = ilMStShowUserCourses::getData($arr_usr_id, $options);
        $options['limit'] = array(
            'start' => intval($this->getOffset()),
            'end' => intval($this->getLimit()),
        );
        $options['count'] = false;
        $data = ilMStShowUserCourses::getData($arr_usr_id, $options);

        $this->setMaxCount($count);
        $this->setData($data);
    }


    /**
     *
     */
    public function initFilter()
    {
        global $DIC;

        $item = new ilTextInputGUI($DIC->language()->txt("crs_title"), "crs_title");
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['crs_title'] = $item->getValue();

        // course members
        $item = new ilRepositorySelectorInputGUI($DIC->language()->txt("usr_filter_coursemember"), "course");
        $item->setSelectText($DIC->language()->txt("mst_select_course"));
        $item->setHeaderMessage($DIC->language()->txt("mst_please_select_course"));
        $item->setClickableTypes(array( ilMyStaffAccess::DEFAULT_CONTEXT ));
        $this->addFilterItem($item);
        $item->readFromSession();
        $item->setParent($this->getParentObject());
        $this->filter["course"] = $item->getValue();

        //membership status
        $item = new ilSelectInputGUI($DIC->language()->txt('member_status'), 'memb_status');
        $item->setOptions(array(
            "" => $DIC->language()->txt("mst_opt_all"),
            ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED => $DIC->language()->txt('mst_memb_status_requested'),
            ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST => $DIC->language()->txt('mst_memb_status_waitinglist'),
            ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED => $DIC->language()->txt('mst_memb_status_registered'),
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
            if ($this->filter["lp_status"]) {
                $this->filter["lp_status"] = $this->filter["lp_status"] - 1;
            }
        }
    }


    /**
     * @return array
     */
    public function getSelectableColumns()
    {
        global $DIC;

        $cols = array();

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

        return $cols;
    }


    /**
     *
     */
    private function addColumns()
    {
        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                if (isset($v['sort_field'])) {
                    $sort = $v['sort_field'];
                } else {
                    $sort = null;
                }
                $this->addColumn($v['txt'], $sort, $v['width']);
            }
        }
    }


    /**
     * @param ilMStListCourse $my_staff_course
     */
    public function fillRow($my_staff_course)
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $my_staff_course, $my_staff_course);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'usr_reg_status':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', ilMStListCourse::getMembershipStatusText($my_staff_course->getUsrRegStatus()));
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'usr_lp_status':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', ilMyStaffGUI::getUserLpStatusAsHtml($my_staff_course));
                        $this->tpl->parseCurrentBlock();
                        break;
                    default:
                        if ($propGetter($k) !== null) {
                            $this->tpl->setCurrentBlock('td');
                            $this->tpl->setVariable('VALUE', (is_array($propGetter($k)) ? implode(", ", $propGetter($k)) : $propGetter($k)));
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
    }


    /**
     * @param ilExcel         $a_excel excel wrapper
     * @param int             $a_row
     * @param ilMStListCourse $my_staff_course
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $my_staff_course)
    {
        $col = 0;
        foreach ($this->getFieldValuesForExport($my_staff_course) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }


    /**
     * @param ilCSVWriter     $a_csv
     * @param ilMStListCourse $my_staff_course
     */
    protected function fillRowCSV($a_csv, $my_staff_course)
    {
        foreach ($this->getFieldValuesForExport($my_staff_course) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }


    /**
     * @param ilMStListCourse $my_staff_course
     */
    protected function getFieldValuesForExport(ilMStListCourse $my_staff_course)
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $my_staff_course, $my_staff_course);

        $field_values = array();

        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_reg_status':
                    $field_values[$k] = ilMStListCourse::getMembershipStatusText($my_staff_course->getUsrRegStatus());
                    break;
                case 'usr_lp_status':
                    $field_values[$k] = ilMyStaffGUI::getUserLpStatusAsText($my_staff_course);
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k));
                    break;
            }
        }

        return $field_values;
    }
}
