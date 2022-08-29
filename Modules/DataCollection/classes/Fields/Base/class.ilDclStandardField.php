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

/**
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclStandardField extends ilDclBaseFieldModel
{
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function doRead(): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $message = "Standard fields cannot be read from DB";
        $this->main_tpl->setOnScreenMessage('failure', $message);
        $ilLog->write("[ilDclStandardField] " . $message);
    }

    public function doCreate(): void
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $message = "Standard fields cannot be written to DB";
        $this->main_tpl->setOnScreenMessage('failure', $message);
        $ilLog->write("[ilDclStandardField] " . $message);
    }

    public function doUpdate(): void
    {
        $this->updateTableFieldSetting();
    }

    public function clone(ilDclStandardField $original_record): void
    {
        $this->setOrder($original_record->getOrder());
        $this->setUnique($original_record->isUnique());
        $this->setExportable($original_record->getExportable());

        $this->doUpdate();
    }

    public function getLocked(): bool
    {
        return true;
    }

    public static function _getStandardFieldsAsArray(): array
    {
        //TODO: this isn't particularly pretty especially as $lng is used in the model. On the long run the standard fields should be refactored into "normal" fields.
        global $DIC;
        $lng = $DIC['lng'];
        $stdfields = array(
            array(
                "id" => "id",
                "title" => $lng->txt("dcl_id"),
                "description" => $lng->txt("dcl_id_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_NUMBER
            ),
            array(
                "id" => "create_date",
                "title" => $lng->txt("dcl_creation_date"),
                "description" => $lng->txt("dcl_creation_date_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_DATETIME
            ),
            array(
                "id" => "last_update",
                "title" => $lng->txt("dcl_last_update"),
                "description" => $lng->txt("dcl_last_update_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_DATETIME
            ),
            array(
                "id" => "owner",
                "title" => $lng->txt("dcl_owner"),
                "description" => $lng->txt("dcl_owner_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_TEXT
            ),
            array(
                "id" => "last_edit_by",
                "title" => $lng->txt("dcl_last_edited_by"),
                "description" => $lng->txt("dcl_last_edited_by_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_TEXT
            ),
            array(
                'id' => 'comments',
                'title' => $lng->txt('dcl_comments'),
                'description' => $lng->txt('dcl_comments_desc'),
                'datatype_id' => ilDclDatatype::INPUTFORMAT_NONE
            ),
        );

        return $stdfields;
    }

    public static function _getStandardFields(int $table_id): array
    {
        $stdFields = array();
        foreach (self::_getStandardFieldsAsArray() as $array) {
            $array["table_id"] = $table_id;
            $field = new ilDclStandardField();
            $field->buildFromDBRecord($array);
            $stdFields[] = $field;
        }

        return $stdFields;
    }

    /**
     * @return array all possible titles of non-importable (excel import) standardfields (atm all
     *               except owner), in all languages;
     */
    public static function _getNonImportableStandardFieldTitles(): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $identifiers = '';
        foreach (
            array(
                'dcl_id',
                'dcl_creation_date',
                'dcl_last_update',
                'dcl_last_edited_by',
                'dcl_comments',
            ) as $id
        ) {
            $identifiers .= $ilDB->quote($id, 'text') . ',';
        }
        $identifiers = rtrim($identifiers, ',');
        $sql = $ilDB->query(
            'SELECT value FROM lng_data WHERE identifier IN (' . $identifiers
            . ')'
        );
        $titles = array();
        while ($rec = $ilDB->fetchAssoc($sql)) {
            $titles[] = $rec['value'];
        }

        return $titles;
    }

    /**
     * @return array all possible titles of importable (excel import) standardfields (atm
     *               exclusively owner), in all languages;
     */
    public static function _getImportableStandardFieldTitle(): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $identifiers = '';
        foreach (array('dcl_owner') as $id) {
            $identifiers .= $ilDB->quote($id, 'text') . ',';
        }
        $identifiers = rtrim($identifiers, ',');
        $sql = $ilDB->query(
            'SELECT value, identifier FROM lng_data WHERE identifier IN ('
            . $identifiers . ')'
        );
        $titles = array();
        while ($rec = $ilDB->fetchAssoc($sql)) {
            $titles[$rec['identifier']][] = $rec['value'];
        }

        return $titles;
    }

    /**
     * @param int|string $field_id
     * @return bool
     */
    public static function _isStandardField($field_id): bool
    {
        $return = false;
        foreach (self::_getStandardFieldsAsArray() as $field) {
            if ($field["id"] == $field_id) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * gives you the datatype id of a specified standard field.
     * @param string $id the id of the standardfield eg. "create_date"
     */
    public static function _getDatatypeForId(string $id): ?string
    {
        $datatype = null;
        foreach (self::_getStandardFieldsAsArray() as $fields_data) {
            if ($id == $fields_data['id']) {
                $datatype = $fields_data['datatype_id'];
                break;
            }
        }

        return $datatype;
    }

    public function isStandardField(): bool
    {
        return true;
    }

    public function isUnique(): bool
    {
        return false;
    }

    /**
     * Returns a query-object for building the record-loader-sql-query
     */
    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ): ?ilDclRecordQueryObject {
        $sql_obj = new ilDclRecordQueryObject();

        $join_str = "";
        if ($this->getId() == 'owner' || $this->getId() == 'last_edit_by') {
            $join_str = "LEFT JOIN usr_data AS sort_usr_data_{$this->getId()} ON (sort_usr_data_{$this->getId()}.usr_id = record.{$this->getId()})";
            $select_str = " sort_usr_data_{$this->getId()}.login AS field_{$this->getId()},";
        } else {
            $select_str = " record.{$this->getId()} AS field_{$this->getId()},";
        }

        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);

        if ($this->getId() !== "comments") {
            $sql_obj->setOrderStatement("field_{$this->getId()} " . $direction);
        }

        return $sql_obj;
    }

    /**
     * Returns a query-object for building the record-loader-sql-query
     */
    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $where_additions = "";
        $join_str = "";
        if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_TEXT) {
            $join_str = "INNER JOIN usr_data AS filter_usr_data_{$this->getId()} ON (filter_usr_data_{$this->getId()}.usr_id = record.{$this->getId()} AND filter_usr_data_{$this->getId()}.login LIKE "
                . $ilDB->quote("%$filter_value%", 'text') . ") ";
        } else {
            if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_NUMBER) {
                $from = (isset($filter_value['from'])) ? $filter_value['from'] : null;
                $to = (isset($filter_value['to'])) ? $filter_value['to'] : null;
                if (is_numeric($from)) {
                    $where_additions .= " AND record.{$this->getId()} >= "
                        . $ilDB->quote($from, 'integer');
                }
                if (is_numeric($to)) {
                    $where_additions .= " AND record.{$this->getId()} <= "
                        . $ilDB->quote($to, 'integer');
                }
            } else {
                if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_DATETIME) {
                    $date_from = (isset($filter_value['from'])
                        && is_object($filter_value['from'])) ? $filter_value['from'] : null;
                    $date_to = (isset($filter_value['to'])
                        && is_object($filter_value['to'])) ? $filter_value['to'] : null;

                    // db->quote(.. date) at some point invokes ilDate->_toString, which adds a <br /> to the string,
                    // that's why strip_tags is used
                    if ($date_from) {
                        $where_additions .= " AND (record.{$this->getId()} >= "
                            . strip_tags($ilDB->quote($date_from, 'date')) . ")";
                    }
                    if ($date_to) {
                        $where_additions .= " AND (record.{$this->getId()} <= "
                            . strip_tags($ilDB->quote($date_to, 'date')) . ")";
                    }
                }
            }
        }

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setWhereStatement($where_additions);

        return $sql_obj;
    }

    public function getSortField(): string
    {
        if ($this->getId() == 'comments') {
            return 'n_comments';
        } else {
            return $this->getTitle();
        }
    }

    public function hasNumericSorting(): bool
    {
        if ($this->getId() == 'comments') {
            return true;
        }

        return parent::hasNumericSorting();
    }

    public function allowFilterInListView(): bool
    {
        //comments are filterable if they are enabled in the tables settings
        return $this->id != 'comments'
            || ilDclCache::getTableCache($this->getTableId())->getPublicCommentsEnabled();
    }

    public function fillHeaderExcel(ilExcel $worksheet, int &$row, int &$col): void
    {
        parent::fillHeaderExcel($worksheet, $row, $col);
        if ($this->getId() == 'owner') {
            global $DIC;
            $lng = $DIC['lng'];
            $worksheet->setCell($row, $col, $lng->txt("dcl_owner_name"));
            $col++;
        }
    }

    /**
     * @return string|int
     */
    public function getValueFromExcel(ilExcel $excel, int $row, int $col)
    {
        $value = $excel->getCell($row, $col);
        switch ($this->id) {
            case 'owner':
                return ilObjUser::_lookupId($value);
            default:
                return $value;
        }
    }

    /**
     * @param $records
     */
    public function afterClone($records)
    {
    }
}
