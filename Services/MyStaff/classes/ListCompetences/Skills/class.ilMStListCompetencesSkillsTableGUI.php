<?php

namespace ILIAS\MyStaff\ListCompetences\Skills;

use Closure;
use ilAdvancedSelectionListGUI;
use ilCSVWriter;
use ilExcel;
use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilMStListCompetencesGUI;
use ilMStListCompetencesSkill;
use ilMStListCompetencesSkills;
use ilMStListCompetencesSkillsGUI;
use ilOrgUnitPathStorage;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTextInputGUI;
use ilUserSearchOptions;

/**
 * Class ilMStListCompetencesTableGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCompetencesSkillsTableGUI extends ilTable2GUI
{

    /**
     * @var array
     */
    protected $filter = array();
    /**
     * @var array
     */
    protected $selectable_columns_cached = [];
    /**
     * @var array
     */
    protected $usr_orgu_names = [];
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
    public function __construct($parent_obj, string $parent_cmd, Container $dic)
    {
        $this->dic = $dic;
        $this->access = ilMyStaffAccess::getInstance();

        $this->setPrefix('myst_cs');
        $this->setFormName('myst_cs');
        $this->setId('myst_cs');

        parent::__construct($parent_obj, $parent_cmd, '');

        $this->setRowTemplate('tpl.list_skills_row.html', "Services/MyStaff");
        $this->setFormAction($this->dic->ctrl()->getFormAction($parent_obj));
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


    /**
     *
     */
    protected function parseData()
    {
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('skill_title');

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


        $skills_fetcher = new ilMStListCompetencesSkills($this->dic);
        $count = $skills_fetcher->getData($options);
        $options['limit'] = array(
            'start' => intval($this->getOffset()),
            'end' => intval($this->getLimit()),
        );
        $options['count'] = false;
        $data = $skills_fetcher->getData($options);
        $this->setMaxCount($count);
        $this->setData($data);
    }


    /**
     *
     */
    public function initFilter()
    {
        // skill
        $item = new ilTextInputGUI($this->dic->language()->txt("skmg_skill"), 'skill');
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['skill'] = $item->getValue();

        // skill level
        $item = new ilTextInputGUI($this->dic->language()->txt("skmg_skill_level"), 'skill_level');
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['skill_level'] = $item->getValue();

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

    public function getSelectableColumns() : array
    {
        if ($this->selectable_columns_cached) {
            return $this->selectable_columns_cached;
        }

        return $this->selectable_columns_cached = $this->initSelectableColumns();
    }

    protected function initSelectableColumns() : array
    {
        $cols = array();

        $arr_searchable_user_columns = ilUserSearchOptions::getSelectableColumnInfo();

        $cols['skill_title'] = array(
            'txt' => $this->dic->language()->txt('skmg_skill'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'skill_title',
        );
        $cols['skill_level'] = array(
            'txt' => $this->dic->language()->txt('skmg_skill_level'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'skill_level',
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
                'sort_field' => 'firstname',
            );
        }
        if ($arr_searchable_user_columns['lastname']) {
            $cols['last_name'] = array(
                'txt' => $this->dic->language()->txt('lastname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'lastname',
            );
        }
        if ($arr_searchable_user_columns['email']) {
            $cols['email'] = array(
                'txt' => $this->dic->language()->txt('email'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'email',
            );
        }
        if ($arr_searchable_user_columns['org_units'] ?? false) {
            $cols['usr_assinged_orgus'] = array(
                'txt' => $this->dic->language()->txt('objs_orgu'),
                'default' => true,
                'width' => 'auto',
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

        //Actions
        if (!$this->getExportMode()) {
            $this->addColumn($this->dic->language()->txt('actions'));
        }
    }

    protected function getTextRepresentationOfUsersOrgUnits(int $user_id) : string
    {
        if (isset($this->usr_orgu_names[$user_id])) {
            return $this->usr_orgu_names[$user_id];
        }

        return $this->usr_orgu_names[$user_id] = \ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
    }

    /**
     * @param ilMStListCompetencesSkill $profile
     */
    public function fillRow($profile)
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $profile, $profile);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'usr_assinged_orgus':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable(
                            'VALUE',
                            $this->getTextRepresentationOfUsersOrgUnits($profile->getUserId())
                        );
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

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($this->dic->language()->txt("actions"));
        $actions->setId($profile->getUserId() . "-" . $profile->getSkillNodeId());

        $mst_lcom_usr_id = $profile->getUserId();

        $this->dic->ctrl()->setParameterByClass(get_class($this->parent_obj), 'mst_lcom_usr_id', $mst_lcom_usr_id);

        $actions = \ilMyStaffGUI::extendActionMenuWithUserActions(
            $actions,
            $mst_lcom_usr_id,
            rawurlencode($this->dic->ctrl()->getLinkTargetByClass(
                "ilMStListCompetencesSkillsGUI",
                \ilMStListCompetencesSkillsGUI::CMD_INDEX
            ))
        );

        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        $this->tpl->parseCurrentBlock();
    }


    /**
     * @param ilExcel                   $a_excel excel wrapper
     * @param int                       $a_row
     * @param ilMStListCompetencesSkill $selected_skill
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $selected_skill)
    {
        $col = 0;
        foreach ($this->getFieldValuesForExport($selected_skill) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }


    /**
     * @param ilCSVWriter               $a_csv
     * @param ilMStListCompetencesSkill $selected_skill
     */
    protected function fillRowCSV($a_csv, $selected_skill)
    {
        foreach ($this->getFieldValuesForExport($selected_skill) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }


    /**
     * @param ilMStListCompetencesSkill $selected_skill
     *
     * @return array
     */
    protected function getFieldValuesForExport(ilMStListCompetencesSkill $selected_skill)
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $selected_skill, $selected_skill);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_assinged_orgus':
                    $field_values[$k] = $this->getTextRepresentationOfUsersOrgUnits($selected_skill->getUserId());
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k));
                    break;
            }
        }

        return $field_values;
    }
}
