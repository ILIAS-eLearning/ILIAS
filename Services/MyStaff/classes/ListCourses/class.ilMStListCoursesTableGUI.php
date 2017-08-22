<?php
require_once "./Services/Table/classes/class.ilTable2GUI.php";
require_once "./Services/Form/classes/class.ilTextInputGUI.php";
require_once "./Services/Form/classes/class.ilSelectInputGUI.php";
require_once "class.ilMStListCourses.php";
require_once "class.ilMStListCourse.php";

//require_once("./Services/Container/classes/class.ilContainerObjectiveGUI.php");

/**
 * Class ilMStListCoursesTableGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version 1.0.0
 *
 */
class ilMStListCoursesTableGUI extends ilTable2GUI {

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



	/**
	 * @param ilMStListCoursesGUI $parent_obj
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
        

		$this->setPrefix('myst_lc');
		$this->setFormName('myst_lc');
		$this->setId('myst_lc');

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

		$options = array(
			'filters' => $this->filter,
			'limit' => array(),
			'count' => true,
			'sort' =>  array('field' => $this->getOrderField(),'direction' => $this->getOrderDirection()),
		);
		$count = ilMStListCourses::getData($arr_usr_id,$options);
		$options['limit'] = array('start' => (int)$this->getOffset(), 'end' => (int)$this->getLimit());
		$options['count'] = false;
		$data = ilMStListCourses::getData($arr_usr_id,$options);

		$this->setMaxCount($count);
		$this->setData($data);
	}


	public function initFilter() {

        $item = new ilTextInputGUI($this->lng->txt("crs_title"), "crs_title");
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['crs_title'] = $item->getValue();

        // course members
        include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
        $item = new ilRepositorySelectorInputGUI($this->lng->txt("user_member_of_course_group"), "course");
        $item->setParent($this->getParentObject());
        $item->setSelectText($this->lng->txt("user_select_course_group"));
        $item->setHeaderMessage($this->lng->txt("user_please_select_course_group"));
        $item->setClickableTypes(array("crs"));
        $this->addFilterItem($item);
        $item->readFromSession();
        $item->setParent($this->getParentObject());
        $this->filter["course"] = $item->getValue();

        $item = new ilTextInputGUI($this->lng->txt("login")."/".$this->lng->txt("email")."/".$this->lng->txt("name"), "user");
        //$item->setDataSource($this->ctrl->getLinkTarget($this->getParentObject(),"addUserAutoComplete", "", true));
        //$item->setSize(20);
        //$item->setSubmitFormOnEnter(true);
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['user'] = $item->getValue();



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
        $cols['usr_login'] = array('txt' => $this->lng->txt('usr_login'), 'default' => true, 'width' => 'auto','sort_field' => 'usr_login');
        $cols['usr_firstname'] = array('txt' => $this->lng->txt('usr_firstname'), 'default' => true, 'width' => 'auto','sort_field' => 'usr_firstname');
        $cols['usr_lastname'] = array('txt' => $this->lng->txt('usr_lastname'), 'default' => true, 'width' => 'auto','sort_field' => 'usr_lastname');
        $cols['usr_email'] = array('txt' => $this->lng->txt('usr_email'), 'default' => true, 'width' => 'auto','sort_field' => 'usr_email');
        $cols['usr_assinged_orgus'] = array('txt' => $this->lng->txt('usr_assinged_orgus'), 'default' => true, 'width' => 'auto');

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
        //Actions
        if(!$this->getExportMode()) {
            $this->addColumn($this->lng->txt('actions'));
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