<?php
require_once "./Services/Table/classes/class.ilTable2GUI.php";
require_once "./Services/Form/classes/class.ilTextInputGUI.php";
require_once "./Services/Form/classes/class.ilSelectInputGUI.php";
require_once "class.ilMStShowUserCourses.php";
require_once "class.ilMStShowUser.php";

//require_once("./Services/Container/classes/class.ilContainerObjectiveGUI.php");

/**
 * Class ilMStShowUserCoursesTableGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMStShowUserCoursesTableGUI extends ilTable2GUI {

    /**
     * @var ilCtrl $ctrl
     */
    protected $ctrl;
    /** @var  array $filter */
    protected $filter = array();
    protected $access;

    protected $ignored_cols;

    protected $custom_export_formats = array();
    protected $custom_export_generators = array();

    /** @var array */
    protected $numeric_fields = array("course_id");

    protected $usr_id;

    /**
     * @param ilMStListUsersGUI $parent_obj
     * @param string $parent_cmd
     */
    public function __construct($parent_obj, $parent_cmd = "index") {
        /** @var $ilCtrl ilCtrl */
        /** @var ilToolbarGUI $ilToolbar */
        global $ilCtrl, $ilToolbar, $tpl, $lng, $ilUser;

        $this->ctrl = $ilCtrl;
        $this->access = ilMyStaffAcess::getInstance();

        $this->lng = $lng;
        $this->toolbar = $ilToolbar;

        $this->usr_id = $_GET['usr_id'];
        $this->ctrl->setParameter($parent_obj, 'usr_id', $this->usr_id);


        $this->setPrefix('myst_su');
        $this->setFormName('myst_su');
        $this->setId('myst_su');

        parent::__construct($parent_obj, $parent_cmd, '');
        //$this->addMultiCommand('multiUserAccreditation', $this->pl->txt('accr_create_courses'));
        $this->setRowTemplate('tpl.default_row.html',"Services/MyStaff");
        //$this->setFormAction($this->ctrl->getFormAction($parent_obj));
        //$this->setDefaultOrderField('Datetime');
        $this->setDefaultOrderDirection('desc');

        $this->setShowRowsSelector(true);

        $this->setEnableTitle(true);
        $this->setDisableFilterHiding(true);
        $this->setEnableNumInfo(true);

        $this->setIgnoredCols(array());
        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));

        $this->setFilterCols(4);
        $this->initFilter();
        $this->addColumns();

        $this->parseData();
    }


    protected function parseData() {
        global $ilUser;
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('crs_title');

        $this->determineLimit();
        $this->determineOffsetAndOrder();


        //Permission Filter
        $arr_usr_id = $this->access->getOrguUsersOfCurrentUserWithShowStaffPermission();

        $this->filter['usr_id'] = $this->usr_id;
        $options = array(
            'filters' => $this->filter,
            'limit' => array(),
            'count' => true,
            'sort' => array('field' => $this->getOrderField(), 'direction' => $this->getOrderDirection())
        );
        $count = ilMStShowUserCourses::getData($arr_usr_id,$options);
        $options['limit'] = array('start' => (int)$this->getOffset(), 'end' => (int)$this->getLimit());
        $options['count'] = false;
        $data = ilMStShowUserCourses::getData($arr_usr_id,$options);

        $this->setMaxCount($count);
        $this->setData($data);
    }


    public function initFilter() {

        //User
        $item = new ilTextInputGUI($this->lng->txt('usr'), 'user');
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['usr_lastname'] = $item->getValue();

        //OrganisationalUnit
        /*
		$item = new ilSelectInputGUI($this->pl->txt('org_unit'), 'org_unit');
		$opts = ilObjOrgUnit::get;
		$opts[0] = '';
		asort($opts);
		$item->setOptions($opts);
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['org_unit'] = $item->getValue();

		// Rekursiv
		$item = new ilCheckboxInputGUI($this->pl->txt('recursive'), 'org_unit_recursive');
		$item->setValue(1);
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['org_unit_recursive'] = $item->getChecked();*/
    }


    /**
     * @return array
     */
    public function getSelectableColumns() {
        $cols = array();

        $cols['crs_title'] = array('txt' => $this->lng->txt('crs_title'), 'default' => true, 'width' => 'auto','sort_field' => 'crs_title');
        $cols['usr_reg_status'] = array('txt' => $this->lng->txt('usr_reg_status'), 'default' => true, 'width' => 'auto','sort_field' => 'reg_status');
        $cols['usr_lp_status'] = array('txt' => $this->lng->txt('usr_lp_status'), 'default' => true, 'width' => 'auto','sort_field' => 'lp_status');

        return $cols;
    }


    private function addColumns() {
        $this->setSelectAllCheckbox("user_ids[]");
        $this->addColumn('', '', '1', true);
        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                if (isset($v['sort_field'])) {
                    $sort = $v['sort_field'];
                } else {
                    $sort = NULL;
                }
                $this->addColumn($v['txt'], $sort, $v['width']);
            }
        }

    }


    /**
     * @param ilMyStaffUser $my_staff_user
     */
    public function fillRow($my_staff_user) {

        $propGetter = Closure::bind(  function($prop){return $this->$prop;}, $my_staff_user, $my_staff_user );


        $this->tpl->setCurrentBlock('record_id');
        $this->tpl->setVariable('RECORD_ID',  '');
        $this->tpl->parseCurrentBlock();

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                if ($propGetter($k) !== NULL) {
                    switch($k) {
                        case 'login':
                            $this->ctrl->setParameterByClass('ilObjUserGUI','ref_id', $propGetter($k));
                            $this->ctrl->setParameterByClass('ilObjUserGUI','obj_id', $propGetter($k));
                            $this->ctrl->setParameterByClass('ilObjUserGUI','admin_mode','settings');
                            $link = $this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI','ilLocalUserAdminGUI','srLocalUserGUI','ilObjUserGUI'),'view');
                            $this->tpl->setCurrentBlock('td');
                            $this->tpl->setVariable('VALUE', '<a href="'.$link.'">'. $propGetter($k).'</a>');
                            $this->tpl->parseCurrentBlock();
                            break;
                        case 'active_as_string':
                            $this->tpl->setCurrentBlock('td');
                            if( $propGetter('active')) {
                                $this->tpl->setVariable('VALUE',  $propGetter($k));
                            } else {
                                $this->tpl->setVariable('VALUE', '<span class="warning">'.$propGetter($k).'</span>');
                            }
                            $this->tpl->parseCurrentBlock();
                            break;
                        default:
                            $this->tpl->setCurrentBlock('td');
                            $this->tpl->setVariable('VALUE', (is_array($propGetter($k)) ? implode(", ", $propGetter($k)) :$propGetter($k)));
                            $this->tpl->parseCurrentBlock();
                            break;
                    }
                } else {
                    $this->tpl->setCurrentBlock('td');
                    $this->tpl->setVariable('VALUE', '&nbsp;');
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
        /*
                $selection = new ilAdvancedSelectionListGUI();
                $selection->setListTitle($this->pl->txt('actions'));
                $selection->setId('selection_list_' . $a_set['user_id']);
                $this->ctrl->setParameterByClass('srLocalUserGUI', 'user_id', $a_set['user_id']);
                $this->tpl->setVariable('ACTIONS', $selection->getHTML());
        */
    }



    /**
     * @param ilExcel $a_excel	excel wrapper
     * @param int    $a_row
     * @param ilMyStaffUser $my_staff_user
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $my_staff_user) {
        $col = 0;

        $propGetter = Closure::bind(  function($prop){return $this->$prop;}, $my_staff_user, $my_staff_user);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                $a_excel->setCell($a_row, $col, strip_tags($propGetter($k)));
                $col ++;
            }
        }
    }

    /**
     * @param object $a_csv
     * @param ilMyStaffUser $my_staff_user
     */
    protected function fillRowCSV($a_csv, $my_staff_user) {

        $propGetter = Closure::bind(  function($prop){return $this->$prop;}, $my_staff_user, $my_staff_user);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if (!in_array($k, $this->getIgnoredCols()) && $this->isColumnSelected($k)) {
                $a_csv->addColumn(strip_tags($propGetter($k)));
            }
        }
        $a_csv->addRow();
    }

    /**
     * @return bool
     */
    public function numericOrdering($sort_field) {
        return in_array($sort_field, array());
    }


    /**
     * @param array $ignored_cols
     */
    public function setIgnoredCols($ignored_cols) {
        $this->ignored_cols = $ignored_cols;
    }


    /**
     * @return array
     */
    public function getIgnoredCols() {
        return $this->ignored_cols;
    }
}
?>