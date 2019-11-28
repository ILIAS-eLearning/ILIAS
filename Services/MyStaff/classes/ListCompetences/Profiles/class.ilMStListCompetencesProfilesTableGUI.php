<?php

namespace ILIAS\MyStaff\ListCompetences\Profiles;

use Closure;
use ilAdvancedSelectionListGUI;
use ilCSVWriter;
use ilExcel;
use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilLPStatus;
use ilMStListCompetencesGUI;
use ilMStListCompetencesProfile;
use ilMStListCompetencesProfiles;
use ilMyStaffGUI;
use ilObjUserTracking;
use ilOrgUnitPathStorage;
use ilRepositorySelectorInputGUI;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTextInputGUI;
use ilUserSearchOptions;

/**
 * Class ilMStListCompetencesProfilesTableGUI
 *
 * @package ILIAS\MyStaff\ListCompetences
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesProfilesTableGUI extends ilTable2GUI
{
    /**
     * @var array
     */
    protected $filter = array();
    /**
     * @var ilMyStaffAccess
     */
    protected $access;
    /**
     * @var Container
     */
    protected $dic;


    /**
     * @param           $parent_obj
     * @param string    $parent_cmd
     * @param Container $dic
     */
    public function __construct($parent_obj, $parent_cmd, Container $dic) {
        $this->dic = $dic;

        $this->access = ilMyStaffAccess::getInstance();

        $this->setPrefix('myst_cp');
        $this->setFormName('myst_cp');
        $this->setId('myst_cp');

        parent::__construct($parent_obj, $parent_cmd, '');

        $this->setRowTemplate('tpl.list_skills_row.html', "Services/MyStaff");
        $this->setFormAction($this->dic->ctrl()->getFormAction($parent_obj));
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
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('profile_title');

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

        $all_users_for_user = $this->access->getUsersForUser($this->dic->user()->getId());

        $profiles_fetcher = new ilMStListCompetencesProfiles($this->dic);
        $count = $profiles_fetcher->getData($all_users_for_user, $options);
        $options['limit'] = array(
            'start' => intval($this->getOffset()),
            'end' => intval($this->getLimit()),
        );
        $options['count'] = false;
        $data = $profiles_fetcher->getData($all_users_for_user, $options);
        $this->setMaxCount($count);
        $this->setData($data);
    }


    /**
     *
     */
    public function initFilter() {
        //user
        $item = new ilTextInputGUI($this->dic->language()->txt("login") . "/" . $this->dic->language()->txt("email") . "/" . $this->dic->language()
                ->txt("name"), "user");

        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['user'] = $item->getValue();

        // orgunits
        if (ilUserSearchOptions::_isEnabled('org_units')) {
            $paths = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
            $options[0] = $this->dic->language()->txt('mst_opt_all');
            foreach ($paths as $org_ref_id => $path) {
                $options[$org_ref_id] = $path;
            }
            $item = new ilSelectInputGUI($this->dic->language()->txt('obj_orgu'), 'org_unit');
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

        $cols['profile_title'] = array(
            'txt' => $this->dic->language()->txt('profile'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'profile_title',
        );
        $cols['status'] = array(
            'txt' => $this->dic->language()->txt('status'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'fulfilled',
        );


        if ($arr_searchable_user_columns['login']) {
            $cols['login'] = array(
                'txt' => $this->dic->language()->txt('login'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'login',
            );
        }
        if ($arr_searchable_user_columns['firstname']) {
            $cols['first_name'] = array(
                'txt' => $this->dic->language()->txt('firstname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'first_name',
            );
        }
        if ($arr_searchable_user_columns['lastname']) {
            $cols['last_name'] = array(
                'txt' => $this->dic->language()->txt('lastname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'last_name',
            );
        }

        return $cols;
    }


    /**
     *
     */
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

        //Actions
        if (!$this->getExportMode()) {
            $this->addColumn($this->dic->language()->txt('actions'));
        }
    }


    /**
     * @param ilMStListCompetencesProfile $profile
     */
    public function fillRow($profile) {
        $propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $profile, $profile);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'status':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', $profile->isFulfilled() ? $this->dic->language()->txt('mst_profile_fulfilled') : $this->dic->language()->txt('mst_profile_not_fulfilled'));
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
        $actions->setListTitle($this->dic->language()->txt("actions"));
        $actions->setAsynch(true);
        $actions->setId($profile->getUserId() . "-" . $profile->getProfileTitle());

        $this->dic->ctrl()->setParameterByClass(get_class($this->parent_obj), 'mst_lprof_usr_id', $profile->getUserId());

        $actions->setAsynchUrl(str_replace("\\", "\\\\", $this->dic->ctrl()
            ->getLinkTarget($this->parent_obj, ilMStListCompetencesGUI::CMD_GET_ACTIONS, "", true)));
        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        $this->tpl->parseCurrentBlock();
    }


    /**
     * @param ilExcel             $a_excel excel wrapper
     * @param int                 $a_row
     * @param ilMStListCompetencesProfile $selected_skill
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $selected_skill) {
        $col = 0;
        foreach ($this->getFieldValuesForExport($selected_skill) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col ++;
        }
    }


    /**
     * @param ilCSVWriter         $a_csv
     * @param ilMStListCompetencesProfile $selected_skill
     */
    protected function fillRowCSV($a_csv, $selected_skill) {
        foreach ($this->getFieldValuesForExport($selected_skill) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }


    /**
     * @param ilMStListCompetencesProfile $profile
     *
     * @return array
     */
    protected function getFieldValuesForExport(ilMStListCompetencesProfile $profile) {
        $propGetter = Closure::bind(function ($prop) { return $this->$prop; }, $profile, $profile);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'fulfilled':
                    $field_values[$k] = $profile->isFulfilled() ? $this->dic->language()->txt('fulfilled') : $this->dic->language()->txt('not_yet_fulfilled');
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k));
                    break;
            }
        }

        return $field_values;
    }
}