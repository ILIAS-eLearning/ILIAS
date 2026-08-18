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
 *********************************************************************/

declare(strict_types=1);

class ilDclStandardField extends ilDclBaseFieldModel
{
    public function doRead(): void
    {
    }

    public function doCreate(): void
    {
    }

    public function doUpdate(): void
    {
        $this->updateTableFieldSetting();
    }

    public function clone(ilDclStandardField $original_record): void
    {
        $this->setOrder($original_record->getOrder());
        $this->setExportable($original_record->getExportable());
        $this->doUpdate();
    }

    public function getLocked(): bool
    {
        return true;
    }

    public static function _getStandardFieldsAsArray(): array
    {
        global $DIC;
        $lng = $DIC->language();
        return [
            "id" => [
                "id" => "id",
                "title" => $lng->txt("dcl_id"),
                "description" => $lng->txt("dcl_id_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_NUMBER
            ],
            "create_date" => [
                "id" => "create_date",
                "title" => $lng->txt("dcl_creation_date"),
                "description" => $lng->txt("dcl_creation_date_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_DATETIME
            ],
            "last_update" => [
                "id" => "last_update",
                "title" => $lng->txt("dcl_last_update"),
                "description" => $lng->txt("dcl_last_update_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_DATETIME
            ],
            "owner" => [
                "id" => "owner",
                "title" => $lng->txt("dcl_owner"),
                "description" => $lng->txt("dcl_owner_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_TEXT
            ],
            "last_edit_by" => [
                "id" => "last_edit_by",
                "title" => $lng->txt("dcl_last_edited_by"),
                "description" => $lng->txt("dcl_last_edited_by_description"),
                "datatype_id" => ilDclDatatype::INPUTFORMAT_TEXT
            ],
            'comments' => [
                'id' => 'comments',
                'title' => $lng->txt('dcl_comments'),
                'description' => $lng->txt('dcl_comments_desc'),
                'datatype_id' => ilDclDatatype::INPUTFORMAT_TEXT
            ],
        ];
    }

    public static function _getStandardFields(int $table_id): array
    {
        $stdFields = [];
        foreach (self::_getStandardFieldsAsArray() as $array) {
            $array["table_id"] = $table_id;
            $field = new ilDclStandardField();
            $field->buildFromDBRecord($array);
            $stdFields[] = $field;
        }

        return $stdFields;
    }

    public static function _getNonImportableStandardFieldTitles(): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $identifiers = '';
        foreach (
            [
                'dcl_id',
                'dcl_creation_date',
                'dcl_last_update',
                'dcl_last_edited_by',
                'dcl_comments',
            ] as $id
        ) {
            $identifiers .= $ilDB->quote($id, 'text') . ',';
        }
        $identifiers = rtrim($identifiers, ',');
        $sql = $ilDB->query(
            'SELECT value FROM lng_data WHERE identifier IN (' . $identifiers
            . ')'
        );
        $titles = [];
        while ($rec = $ilDB->fetchAssoc($sql)) {
            $titles[] = $rec['value'];
        }

        return $titles;
    }

    public static function _getImportableStandardFieldTitle(): array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $identifiers = '';
        $id = 'dcl_owner';
        $identifiers .= $ilDB->quote($id, 'text') . ',';
        $identifiers = rtrim($identifiers, ',');
        $sql = $ilDB->query(
            'SELECT value, identifier FROM lng_data WHERE identifier IN ('
            . $identifiers . ')'
        );
        $titles = [];
        while ($rec = $ilDB->fetchAssoc($sql)) {
            $titles[$rec['identifier']][] = $rec['value'];
        }

        return $titles;
    }

    public static function _isStandardField(mixed $field_id): bool
    {
        $return = false;
        foreach (self::_getStandardFieldsAsArray() as $field) {
            if ($field["id"] == $field_id) {
                $return = true;
            }
        }

        return $return;
    }

    public static function _getDatatypeForId(string $id): ?int
    {
        return self::_getStandardFieldsAsArray()[$id]['datatype_id'];
    }

    public function isStandardField(): bool
    {
        return true;
    }

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

    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {

        $where_additions = "";
        $join_str = "";
        if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_TEXT) {
            $join_str = "INNER JOIN usr_data AS filter_usr_data_{$this->getId()} ON (filter_usr_data_{$this->getId()}.usr_id = record.{$this->getId()} AND filter_usr_data_{$this->getId()}.login LIKE "
                . $this->db->quote("%$filter_value%", 'text') . ") ";
        } else {
            if ($this->getDatatypeId() == ilDclDatatype::INPUTFORMAT_NUMBER) {
                $from = (isset($filter_value['from'])) ? $filter_value['from'] : null;
                $to = (isset($filter_value['to'])) ? $filter_value['to'] : null;
                if (is_numeric($from)) {
                    $where_additions .= " AND record.{$this->getId()} >= "
                        . $this->db->quote($from, 'integer');
                }
                if (is_numeric($to)) {
                    $where_additions .= " AND record.{$this->getId()} <= "
                        . $this->db->quote($to, 'integer');
                }
            } else {
                if ($this->getDatatypeId() === ilDclDatatype::INPUTFORMAT_DATETIME) {
                    $date_from = (isset($filter_value['from'])
                        && is_object($filter_value['from'])) ? $filter_value['from'] : null;
                    $date_to = (isset($filter_value['to'])
                        && is_object($filter_value['to'])) ? $filter_value['to'] : null;

                    if ($date_from) {
                        $where_additions .= " AND (record.{$this->getId()} >= "
                            . strip_tags($this->db->quote($date_from, 'date')) . ")";
                    }
                    if ($date_to) {
                        $where_additions .= " AND (record.{$this->getId()} <= "
                            . strip_tags($this->db->quote($date_to, 'date')) . ")";
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
        return $this->id != 'comments'
            || ilDclCache::getTableCache($this->getTableId())->getPublicCommentsEnabled();
    }

    public function fillHeaderExcel(ilExcel $worksheet, int &$row, int &$col): void
    {
        parent::fillHeaderExcel($worksheet, $row, $col);
        if ($this->getId() == 'owner') {
            global $DIC;
            $lng = $DIC->language();
            $worksheet->setCell($row, $col, $lng->txt("dcl_owner_name"));
            $col++;
        }
    }

    public function getValueFromExcel(ilExcel $excel, int $row, int $col): string
    {
        $value = $excel->getCell($row, $col);
        switch ($this->id) {
            case 'owner':
                return (string) ilObjUser::_lookupId($value);
            default:
                return $value;
        }
    }

    public function afterClone(array $records): void
    {
    }
}
