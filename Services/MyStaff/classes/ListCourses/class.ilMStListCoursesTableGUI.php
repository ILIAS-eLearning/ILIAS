<?php

/**
 * Class ilMStListCoursesTableGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilMStListCoursesTableGUI extends ilTable2GUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	/**
	 * @var array
	 */
	protected $filter = array();
	/**
	 * @var ilMyStaffAccess
	 */
	protected $access;


	/**
	 * @param ilMStListCoursesGUI $parent_obj
	 * @param string              $parent_cmd
	 */
	public function __construct($parent_obj, $parent_cmd = "index") {
		$this->access = ilMyStaffAccess::getInstance();

		$this->setPrefix('myst_lc');
		$this->setFormName('myst_lc');
		$this->setId('myst_lc');

		parent::__construct($parent_obj, $parent_cmd, '');

		$this->setRowTemplate('tpl.list_courses_row.html', "Services/MyStaff");
		$this->setFormAction($this->ctrl()->getFormAction($parent_obj));
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
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setDefaultOrderField('crs_title');

		$this->determineLimit();
		$this->determineOffsetAndOrder();

		$options = array(
			'filters' => $this->filter,
			'limit' => array(),
			'count' => true,
			'sort' => array(
				'field' => $this->getOrderField(),
				'direction' => $this->getOrderDirection(),
			),
		);

		$all_users_for_user = $this->access->getUsersForUser($this->dic()->user()->getId());

		$count = ilMStListCourses::getData($all_users_for_user, $options);
		$options['limit'] = array(
			'start' => (int)$this->getOffset(),
			'end' => (int)$this->getLimit(),
		);
		$options['count'] = false;
		$data = ilMStListCourses::getData($all_users_for_user, $options);
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
		$item = new ilRepositorySelectorInputGUI($this->lng()->txt("usr_filter_coursemember"), "course");
		$item->setParent($this->getParentObject());
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
			"" => $this->lng()->txt("mst_opt_all"),
			ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED => $this->lng()->txt('mst_memb_status_requested'),
			ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST => $this->lng()->txt('mst_memb_status_waitinglist'),
			ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED => $this->lng()->txt('mst_memb_status_registered'),
		));
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter["memb_status"] = $item->getValue();

		if (ilObjUserTracking::_enabledLearningProgress() && $this->access->hasCurrentUserAccessToCourseLearningProgressForAtLeastOneUser()) {
			//learning progress status
			$item = new ilSelectInputGUI($this->lng()->txt('learning_progress'), 'lp_status');
			//+1 because LP_STATUS_NOT_ATTEMPTED_NUM is 0.
			$item->setOptions(array(
				"" => $this->lng()->txt("mst_opt_all"),
				ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM + 1 => $this->lng()->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED),
				ilLPStatus::LP_STATUS_IN_PROGRESS_NUM + 1 => $this->lng()->txt(ilLPStatus::LP_STATUS_IN_PROGRESS),
				ilLPStatus::LP_STATUS_COMPLETED_NUM + 1 => $this->lng()->txt(ilLPStatus::LP_STATUS_COMPLETED),
				ilLPStatus::LP_STATUS_FAILED_NUM + 1 => $this->lng()->txt(ilLPStatus::LP_STATUS_FAILED),
			));
			$this->addFilterItem($item);
			$item->readFromSession();
			$this->filter["lp_status"] = $item->getValue();
			if ($this->filter["lp_status"]) {
				$this->filter["lp_status"] = $this->filter["lp_status"] - 1;
			}
		}

		//user
		$item = new ilTextInputGUI($this->lng()->txt("login") . "/" . $this->lng()->txt("email") . "/" . $this->lng()->txt("name"), "user");

		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['user'] = $item->getValue();

		if (ilUserSearchOptions::_isEnabled('org_units')) {
			$paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
			$options[0] = $this->lng()->txt('mst_opt_all');
			foreach ($paths as $org_ref_id => $path) {
				$options[$org_ref_id] = $path;
			}
			$item = new ilSelectInputGUI($this->lng()->txt('obj_orgu'), 'org_unit');
			$item->setOptions($options);
			$this->addFilterItem($item);
			$item->readFromSession();
			$this->filter['org_unit'] = $item->getValue();
		}
	}


	/**
	 * @return array
	 */
	public function getSelectableColumns() {
		$cols = array();

		$arr_searchable_user_columns = ilUserSearchOptions::getSelectableColumnInfo();

		$cols['crs_title'] = array(
			'txt' => $this->lng()->txt('crs_title'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => 'crs_title',
		);
		$cols['usr_reg_status'] = array(
			'txt' => $this->lng()->txt('member_status'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => 'reg_status',
		);
		if (ilObjUserTracking::_enabledLearningProgress() && $this->access->hasCurrentUserAccessToCourseLearningProgressForAtLeastOneUser()) {
			$cols['usr_lp_status'] = array(
				'txt' => $this->lng()->txt('learning_progress'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'lp_status',
			);
		}

		if ($arr_searchable_user_columns['login']) {
			$cols['usr_login'] = array(
				'txt' => $this->lng()->txt('login'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_login',
			);
		}
		if ($arr_searchable_user_columns['firstname']) {
			$cols['usr_firstname'] = array(
				'txt' => $this->lng()->txt('firstname'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_firstname',
			);
		}
		if ($arr_searchable_user_columns['lastname']) {
			$cols['usr_lastname'] = array(
				'txt' => $this->lng()->txt('lastname'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_lastname',
			);
		}

		if ($arr_searchable_user_columns['email']) {
			$cols['usr_email'] = array(
				'txt' => $this->lng()->txt('email'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_email',
			);
		}

		if ($arr_searchable_user_columns['org_units']) {
			$cols['usr_assinged_orgus'] = array(
				'txt' => $this->lng()->txt('objs_orgu'),
				'default' => true,
				'width' => 'auto',
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
					$sort = NULL;
				}
				$this->addColumn($v['txt'], $sort, $v['width']);
			}
		}

		//Actions
		if (!$this->getExportMode()) {
			$this->addColumn($this->lng()->txt('actions'));
		}
	}


	/**
	 * @param ilMStListCourse $my_staff_course
	 */
	public function fillRow($my_staff_course) {
		$propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $my_staff_course, $my_staff_course);

		foreach ($this->getSelectableColumns() as $k => $v) {
			if ($this->isColumnSelected($k)) {
				switch ($k) {
					case 'usr_assinged_orgus':
						$this->tpl->setCurrentBlock('td');
						$this->tpl->setVariable('VALUE', (string)ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($my_staff_course->getUsrId()));
						$this->tpl->parseCurrentBlock();
						break;
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
						if ($propGetter($k) !== NULL) {
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

		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle($this->dic()->language()->txt("actions"));
		$actions->setAsynch(true);
		$actions->setId($my_staff_course->getUsrId() . "-" . $my_staff_course->getCrsRefId());

		$this->dic()->ctrl()->setParameterByClass(ilMStListCoursesGUI::class, 'mst_lco_usr_id', $my_staff_course->getUsrId());
		$this->dic()->ctrl()->setParameterByClass(ilMStListCoursesGUI::class, 'mst_lco_crs_ref_id', $my_staff_course->getCrsRefId());

		$actions->setAsynchUrl(str_replace("\\", "\\\\", $this->dic()->ctrl()
			->getLinkTarget($this->parent_obj, ilMStListCoursesGUI::CMD_GET_ACTIONS, "", true)));
		$this->tpl->setVariable('ACTIONS', $actions->getHTML());
		$this->tpl->parseCurrentBlock();
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
	 * @param \ilMStListCourse $my_staff_course
	 *
	 * @return array
	 */
	protected function getFieldValuesForExport(ilMStListCourse $my_staff_course) {

		$propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $my_staff_course, $my_staff_course);

		$field_values = array();
		foreach ($this->getSelectedColumns() as $k => $v) {
			switch ($k) {
				case 'usr_assinged_orgus':
					$field_values[$k] = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($my_staff_course->getUsrId());
					break;
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
