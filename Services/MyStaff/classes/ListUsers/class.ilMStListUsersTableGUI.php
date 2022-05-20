<?php
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
    protected array $filter = array();
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

    protected function parseData() : void
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
        $data = array_map(function (ilMStListUser $it) : array {
            return [$it];
        }, $data);
        $this->setData($data);
    }

    final public function initFilter() : void
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
            $root = ilObjOrgUnit::getRootOrgRefId();
            $tree = ilObjOrgUnitTree::_getInstance();
            $nodes = $tree->getAllChildren($root);
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

    final public function getSelectableColumns() : array
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

        return $cols;
    }

    private function addColumns() : void
    {
        global $DIC;

        //User Profile Picture
        if (!$this->getExportMode()) {
            $this->addColumn('');
        }

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
     * @param array<ilMStListUser> $a_set
     * @return void
     * @throws \ilCtrlException
     * @throws \ilTemplateException
     */
    final public function fillRow(array $a_set) : void
    {
        global $DIC;

        $set = array_pop($a_set);

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $set, $set);

        //Avatar
        $this->tpl->setCurrentBlock('user_profile_picture');
        $il_obj_user = $set->returnIlUserObj();
        $avatar = $this->uiFactory->image()->standard($il_obj_user->getPersonalPicturePath('small'), $il_obj_user->getPublicName());
        $this->tpl->setVariable('user_profile_picture', $this->uiRenderer->render($avatar));
        $this->tpl->parseCurrentBlock();

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'org_units':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable(
                            'VALUE',
                            strval(ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($set->getUsrId()))
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
        }

        $dropdown = $this->uiFactory->dropdown()->standard([
            $this->uiFactory->button()->shy('test', '#')
        ])->withLabel($this->language->txt("actions"));

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($this->language->txt("actions"));
        $actions->setAsynch(true);
        $actions->setId(strval($set->getUsrId()));

        $DIC->ctrl()->setParameterByClass(ilMStListUsersGUI::class, 'mst_lus_usr_id', $set->getUsrId());

        $actions->setAsynchUrl(str_replace("\\", "\\\\", $DIC->ctrl()
                                                             ->getLinkTarget(
                                                                 $this->parent_obj,
                                                                 ilMStListUsersGUI::CMD_GET_ACTIONS,
                                                                 "",
                                                                 true
                                                             )));
        $this->tpl->setVariable('ACTIONS', $this->uiRenderer->render($dropdown));
        $this->tpl->parseCurrentBlock();
    }

    private function getProfileBackUrl() : string
    {
        global $DIC;

        return rawurlencode($DIC->ctrl()->getLinkTargetByClass(
            strtolower(ilMyStaffGUI::class),
            ilMyStaffGUI::CMD_INDEX
        ));
    }

    protected function fillRowExcel(ilExcel $a_excel, int &$a_row, array $a_set) : void
    {
        $set = array_pop($a_set);

        $col = 0;
        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set) : void
    {
        $set = array_pop($a_set);

        foreach ($this->getFieldValuesForExport($set) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }

    protected function getFieldValuesForExport(ilMStListUser $my_staff_user) : array
    {
        global $DIC;

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $my_staff_user, $my_staff_user);

        $field_values = array();

        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'org_units':
                    $field_values[$k] = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($my_staff_user->getUsrId());
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
                    $field_values[$k] = strip_tags($propGetter($k));
                    break;
            }
        }

        return $field_values;
    }
}
