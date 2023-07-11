<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

namespace ILIAS\MyStaff\ListCompetences\Skills;

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStListCompetencesTableGUI
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCompetencesSkillsTableGUI extends \ilTable2GUI
{
    protected array $filter = [];
    protected array $selectable_columns_cached = [];
    protected array $usr_orgu_names = [];
    protected ilMyStaffAccess $access;
    protected Container $dic;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;

    public function __construct(\ilMStListCompetencesSkillsGUI $parent_obj, string $parent_cmd, Container $dic)
    {
        $this->dic = $dic;
        $this->access = ilMyStaffAccess::getInstance();
        $this->ui_fac = $this->dic->ui()->factory();
        $this->ui_ren = $this->dic->ui()->renderer();

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

    protected function parseData(): void
    {
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('skill_title');

        $this->determineLimit();
        $this->determineOffsetAndOrder();

        $options = array(
            'filters' => $this->filter,
            'limit' => array(
                'start' => $this->getOffset(),
                'end' => $this->getLimit(),
            ),
            'count' => true,
            'sort' => array(
                'field' => $this->getOrderField(),
                'direction' => $this->getOrderDirection(),
            ),
        );

        $skills_fetcher = new \ilMStListCompetencesSkills($this->dic);
        $result = $skills_fetcher->getData($options);

        $this->setMaxCount($result->getTotalDatasetCount());
        $data = $result->getDataset();

        // Workaround because the fillRow Method only accepts arrays
        $data = array_map(function (\ilMStListCompetencesSkill $it): array {
            return [$it];
        }, $data);
        $this->setData($data);
    }

    final public function initFilter(): void
    {
        // skill
        $item = new \ilTextInputGUI($this->dic->language()->txt("skmg_skill"), 'skill');
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['skill'] = $item->getValue();

        // skill level
        $item = new \ilTextInputGUI($this->dic->language()->txt("skmg_skill_level"), 'skill_level');
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['skill_level'] = $item->getValue();

        //user
        $item = new \ilTextInputGUI(
            $this->dic->language()->txt("login") . "/" . $this->dic->language()->txt("email") . "/" . $this->dic->language()
                                                                                                                                       ->txt("name"),
            "user"
        );

        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['user'] = $item->getValue();

        // orgunits
        if (\ilUserSearchOptions::_isEnabled('org_units')) {
            $paths = \ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
            $options[0] = $this->dic->language()->txt('mst_opt_all');
            foreach ($paths as $org_ref_id => $path) {
                $options[$org_ref_id] = $path;
            }
            $item = new \ilSelectInputGUI($this->dic->language()->txt('obj_orgu'), 'org_unit');
            $item->setOptions($options);
            $this->addFilterItem($item);
            $item->readFromSession();
            $this->filter['org_unit'] = $item->getValue();
        }
    }

    final public function getSelectableColumns(): array
    {
        if ($this->selectable_columns_cached) {
            return $this->selectable_columns_cached;
        }

        return $this->selectable_columns_cached = $this->initSelectableColumns();
    }

    protected function initSelectableColumns(): array
    {
        $cols = array();

        $arr_searchable_user_columns = \ilUserSearchOptions::getSelectableColumnInfo();

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

        if ($arr_searchable_user_columns['login'] ?? false) {
            $cols['login'] = array(
                'txt' => $this->dic->language()->txt('login'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'login',
            );
        }
        if ($arr_searchable_user_columns['firstname'] ?? false) {
            $cols['first_name'] = array(
                'txt' => $this->dic->language()->txt('firstname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'firstname',
            );
        }
        if ($arr_searchable_user_columns['lastname'] ?? false) {
            $cols['last_name'] = array(
                'txt' => $this->dic->language()->txt('lastname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'lastname',
            );
        }
        if ($arr_searchable_user_columns['email'] ?? false) {
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

    private function addColumns(): void
    {
        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                $sort = $v['sort_field'] ?? "";
                $this->addColumn($v['txt'], $sort);
            }
        }

        //Actions
        if (!$this->getExportMode()) {
            $this->addColumn($this->dic->language()->txt('actions'));
        }
    }

    protected function getTextRepresentationOfUsersOrgUnits(int $user_id): string
    {
        if (isset($this->usr_orgu_names[$user_id])) {
            return $this->usr_orgu_names[$user_id];
        }

        return $this->usr_orgu_names[$user_id] = \ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
    }

    /**
     * @param array<\ilMStListCompetencesSkill> $a_set
     */
    final protected function fillRow(array $a_set): void
    {
        $set = array_pop($a_set);

        $propGetter = \Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $set, $set);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'usr_assinged_orgus':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable(
                            'VALUE',
                            $this->getTextRepresentationOfUsersOrgUnits($set->getUserId())
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

        $mst_lcom_usr_id = $set->getUserId();

        $this->dic->ctrl()->setParameterByClass(get_class($this->parent_obj), 'mst_lcom_usr_id', $mst_lcom_usr_id);

        $buttons = \ilMyStaffGUI::extendActionMenuWithUserActions(
            $mst_lcom_usr_id,
            rawurlencode($this->dic->ctrl()->getLinkTargetByClass(
                "ilMStListCompetencesSkillsGUI",
                \ilMStListCompetencesSkillsGUI::CMD_INDEX
            ))
        );

        $dropdown = $this->ui_fac->dropdown()->standard($buttons)->withLabel($this->lng->txt("actions"));
        $this->tpl->setVariable("ACTIONS", $this->ui_ren->render($dropdown));
        $this->tpl->parseCurrentBlock();
    }

    protected function fillRowExcel(\ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        $set = array_pop($a_set);

        $col = 0;
        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }

    protected function fillRowCSV(\ilCSVWriter $a_csv, array $a_set): void
    {
        $set = array_pop($a_set);

        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }

    protected function getFieldValuesForExport(\ilMStListCompetencesSkill $selected_skill): array
    {
        $propGetter = \Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $selected_skill, $selected_skill);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_assinged_orgus':
                    $field_values[$k] = $this->getTextRepresentationOfUsersOrgUnits($selected_skill->getUserId());
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k) ?? "");
                    break;
            }
        }

        return $field_values;
    }
}
