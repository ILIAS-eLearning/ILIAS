<?php

/**
 * Class ilMStShowUserCoursesTableGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilMStShowUserCoursesTableGUI extends ilTable2GUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
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
	 * @param ilMStListUsersGUI $parent_obj
	 * @param string            $parent_cmd
	 */
	public function __construct($parent_obj, $parent_cmd = "index") {

		$this->access = ilMyStaffAccess::getInstance();

		$this->usr_id = $this->dic()->http()->request()->getQueryParams()['usr_id'];

		$this->setPrefix('myst_su');
		$this->setFormName('myst_su');
		$this->setId('myst_su');

		parent::__construct($parent_obj, $parent_cmd, '');
		$this->setRowTemplate('tpl.list_user_courses_row.html', "Services/MyStaff");
		$this->setFormAction($this->ctrl()->getFormAction($parent_obj));;
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


	protected function parseData() {
		global $DIC;
		$ilUser = $DIC['ilUser'];

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setDefaultOrderField('crs_title');

		$this->determineLimit();
		$this->determineOffsetAndOrder();

		//Permission Filter
		$operation = ilOrgUnitOperationQueries::findByOperationString(ilOrgUnitOperation::OP_ACCESS_ENROLMENTS, 'crs');

		$arr_usr_id = $this->access->getUsersForUserOperationAndContext($ilUser->getId(), $operation->getOperationId(), 'crs');

		$this->filter['usr_id'] = $this->usr_id;
		$options = array(
			'filters' => $this->filter,
			'limit'   => array(),
			'count'   => true,
			'sort'    => array(
				'field'     => $this->getOrderField(),
				'direction' => $this->getOrderDirection(),
			),
		);
		$count = ilMStShowUserCourses::getData($arr_usr_id, $options);
		$options['limit'] = array(
			'start' => (int)$this->getOffset(),
			'end'   => (int)$this->getLimit(),
		);
		$options['count'] = false;
		$data = ilMStShowUserCourses::getData($arr_usr_id, $options);

		$this->setMaxCount($count);
		$this->setData($data);
	}


	public function initFilter() {

		$item = new ilTextInputGUI($this->lng()->txt("crs_title"), "crs_title");
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['crs_title'] = $item->getValue();

		// course members
		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
		$item = new ilRepositorySelectorInputGUI($this->lng()
		                                              ->txt("usr_filter_coursemember"), "course");
		$item->setSelectText($this->lng()->txt("mst_select_course"));
		$item->setHeaderMessage($this->lng()->txt("mst_please_select_course"));
		$item->setClickableTypes(array( "crs" ));
		$this->addFilterItem($item);
		$item->readFromSession();
		$item->setParent($this->getParentObject());
		$this->filter["course"] = $item->getValue();

		//membership status
		$item = new ilSelectInputGUI($this->lng()->txt('member_status'), 'memb_status');
		$item->setOptions(array(
			""                                             => $this->lng()->txt("mst_opt_all"),
			ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED   => $this->lng()
			                                                       ->txt('mst_memb_status_requested'),
			ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST => $this->lng()
			                                                       ->txt('mst_memb_status_waitinglist'),
			ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED  => $this->lng()
			                                                       ->txt('mst_memb_status_registered'),
		));
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter["memb_status"] = $item->getValue();

		if (ilObjUserTracking::_enabledLearningProgress()) {
			//learning progress status
			$item = new ilSelectInputGUI($this->lng()->txt('learning_progress'), 'lp_status');
			//+1 because LP_STATUS_NOT_ATTEMPTED_NUM is 0.
			$item->setOptions(array(
				""                                          => $this->lng()->txt("mst_opt_all"),
				ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM + 1 => $this->lng()
				                                                    ->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED),
				ilLPStatus::LP_STATUS_IN_PROGRESS_NUM + 1   => $this->lng()
				                                                    ->txt(ilLPStatus::LP_STATUS_IN_PROGRESS),
				ilLPStatus::LP_STATUS_COMPLETED_NUM + 1     => $this->lng()
				                                                    ->txt(ilLPStatus::LP_STATUS_COMPLETED),
				ilLPStatus::LP_STATUS_FAILED_NUM + 1        => $this->lng()
				                                                    ->txt(ilLPStatus::LP_STATUS_FAILED),
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
	public function getSelectableColumns() {
		$cols = array();

		$cols['crs_title'] = array(
			'txt'        => $this->lng()->txt('crs_title'),
			'default'    => true,
			'width'      => 'auto',
			'sort_field' => 'crs_title',
		);
		$cols['usr_reg_status'] = array(
			'txt'        => $this->lng()->txt('member_status'),
			'default'    => true,
			'width'      => 'auto',
			'sort_field' => 'reg_status',
		);
		if (ilObjUserTracking::_enabledLearningProgress()) {
			$cols['usr_lp_status'] = array(
				'txt'        => $this->lng()->txt('learning_progress'),
				'default'    => true,
				'width'      => 'auto',
				'sort_field' => 'lp_status',
			);
		}

		return $cols;
	}


	private function addColumns() {
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
	public function fillRow($my_staff_course) {

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
						$f = $this->dic()->ui()->factory();
						$renderer = $this->dic()->ui()->renderer();
						$lp_icon = $f->image()
						             ->standard(ilLearningProgressBaseGUI::_getImagePathForStatus($my_staff_course->getUsrLpStatus()), ilLearningProgressBaseGUI::_getStatusText((int)$my_staff_course->getUsrLpStatus()));
						$this->tpl->setCurrentBlock('td');
						$this->tpl->setVariable('VALUE', $renderer->render($lp_icon) . ' '
						                                 . ilLearningProgressBaseGUI::_getStatusText((int)$my_staff_course->getUsrLpStatus()));
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
	protected function fillRowExcel(ilExcel $a_excel, &$a_row, $my_staff_course) {
		$col = 0;
		foreach ($this->getFieldValuesForExport($my_staff_course) as $k => $v) {
			$a_excel->setCell($a_row, $col, $v);
			$col ++;
		}
	}


	/**
	 * @param object          $a_csv
	 * @param ilMStListCourse $my_staff_course
	 */
	protected function fillRowCSV($a_csv, $my_staff_course) {
		foreach ($this->getFieldValuesForExport($my_staff_course) as $k => $v) {
			$a_csv->addColumn($v);
		}
		$a_csv->addRow();
	}


	/**
	 * @param ilMStListCourse $my_staff_course
	 */
	protected function getFieldValuesForExport($my_staff_course) {

		$propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $my_staff_course, $my_staff_course);

		$field_values = array();

		foreach ($this->getSelectableColumns() as $k => $v) {
			switch ($k) {
				case 'usr_reg_status':
					$field_values[$k] = ilMStListCourse::getMembershipStatusText($my_staff_course->getUsrRegStatus());
					break;
				case 'usr_lp_status':
					$field_values[$k] = ilLearningProgressBaseGUI::_getStatusText((int)$my_staff_course->getUsrLpStatus());
					break;
				default:
					$field_values[$k] = strip_tags($propGetter($k));
					break;
			}
		}

		return $field_values;
	}
}
