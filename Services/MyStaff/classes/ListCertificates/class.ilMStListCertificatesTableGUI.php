<?php

namespace ILIAS\MyStaff\ListCertificates;

use Certificate\API\Data\UserCertificateDto;
use Closure;
use ilAdvancedSelectionListGUI;
use ilCSVWriter;
use ilDateTime;
use ilExcel;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilMStListCertificatesGUI;
use ilOrgUnitPathStorage;
use ilSelectInputGUI;
use ilTable2GUI;
use ilTextInputGUI;
use ilUserSearchOptions;

/**
 * Class ilMStListCertificatesTableGUI
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCertificatesTableGUI extends ilTable2GUI
{
    protected array $filter = array();
    protected ilMyStaffAccess $access;

    public function __construct(ilMStListCertificatesGUI $parent_obj, $parent_cmd = ilMStListCertificatesGUI::CMD_INDEX)
    {
        global $DIC;

        $this->access = ilMyStaffAccess::getInstance();

        $this->setPrefix('myst_lcrt');
        $this->setFormName('myst_lcrt');
        $this->setId('myst_lcrt');

        parent::__construct($parent_obj, $parent_cmd, '');

        $this->setRowTemplate('tpl.list_courses_row.html', "Services/MyStaff");
        $this->setFormAction($DIC->ctrl()->getFormAction($parent_obj));
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

    private function parseData() : void
    {
        global $DIC;

        $this->setExternalSorting(true);
        $this->setDefaultOrderField('obj_title');

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

        $certificates_fetcher = new ilMStListCertificates($DIC);
        $data = $certificates_fetcher->getData($options);
        $options['limit'] = array(
            'start' => intval($this->getOffset()),
            'end' => intval($this->getLimit()),
        );
        $this->setMaxCount(count($data));

        // Workaround because the fillRow Method only accepts arrays
        $data = array_map(function (UserCertificateDto $it) : array {
            return [$it];
        }, $data);
        $this->setData($data);
    }

    final public function initFilter() : void
    {
        global $DIC;

        $item = new ilTextInputGUI($DIC->language()->txt("title"), "obj_title");
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['obj_title'] = $item->getValue();

        //user
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
            $this->addFilterItem($item);
            $item->readFromSession();
            $this->filter['org_unit'] = $item->getValue();
        }
    }

    final public function getSelectableColumns() : array
    {
        global $DIC;

        $cols = array();

        $arr_searchable_user_columns = ilUserSearchOptions::getSelectableColumnInfo();

        $cols['objectTitle'] = array(
            'txt' => $DIC->language()->txt('title'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'objectTitle',
        );
        $cols['issuedOnTimestamp'] = array(
            'txt' => $DIC->language()->txt('mst_cert_issued_on'),
            'default' => true,
            'width' => 'auto',
            'sort_field' => 'issuedOnTimestamp',
        );
        if ($arr_searchable_user_columns['login']) {
            $cols['userLogin'] = array(
                'txt' => $DIC->language()->txt('login'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userLogin',
            );
        }
        if ($arr_searchable_user_columns['firstname']) {
            $cols['userFirstName'] = array(
                'txt' => $DIC->language()->txt('firstname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userFirstName',
            );
        }
        if ($arr_searchable_user_columns['lastname']) {
            $cols['userLastName'] = array(
                'txt' => $DIC->language()->txt('lastname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userLastName',
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

    private function addColumns() : void
    {
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
     * @param array<UserCertificateDto> $a_set
     * @return void
     * @throws \JsonException
     * @throws \ilDateTimeException
     * @throws \ilTemplateException
     */
    final public function fillRow(array $a_set) : void
    {
        global $DIC;
        
        $set = array_pop($a_set);

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $set, $set);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'usr_assinged_orgus':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable(
                            'VALUE',
                            strval(ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($set->getUserId()))
                        );
                        $this->tpl->parseCurrentBlock();
                        break;
                    case 'issuedOnTimestamp':
                        $date_time = new ilDateTime($propGetter($k), IL_CAL_UNIX);
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', $date_time->get(IL_CAL_DATE));
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
        $actions->setListTitle($DIC->language()->txt("actions"));
        $actions->setAsynch(false);
        $actions->setId($set->getCertificateId());
        $actions->addItem($DIC->language()->txt("mst_download_certificate"), '', $set->getDownloadLink());

        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        $this->tpl->parseCurrentBlock();
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

    private function getFieldValuesForExport(UserCertificateDto $user_certificate_dto) : array
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $user_certificate_dto, $user_certificate_dto);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_assinged_orgus':
                    $field_values[$k] = ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_certificate_dto->getUserId());
                    break;
                case 'issuedOnTimestamp':
                    $field_values[$k] = new ilDateTime($propGetter($k), IL_CAL_UNIX);
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k));
                    break;
            }
        }

        return $field_values;
    }
}
