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
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCertificatesTableGUI extends ilTable2GUI
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
     * @param ilMStListCertificatesGUI $parent_obj
     * @param string                   $parent_cmd
     */
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


    /**
     *
     */
    protected function parseData() : void
    {
        global $DIC;

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('obj_title');

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

        $certificates_fetcher = new ilMStListCertificates($DIC);
        $data = $certificates_fetcher->getData($options);

        $this->setData($data);

        $options['limit'] = array(
            'start' => null,
            'end' => null,
        );
        $max_data = $certificates_fetcher->getData($options);
        $this->setMaxCount(count($max_data));
    }


    public function initFilter()
    {
        global $DIC;

        $item = new ilTextInputGUI($DIC->language()->txt("title"), "obj_title");
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['obj_title'] = $item->getValue();

        //user
        $item = new ilTextInputGUI(
            $DIC->language()->txt("login")
            . "/" . $DIC->language()->txt("email")
            . "/" . $DIC->language()->txt("name"),
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

    public function getSelectableColumns() : array
    {
        if ($this->selectable_columns_cached) {
            return $this->selectable_columns_cached;
        }

        return $this->selectable_columns_cached = $this->initSelectableColumns();
    }

    protected function initSelectableColumns() : array
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
            $cols['userEmail'] = array(
                'txt' => $DIC->language()->txt('email'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userEmail',
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
    private function addColumns()
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

    protected function getTextRepresentationOfUsersOrgUnits(int $user_id) : string
    {
        if (isset($this->usr_orgu_names[$user_id])) {
            return $this->usr_orgu_names[$user_id];
        }

        return $this->usr_orgu_names[$user_id] = \ilOrgUnitPathStorage::getTextRepresentationOfUsersOrgUnits($user_id);
    }

    /**
     * @param UserCertificateDto $user_certificate_dto
     */
    public function fillRow($user_certificate_dto)
    {
        global $DIC;

        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $user_certificate_dto, $user_certificate_dto);

        foreach ($this->getSelectableColumns() as $k => $v) {
            if ($this->isColumnSelected($k)) {
                switch ($k) {
                    case 'usr_assinged_orgus':
                        $this->tpl->setCurrentBlock('td');
                        $this->tpl->setVariable('VALUE', $this->getTextRepresentationOfUsersOrgUnits($user_certificate_dto->getUserId()));
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
        $actions->setId($user_certificate_dto->getCertificateId());
        $actions->addItem($DIC->language()->txt("mst_download_certificate"), '', $user_certificate_dto->getDownloadLink());

        $this->tpl->setVariable('ACTIONS', $actions->getHTML());
        $this->tpl->parseCurrentBlock();
    }


    /**
     * @param ilExcel            $a_excel excel wrapper
     * @param int                $a_row
     * @param UserCertificateDto $user_certificate_dto
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $user_certificate_dto)
    {
        $col = 0;
        foreach ($this->getFieldValuesForExport($user_certificate_dto) as $k => $v) {
            $a_excel->setCell($a_row, $col, $v);
            $col++;
        }
    }


    /**
     * @param ilCSVWriter        $a_csv
     * @param UserCertificateDto $user_certificate_dto
     */
    protected function fillRowCSV($a_csv, $user_certificate_dto)
    {
        foreach ($this->getFieldValuesForExport($user_certificate_dto) as $k => $v) {
            $a_csv->addColumn($v);
        }
        $a_csv->addRow();
    }


    /**
     * @param UserCertificateDto $user_certificate_dto
     *
     * @return array
     */
    protected function getFieldValuesForExport(UserCertificateDto $user_certificate_dto)
    {
        $propGetter = Closure::bind(function ($prop) {
            return $this->$prop;
        }, $user_certificate_dto, $user_certificate_dto);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_assinged_orgus':
                    $field_values[$k] = $this->getTextRepresentationOfUsersOrgUnits($user_certificate_dto->getUserId());
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
