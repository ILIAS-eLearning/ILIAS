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
	 * @param ilMStListUsersGUI $parent_obj
	 * @param string $parent_cmd
	 */
	public function __construct($parent_obj, $parent_cmd = "index") {
		/** @var $ilCtrl ilCtrl */
		/** @var ilToolbarGUI $ilToolbar */
		global $ilCtrl, $ilToolbar, $tpl, $lng, $ilUser;

		$this->ctrl = $ilCtrl;

		//$this->access = $this->pl->getAccessManager();
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

		//$this->setIgnoredCols(array('action', 'skills'));
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
		$this->setDefaultOrderField($this->columns[0]);

		$this->determineLimit();
		$this->determineOffsetAndOrder();

		$permission_filters = array();

		$options = array(
			'filters' => $this->filter,
			'limit' => array(),
			'count' => true,
			'sort' => array('field' => 'lastname', 'direction' => 'ASC')
		);
		$count = ilMStListCourses::getData($options);
		$options['limit'] = array('start' => (int)$this->getOffset(), 'end' => (int)$this->getLimit());
		$options['count'] = false;
		$data = ilMStListCourses::getData($options);

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

		$cols['crs_title'] = array('txt' => $this->lng->txt('crs_title'), 'default' => true, 'width' => 'auto');
		$cols['usr_reg_status'] = array('txt' => $this->lng->txt('usr_reg_status'), 'default' => true, 'width' => 'auto');
		$cols['usr_lp_status'] = array('txt' => $this->lng->txt('usr_lp_status'), 'default' => true, 'width' => 'auto');
        $cols['usr_login'] = array('txt' => $this->lng->txt('usr_login'), 'default' => true, 'width' => 'auto');
        $cols['usr_firstname'] = array('txt' => $this->lng->txt('usr_firstname'), 'default' => true, 'width' => 'auto');
        $cols['usr_lastname'] = array('txt' => $this->lng->txt('usr_lastname'), 'default' => true, 'width' => 'auto');
        $cols['usr_email'] = array('txt' => $this->lng->txt('usr_email'), 'default' => true, 'width' => 'auto');
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
	 * @param array $formats
	 */
	public function setExportFormats(array $formats) {

		parent::setExportFormats($formats);

		$custom_fields = array_diff($formats, $this->export_formats);

		foreach ($custom_fields as $format_key) {
			if (isset($this->custom_export_formats[$format_key])) {
				$this->export_formats[$format_key] = $this->pl->getPrefix() . "_" . $this->custom_export_formats[$format_key];
			}
		}
	}


	public function exportData($format, $send = false) {
		if (array_key_exists($format, $this->custom_export_formats)) {
			if ($this->dataExists()) {

				foreach ($this->custom_export_generators as $export_format => $generator_config) {
					if ($this->getExportMode() == $export_format) {
						$generator_config['generator']->generate();
					}
				}
			}
		} else {
			parent::exportData($format, $send);
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
     * @param object $a_worksheet
     * @param int    $a_row
     * @param array  $a_set
     */
    /*
    protected function fillRowExcel($a_worksheet, &$a_row, $a_set) {
        $col = 0;

        foreach ($this->getSelectableColumns() as $k => $v) {
            if (is_array($a_set[$k])) {
                $value = implode(', ', $a_set[$k]);
            }

            if ($this->isColumnSelected($k)) {
                $a_worksheet->writeString($a_row, $col, strip_tags($a_set[$k]));
                $col ++;
            }
        }
    }*/

    /**
     * @param object $a_csv
     * @param array  $a_set
     */
    /*
    protected function fillRowCSV($a_csv, $a_set) {
        foreach ($this->getSelectableColumns() as $k => $v) {
            if (is_array($a_set[$k])) {
                $value = implode(', ', $a_set[$k]);
            }
            if (!in_array($k, $this->getIgnoredCols()) && $this->isColumnSelected($k)) {
                $a_csv->addColumn(strip_tags($a_set[$k]));
            }
        }
        $a_csv->addRow();
    }*/

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