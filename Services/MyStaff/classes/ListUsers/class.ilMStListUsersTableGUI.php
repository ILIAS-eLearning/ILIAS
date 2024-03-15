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
declare(strict_types=1);

namespace ILIAS\MyStaff\ListUsers;

use Closure;
use ilAdvancedSelectionListGUI;
use ilCSVWriter;
use ilExcel;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilMStListUsersGUI;
use ilObjOrgUnit;
use ilObjOrgUnitTree;
use ilOrgUnitPathStorage;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTextInputGUI;
use ilUserSearchOptions;
use ilMyStaffGUI;

/**
 * Class ilMStListUsersTableGUI
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListUsersTableGUI extends ilTable2GUI
{
    protected array $filter = [];
    protected array $selectable_columns_cached = [];
    protected array $usr_orgu_names = [];
    protected ilMyStaffAccess $access;

    private \ILIAS\UI\Factory $uiFactory;
    private \ILIAS\UI\Renderer $uiRenderer;
    private \ilLanguage $language;

    /**
     * @param ilMStListUsersGUI $parent_obj
     * @param string            $parent_cmd
     */
    public function __construct(ilMStListUsersGUI $parent_obj, $parent_cmd = ilMStListUsersGUI::CMD_INDEX)
    {
        global $DIC;

        $this->access = ilMyStaffAccess::getInstance();

        $this->setPrefix('myst_lu');
        $this->setFormName('myst_lu');
        $this->setId('myst_lu');

        parent::__construct($parent_obj, $parent_cmd, '');

        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();

        $this->setRowTemplate('tpl.list_users_row.html', "Services/MyStaff");
        $this->setFormAction($DIC->ctrl()->getFormAction($parent_obj));
        $this->setDefaultOrderDirection('desc');

        $this->setShowRowsSelector(true);

        $this->setEnableTitle(true);
        $this->setDisableFilterHiding(true);
        $this->setEnableNumInfo(true);

        $this->setExportFormats(array(self::EXPORT_EXCEL, self::EXPORT_CSV));

        $this->setFilterCols(4);
        $this->initFilter();
        $this->addColumns();

        $this->parseData();
    }

    protected function parseData(): void
    {
        global $DIC;

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('lastname');

        $this->determineLimit();
        $this->determineOffsetAndOrder();

        //Permission Filter
        $arr_usr_id = $this->access->getUsersForUser($DIC->user()->getId());

        $options = array(
            'filters' => $this->filter,
            'limit' => array(
                'start' => $this->getOffset(),
                'end' => $this->getLimit(),
            ),
            'sort' => array(
                'field' => $this->getOrderField(),
                'direction' => $this->getOrderDirection(),
            ),
        );

        $list_users_fetcher = new ilMStListUsers($DIC);
        $result = $list_users_fetcher->getData($arr_usr_id, $options);

        $this->setMaxCount($result->getTotalDatasetCount());
        $data = $result->getDataset();

        // Workaround because the fillRow Method only accepts arrays
        $data = array_map(function (ilMStListUser $it): array {
            return [$it];
        }, $data);
        $this->setData($data);
    }

    final public function initFilter(): void
    {
        global $DIC;

        // User name, login, email filter
        $item = new ilTextInputGUI(
            $DIC->language()->txt("login") . "/" . $DIC->language()->txt("email") . "/" . $DIC->language()
                                                                                                                     ->txt("name"),
            "user"
        );
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
            $item->addCustomAttribute("style='width:100%'");
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
        $arr_fields_without_table_sort = array(
            'org_units',
            'interests_general',
            'interests_help_offered',
            'interests_help_looking',
        );
        $cols = array();
        foreach (ilUserSearchOptions::getSelectableColumnInfo() as $key => $col) {
            $cols[$key] = $col;
            if (!in_array($key, $arr_fields_without_table_sort)) {
                $cols[$key]['sort_field'] = $key;
            }
        }

        $user_defined_fields = \ilUserDefinedFields::_getInstance();
        foreach ($user_defined_fields->getDefinitions() as $field => $definition) {
            unset($cols["udf_" . $field]);
        }

        return $cols;
    }

    private function addColumns(): void
    {
        global $DIC;

        //User Profile Picture
        if (!$this->getExportMode()) {
            $this->addColumn('');
        }

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                $sort = $v['sort_field'] ?? "";
                $this->addColumn($v['txt'], $sort);
            }
        }
        //Actions
        if (!$this->getExportMode()) {
            $this->addColumn($DIC->language()->txt('actions'));
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
     * @param array<ilMStListUser> $a_set
     * @return void
     * @throws \ilCtrlException
     * @throws \ilTemplateException
     */
    final protected function fillRow(array $a_set): void
    {
        global $DIC;

        $set = array_pop($a_set);

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $set, $set);

        //Avatar
        $this->tpl->setCurrentBlock('user_profile_picture');
        $il_obj_user = $set->returnIlUserObj();
        $avatar = $this->uiFactory->image()->standard($il_obj_user->getPersonalPicturePath('small'), $il_obj_user->getPublicName());
        $this->tpl->setVariable('user_profile_picture', $this->uiRenderer->render($avatar));
        $this->tpl->parseCurrentBlock();

        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'org_units':
                    $this->tpl->setCurrentBlock('td');
                    $this->tpl->setVariable(
                        'VALUE',
                        $this->getTextRepresentationOfUsersOrgUnits($set->getUsrId())
                    );
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'gender':
                    $this->tpl->setCurrentBlock('td');
                    $this->tpl->setVariable('VALUE', $DIC->language()->txt('gender_' . $set->getGender()));
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'interests_general':
                    $this->tpl->setCurrentBlock('td');
                    $this->tpl->setVariable('VALUE', ($set->returnIlUserObj()
                                                            ->getGeneralInterestsAsText() ? $set->returnIlUserObj()->getGeneralInterestsAsText() : '&nbsp;'));
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'interests_help_offered':
                    $this->tpl->setCurrentBlock('td');
                    $this->tpl->setVariable('VALUE', ($set->returnIlUserObj()
                                                            ->getOfferingHelpAsText() ? $set->returnIlUserObj()->getOfferingHelpAsText() : '&nbsp;'));
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'interests_help_looking':
                    $this->tpl->setCurrentBlock('td');
                    $this->tpl->setVariable('VALUE', ($set->returnIlUserObj()
                                                            ->getLookingForHelpAsText() ? $set->returnIlUserObj()->getLookingForHelpAsText() : '&nbsp;'));
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

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($this->language->txt("actions"));
        $actions->setId(strval($set->getUsrId()));

        $mst_lus_usr_id = $set->getUsrId();

        if ($this->access->hasCurrentUserAccessToCourseMemberships()) {
            $DIC->ctrl()->setParameterByClass(\ilMStShowUserCoursesGUI::class, 'usr_id', $mst_lus_usr_id);
            $actions->addItem(
                $DIC->language()->txt('mst_show_courses'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(array(
                    \ilDashboardGUI::class,
                    ilMyStaffGUI::class,
                    \ilMStShowUserGUI::class,
                    \ilMStShowUserCoursesGUI::class,
                ))
            );
        }

        if ($this->access->hasCurrentUserAccessToCertificates()) {
            $DIC->ctrl()->setParameterByClass(\ilUserCertificateGUI::class, 'usr_id', $mst_lus_usr_id);
            $actions->addItem(
                $DIC->language()->txt('mst_list_certificates'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(array(
                    \ilDashboardGUI::class,
                    ilMyStaffGUI::class,
                    \ilMStShowUserGUI::class,
                    \ilUserCertificateGUI::class,
                ))
            );
        }

        if ($this->access->hasCurrentUserAccessToCompetences()) {
            $DIC->ctrl()->setParameterByClass(\ilMStShowUserCompetencesGUI::class, 'usr_id', $mst_lus_usr_id);
            $actions->addItem(
                $DIC->language()->txt('mst_list_competences'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(array(
                    \ilDashboardGUI::class,
                    ilMyStaffGUI::class,
                    \ilMStShowUserGUI::class,
                    \ilMStShowUserCompetencesGUI::class,
                ))
            );
        }

        $this->ctrl->setParameterByClass(\ilMStListUsersGUI::class, 'mst_lus_usr_id', $mst_lus_usr_id);

        $actions = ilMyStaffGUI::extendActionMenuWithUserActions(
            $actions,
            $mst_lus_usr_id,
            rawurlencode($this->ctrl->getLinkTargetByClass("ilMStListUsersGUI", \ilMStListUsersGUI::CMD_INDEX))
        );

        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    private function getProfileBackUrl(): string
    {
        global $DIC;

        return rawurlencode($DIC->ctrl()->getLinkTargetByClass(
            strtolower(ilMyStaffGUI::class),
            ilMyStaffGUI::CMD_INDEX
        ));
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set): void
    {
        $set = array_pop($a_set);

        $col = 0;
        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $set = array_pop($a_set);

        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }

    protected function getFieldValuesForExport(ilMStListUser $my_staff_user): array
    {
        global $DIC;

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $my_staff_user, $my_staff_user);

        $field_values = array();

        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'org_units':
                    $field_values[$k] = $this->getTextRepresentationOfUsersOrgUnits($my_staff_user->getUsrId());
                    break;
                case 'gender':
                    $field_values[$k] = $DIC->language()->txt('gender_' . $my_staff_user->getGender());
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
                    $field_values[$k] = strip_tags($propGetter($k) ?? "");
                    break;
            }
        }

        return $field_values;
    }
}
