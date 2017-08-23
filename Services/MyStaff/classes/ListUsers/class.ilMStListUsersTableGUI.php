<?php
require_once "./Services/Table/classes/class.ilTable2GUI.php";
require_once "./Services/Form/classes/class.ilTextInputGUI.php";
require_once "./Services/Form/classes/class.ilSelectInputGUI.php";
require_once "class.ilMStListUsers.php";
require_once "class.ilMStListUser.php";
require_once "./Services/MyStaff/classes/class.ilMyStaffAccess.php";

require_once "./Services/Form/classes/class.ilTextInputGUI.php";
require_once "./Modules/OrgUnit/classes/class.ilObjOrgUnit.php";
require_once "./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php";
require_once "./Modules/OrgUnit/classes/PathStorage/class.ilOrgUnitPathStorage.php";

//require_once("./Services/Container/classes/class.ilContainerObjectiveGUI.php");

/**
 * Class ilMStListUsersTableGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMStListUsersTableGUI extends ilTable2GUI {

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
     * @var \ILIAS\UI\Factory
     */
    protected $factory;



	/**
	 * @param ilMStListUsersGUI $parent_obj
	 * @param string $parent_cmd
	 */
	public function __construct($parent_obj, $parent_cmd = "index") {
		/** @var $ilCtrl ilCtrl */
		/** @var ilToolbarGUI $ilToolbar */
        /** @var $DIC ILIAS\DI\Container */
		global $ilCtrl, $ilToolbar, $DIC, $tpl, $lng, $ilUser;

		$this->ctrl = $ilCtrl;

        $this->access = ilMyStaffAcess::getInstance();

		$this->lng = $lng;
		$this->toolbar = $ilToolbar;

        $this->dic = $DIC;

		$this->setPrefix('myst_lu');
		$this->setFormName('myst_lu');
		$this->setId('myst_lu');

		parent::__construct($parent_obj, $parent_cmd, '');
		//$this->addMultiCommand('multiUserAccreditation', $this->pl->txt('accr_create_courses'));
		$this->setRowTemplate('tpl.list_users_row.html',"Services/MyStaff");
		$this->setFormAction($this->ctrl->getFormAction($parent_obj));
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
		$this->setDefaultOrderField('lastname');

		$this->determineLimit();
		$this->determineOffsetAndOrder();

        //Permission Filter
        $arr_usr_id = $this->access->getOrguUsersOfCurrentUserWithShowStaffPermission();

		$options = array(
			'filters' => $this->filter,
			'limit' => array(),
			'count' => true,
			'sort' => array('field' => $this->getOrderField(),'direction' => $this->getOrderDirection()),
		);
		$count = ilMStListUsers::getData($arr_usr_id,$options);
		$options['limit'] = array('start' => (int)$this->getOffset(), 'end' => (int)$this->getLimit());
		$options['count'] = false;
		$data = ilMStListUsers::getData($arr_usr_id,$options);

		$this->setMaxCount($count);
		$this->setData($data);
	}


	public function initFilter() {


        // User name, login, email filter
        $item = new ilTextInputGUI($this->lng->txt("login")."/".$this->lng->txt("email")."/".$this->lng->txt("name"), "user");
        //$item->setDataSource($this->ctrl->getLinkTarget($this->getParentObject(),"addUserAutoComplete", "", true));
        //$item->setSize(20);
        //$item->setSubmitFormOnEnter(true);
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['user'] = $item->getValue();



        $root = ilObjOrgUnit::getRootOrgRefId();
        $tree = ilObjOrgUnitTree::_getInstance();
        $nodes = $tree->getAllChildren($root);
        $paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
        $options[0] = $this->lng->txt('mst_opt_all');
        foreach($paths as $org_ref_id => $path)
        {
            $options[$org_ref_id] = $path;
        }
        $item = new ilSelectInputGUI($this->lng->txt('obj_orgu'), 'org_unit');
        $item->setOptions($options);
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['org_unit'] = $item->getValue();



        /*
        $ul->readFromSession();
        $this->filter["query"] = $ul->getValue();

		//User
		$item = new ilTextInputGUI($this->lng->txt('usr'), 'user');
		$this->addFilterItem($item);
		$item->readFromSession();
		$this->filter['usr_lastname'] = $item->getValue();*/

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

	    $arr_fields_without_table_sort = array('org_units','interests_general','interests_help_offered','interests_help_looking');
		$cols = array();
        foreach(ilUserSearchOptions::getSelectableColumnInfo() as $key => $col) {
            $cols[$key] = $col;
            if(!in_array($key,$arr_fields_without_table_sort)) {
                $cols[$key]['sort_field'] = $key;
            }
        }

		return $cols;
	}


	private function addColumns() {
		//$this->setSelectAllCheckbox("user_ids[]");
		//$this->addColumn('', '', '1', true);

        //User Profile Picture
        if(!$this->getExportMode()) {
            $this->addColumn('');
        }

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
     * @param ilMStListUser $my_staff_user
     */
    public function fillRow($my_staff_user) {
        global $ilUser;

        $propGetter = Closure::bind(  function($prop){return $this->$prop;}, $my_staff_user, $my_staff_user );

        //Avatar
        $this->tpl->setCurrentBlock('user_profile_picture');
        $f = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();
        $il_obj_user = $my_staff_user->returnIlUserObj();
        $avatar = $f->image()->standard($il_obj_user->getPersonalPicturePath('small'), $il_obj_user->getPublicName());
        $this->tpl->setVariable('user_profile_picture',  $renderer->render($avatar));
        $this->tpl->parseCurrentBlock();

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch($k) {
                    case 'org_units':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', (string)ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($my_staff_user->getUsrId()));
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'gender':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', $this->lng->txt('gender_'.$my_staff_user->getGender()));
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'interests_general':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', ($my_staff_user->returnIlUserObj()->getGeneralInterestsAsText() ? $my_staff_user->returnIlUserObj()->getGeneralInterestsAsText() : '&nbsp;'));
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'interests_help_offered':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', ($my_staff_user->returnIlUserObj()->getOfferingHelpAsText() ? $my_staff_user->returnIlUserObj()->getOfferingHelpAsText()  : '&nbsp;'));
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'interests_help_looking':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', ($my_staff_user->returnIlUserObj()->getLookingForHelpAsText() ? $my_staff_user->returnIlUserObj()->getLookingForHelpAsText() : '&nbsp;'));
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

        $user_action_collector =  ilUserActionCollector::getInstance($ilUser->getId());
        //TODO context, context_id
        $action_collection = $user_action_collector->getActionsForTargetUser($my_staff_user->getUsrId(),'awrn','toplist');

        //TODO Async
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($this->lng->txt('actions'));
        $selection->setId('selection_list_' . $my_staff_user->getUsrId());
        foreach ($action_collection->getActions() as $action) {
            $selection->addItem($action->getText(), '', $action->getHref());
        }
        $this->ctrl->setParameterByClass('ilMStShowUserGUI','usr_id',$my_staff_user->getUsrId());
        $selection->addItem($this->lng->txt('mst_show_courses'), '', $this->ctrl->getLinkTargetByClass(array('ilPersonalDesktopGUI','ilMyStaffGUI','ilMStShowUserGUI')));
        $this->tpl->setVariable('ACTIONS', $selection->getHTML());

    }


    /**
     * @param ilExcel $a_excel	excel wrapper
     * @param int    $a_row
     * @param ilMyStaffUser $my_staff_user
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $my_staff_user) {
        $col = 0;
        foreach ($this->getFieldValuesForExport($my_staff_user) as $k => $v) {
                $a_excel->setCell($a_row, $col, $v);
                $col ++;
        }
    }

    /**
     * @param object $a_csv
     * @param ilMyStaffUser $my_staff_user
     */
    protected function fillRowCSV($a_csv, $my_staff_user) {
        foreach ($this->getFieldValuesForExport($my_staff_user) as $k => $v) {
                $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }

    /**
     * @param ilMyStaffUser $my_staff_user
     */
    protected function getFieldValuesForExport($my_staff_user) {

        $propGetter = Closure::bind(  function($prop){return $this->$prop;}, $my_staff_user, $my_staff_user);

        $field_values = array();

        foreach ($this->getSelectableColumns() as $k => $v) {
            switch($k) {
                case 'org_units':
                    $field_values[$k] = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($my_staff_user->getUsrId());
                    break;
                case 'gender':
                    $field_values[$k] = $this->lng->txt('gender_' . $my_staff_user->getGender());
                    break;
                case 'interests_general':
                    $field_values[$k] = $my_staff_user->returnIlUserObj()->getGeneralInterestsAsText();
                    break;
                case 'interests_help_offered':
                    $field_values[$k] = $my_staff_user->returnIlUserObj()->getOfferingHelpAsText();
                    break;
                case 'interests_help_looking':
                    $field_values[$k] = $my_staff_user->returnIlUserObj()->getLookingForHelpAsText();
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k));
                    break;
            }
        }

        return $field_values;
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