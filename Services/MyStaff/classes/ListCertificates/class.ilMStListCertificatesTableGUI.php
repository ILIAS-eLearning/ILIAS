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

namespace ILIAS\MyStaff\ListCertificates;

use ILIAS\Certificate\API\Data\UserCertificateDto;
use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStListCertificatesTableGUI
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCertificatesTableGUI extends \ilTable2GUI
{
    protected array $filter = [];
    protected array $selectable_columns_cached = [];
    protected array $usr_orgu_names = [];
    protected ilMyStaffAccess $access;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;

    public function __construct(\ilMStListCertificatesGUI $parent_obj, $parent_cmd = \ilMStListCertificatesGUI::CMD_INDEX)
    {
        global $DIC;

        $this->access = ilMyStaffAccess::getInstance();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();

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

    private function parseData(): void
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

        // Workaround because the fillRow Method only accepts arrays
        $data = array_map(function (UserCertificateDto $it): array {
            return [$it];
        }, $data);
        $this->setData($data);

        $options['limit'] = array(
            'start' => null,
            'end' => null,
        );
        $max_data = $certificates_fetcher->getData($options);
        $this->setMaxCount(count($max_data));
    }

    final public function initFilter(): void
    {
        global $DIC;

        $item = new \ilTextInputGUI($DIC->language()->txt("title"), "obj_title");
        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['obj_title'] = $item->getValue();

        //user
        $item = new \ilTextInputGUI(
            $DIC->language()->txt("login")
            . "/" . $DIC->language()->txt("email")
            . "/" . $DIC->language()->txt("name"),
            "user"
        );

        $this->addFilterItem($item);
        $item->readFromSession();
        $this->filter['user'] = $item->getValue();

        if (\ilUserSearchOptions::_isEnabled('org_units')) {
            $paths = \ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits();
            $options[0] = $DIC->language()->txt('mst_opt_all');
            foreach ($paths as $org_ref_id => $path) {
                $options[$org_ref_id] = $path;
            }
            $item = new \ilSelectInputGUI($DIC->language()->txt('obj_orgu'), 'org_unit');
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
        global $DIC;

        $cols = array();

        $arr_searchable_user_columns = \ilUserSearchOptions::getSelectableColumnInfo();

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
        if ($arr_searchable_user_columns['login'] ?? false) {
            $cols['userLogin'] = array(
                'txt' => $DIC->language()->txt('login'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userLogin',
            );
        }
        if ($arr_searchable_user_columns['firstname'] ?? false) {
            $cols['userFirstName'] = array(
                'txt' => $DIC->language()->txt('firstname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userFirstName',
            );
        }
        if ($arr_searchable_user_columns['lastname'] ?? false) {
            $cols['userLastName'] = array(
                'txt' => $DIC->language()->txt('lastname'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userLastName',
            );
        }

        if ($arr_searchable_user_columns['email'] ?? false) {
            $cols['userEmail'] = array(
                'txt' => $DIC->language()->txt('email'),
                'default' => true,
                'width' => 'auto',
                'sort_field' => 'userEmail',
            );
        }
        if ($arr_searchable_user_columns['org_units'] ?? false) {
            $cols['usr_assinged_orgus'] = array(
                'txt' => $DIC->language()->txt('objs_orgu'),
                'default' => true,
                'width' => 'auto',
            );
        }

        return $cols;
    }

    private function addColumns(): void
    {
        global $DIC;

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
     * @param array<UserCertificateDto> $a_set
     * @return void
     * @throws \JsonException
     * @throws \ilDateTimeException
     * @throws \ilTemplateException
     */
    final protected function fillRow(array $a_set): void
    {
        global $DIC;

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
                    case 'issuedOnTimestamp':
                        $date_time = new \ilDateTime($propGetter($k), IL_CAL_UNIX);
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

        $button = $this->ui_fac->button()->shy($this->lng->txt("mst_download_certificate"), $set->getDownloadLink());
        $dropdown = $this->ui_fac->dropdown()->standard([$button])->withLabel($this->lng->txt("actions"));
        $this->tpl->setVariable('ACTIONS', $this->ui_ren->render($dropdown));
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

    private function getFieldValuesForExport(UserCertificateDto $user_certificate_dto): array
    {
        $propGetter = \Closure::bind(function ($prop) {
            return $this->$prop ?? null;
        }, $user_certificate_dto, $user_certificate_dto);

        $field_values = array();
        foreach ($this->getSelectedColumns() as $k => $v) {
            switch ($k) {
                case 'usr_assinged_orgus':
                    $field_values[$k] = $this->getTextRepresentationOfUsersOrgUnits($user_certificate_dto->getUserId());
                    break;
                case 'issuedOnTimestamp':
                    $field_values[$k] = new \ilDateTime($propGetter($k), IL_CAL_UNIX);
                    break;
                default:
                    $field_values[$k] = strip_tags($propGetter($k) ?? "");
                    break;
            }
        }

        return $field_values;
    }
}
