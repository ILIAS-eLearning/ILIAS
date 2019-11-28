<?php
namespace ILIAS\MyStaff\ListStudyProgrammes;
use Closure;
use ilAdvancedSelectionListGUI;
use ilCSVWriter;
use ilExcel;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilLPStatus;
use ilMStListStudyProgrammesGUI;
use ilObjUserTracking;
use ilOrgUnitPathStorage;
use ilRepositorySelectorInputGUI;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTextInputGUI;
use ilUserSearchOptions;

/**
 * Class ilMStListStudyProgrammesTableGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListStudyProgrammesTableGUI extends ilTable2GUI {

	/**
	 * @var array
	 */
	protected $filter = array();
	/**
	 * @var ilMyStaffAccess
	 */
	protected $access;


	/**
	 * @param ilMStListStudyProgrammesGUI $parent_obj
	 * @param string              $parent_cmd
	 */
	public function __construct(ilMStListStudyProgrammesGUI $parent_obj, $parent_cmd = ilMStListStudyProgrammesGUI::CMD_INDEX) {
		global $DIC;

		$this->access = ilMyStaffAccess::getInstance();

		$this->setPrefix('myst_lc');
		$this->setFormName('myst_lc');
		$this->setId('myst_lc');

		parent::__construct($parent_obj, $parent_cmd, '');

		$this->setRowTemplate('tpl.list_courses_row.html', "Services/MyStaff");
		$this->setFormAction($DIC->ctrl()->getFormAction($parent_obj));
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
	protected function parseData() {
		global $DIC;

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

		$all_users_for_user = $this->access->getUsersForUser($DIC->user()->getId());

		$count = ilMStListStudyProgrammes::getData($all_users_for_user, $options);
		$options['limit'] = array(
			'start' => intval($this->getOffset()),
			'end' => intval($this->getLimit()),
		);
		$options['count'] = false;
		$data = ilMStListStudyProgrammes::getData($all_users_for_user, $options);
		$this->setMaxCount($count);
		$this->setData($data);
	}


	/**
	 *
	 */
	public function initFilter() {
		global $DIC;

		$item = new ilTextInputGUI($DIC->language()->txt("st_prog_title"), "st_prog_title");
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['st_prog_title'] = $item->getValue();

		// course members
		$item = new ilRepositorySelectorInputGUI($DIC->language()->txt("usr_filter_coursemember"), "course");
		$item->setParent($this->getParentObject());
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
			ilMStListStudyProgramme::MEMBERSHIP_STATUS_REQUESTED => $DIC->language()->txt('mst_memb_status_requested'),
			ilMStListStudyProgramme::MEMBERSHIP_STATUS_WAITINGLIST => $DIC->language()->txt('mst_memb_status_waitinglist'),
			ilMStListStudyProgramme::MEMBERSHIP_STATUS_REGISTERED => $DIC->language()->txt('mst_memb_status_registered'),
		));
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter["memb_status"] = $item->getValue();

		if (ilObjUserTracking::_enabledLearningProgress() && $this->access->hasCurrentUserAccessToStudyProgrammeLearningProgressForAtLeastOneUser()) {
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

		//user
		$item = new ilTextInputGUI($DIC->language()->txt("login") . "/" . $DIC->language()->txt("email") . "/" . $DIC->language()
				->txt("name"), "user");

		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['user'] = $item->getValue();

		if (ilUserSearchOptions::_isEnabled('org_units')) {
			$paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
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


	/**
	 * @return array
	 */
	public function getSelectableColumns() {
		global $DIC;

		$cols = array();

		$arr_searchable_user_columns = ilUserSearchOptions::getSelectableColumnInfo();

		$cols['st_prog_title'] = array(
			'txt' => $DIC->language()->txt('st_prog_title'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => 'st_prog_title',
		);
		$cols['st_prog_usr_status'] = array(
			'txt' => $DIC->language()->txt('st_prog_usr_status'),
			'default' => true,
			'width' => 'auto',
			'sort_field' => 'st_prog_usr_status',
		);
        $cols['st_prog_points_required'] = array(
            'txt' => $DIC->language()->txt('st_prog_points_required'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'st_prog_points_required',
        );
        $cols['st_prog_usr_status'] = array(
            'txt' => $DIC->language()->txt('st_prog_points_current'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'st_prog_points_current',
        );
        $cols['st_prog_assignment_date'] = array(
            'txt' => $DIC->language()->txt('st_prog_assignment_date'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'st_prog_assignment_date',
        );
        $cols['st_prog_completion_date'] = array(
            'txt' => $DIC->language()->txt('st_prog_completion_date'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'st_prog_completion_date',
        );
        $cols['st_prog_expiry_date'] = array(
            'txt' => $DIC->language()->txt('st_prog_expiry_date'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'st_prog_expiry_date',
        );
        $cols['st_prog_validity'] = array(
            'txt' => $DIC->language()->txt('st_prog_validity'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'st_prog_validity',
        );


		if ($arr_searchable_user_columns['login']) {
			$cols['usr_login'] = array(
				'txt' => $DIC->language()->txt('login'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_login',
			);
		}
		if ($arr_searchable_user_columns['firstname']) {
			$cols['usr_firstname'] = array(
				'txt' => $DIC->language()->txt('firstname'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_firstname',
			);
		}
		if ($arr_searchable_user_columns['lastname']) {
			$cols['usr_lastname'] = array(
				'txt' => $DIC->language()->txt('lastname'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_lastname',
			);
		}

		if ($arr_searchable_user_columns['email']) {
			$cols['usr_email'] = array(
				'txt' => $DIC->language()->txt('email'),
				'default' => true,
				'width' => 'auto',
				'sort_field' => 'usr_email',
			);
		}

		if ($arr_searchable_user_columns['org_units']) {
			$cols['usr_assinged_orgus'] = array(
				'txt' => $DIC->language()->txt('objs_orgu'),
				'default' => true,
				'width' => 'auto',
			);
		}

		return $cols;
	}


	/**
	 *
	 */
	private function addColumns() {
		global $DIC;

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

		//Actions
		if (!$this->getExportMode()) {
			$this->addColumn($DIC->language()->txt('actions'));
		}
	}


	/**
	 * @param ilMStListStudyProgramme $profile
	 */
	public function fillRow($profile) {
		global $DIC;

		$propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $profile, $profile);

		foreach ($this->getSelectableColumns() as $k => $v) {
			if ($this->isColumnSelected($k)) {
				switch ($k) {
					case 'usr_assinged_orgus':
						$this->tpl->setCurrentBlock('td');
						$this->tpl->setVariable('VALUE', strval(ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($profile->getUsrId())));
						$this->tpl->parseCurrentBlock();
						break;
					case 'usr_reg_status':
						$this->tpl->setCurrentBlock('td');
						$this->tpl->setVariable('VALUE', ilMStListStudyProgramme::getMembershipStatusText($profile->getUsrRegStatus()));
						$this->tpl->parseCurrentBlock();
						break;
					case 'usr_lp_status':
						$this->tpl->setCurrentBlock('td');
						$this->tpl->setVariable('VALUE', ilMyStaffGUI::getUserLpStatusAsHtml($profile));
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

		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle($DIC->language()->txt("actions"));
		$actions->setAsynch(true);
		$actions->setId($profile->getUsrId() . "-" . $profile->getCrsRefId());

		$DIC->ctrl()->setParameterByClass(ilMStListStudyProgrammesGUI::class, 'mst_lco_usr_id', $profile->getUsrId());
		$DIC->ctrl()->setParameterByClass(ilMStListStudyProgrammesGUI::class, 'mst_lco_crs_ref_id', $profile->getCrsRefId());

		$actions->setAsynchUrl(str_replace("\\", "\\\\", $DIC->ctrl()
			->getLinkTarget($this->parent_obj, ilMStListStudyProgrammesGUI::CMD_GET_ACTIONS, "", true)));
		$this->tpl->setVariable('ACTIONS', $actions->getHTML());
		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @param ilExcel                 $a_excel excel wrapper
	 * @param int                     $a_row
	 * @param ilMStListStudyProgramme $selected_skill
	 */
	protected function fillRowExcel(ilExcel $a_excel, &$a_row, $selected_skill) {
		$col = 0;
		foreach ($this->getFieldValuesForExport($selected_skill) as $k => $v) {
			$a_excel->setCell($a_row, $col, $v);
			$col ++;
		}
	}


	/**
	 * @param ilCSVWriter             $a_csv
	 * @param ilMStListStudyProgramme $selected_skill
	 */
	protected function fillRowCSV($a_csv, $selected_skill) {
		foreach ($this->getFieldValuesForExport($selected_skill) as $k => $v) {
			$a_csv->addColumn($v);
		}
		$a_csv->addRow();
	}


	/**
	 * @param ilMStListStudyProgramme $my_staff_course
	 *
	 * @return array
	 */
	protected function getFieldValuesForExport(ilMStListStudyProgramme $my_staff_course) {
		$propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $my_staff_course, $my_staff_course);

		$field_values = array();
		foreach ($this->getSelectedColumns() as $k => $v) {
			switch ($k) {
				case 'usr_assinged_orgus':
					$field_values[$k] = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($my_staff_course->getUsrId());
					break;
				case 'usr_reg_status':
					$field_values[$k] = ilMStListStudyProgramme::getMembershipStatusText($my_staff_course->getUsrRegStatus());
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
