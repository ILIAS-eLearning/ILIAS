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

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilDclFileFieldModel extends ilDclBaseFieldModel
{
    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ): ?ilDclRecordQueryObject {
        $join_str = "LEFT JOIN il_dcl_record_field AS sort_record_field_{$this->getId()} ON (sort_record_field_{$this->getId()}.record_id = record.id AND sort_record_field_{$this->getId()}.field_id = "
            . $this->db->quote($this->getId(), 'integer') . ") ";
        $join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS sort_stloc_{$this->getId()} ON (sort_stloc_{$this->getId()}.record_field_id = sort_record_field_{$this->getId()}.id) ";
        $join_str .= "LEFT JOIN il_resource_revision AS sort_object_data_{$this->getId()} ON (sort_object_data_{$this->getId()}.rid = sort_stloc_{$this->getId()}.value) ";
        $select_str = " sort_object_data_{$this->getId()}.title AS field_{$this->getId()},";

        $record_query = new ilDclRecordQueryObject();
        $record_query->setSelectStatement($select_str);
        $record_query->setJoinStatement($join_str);
        $record_query->setOrderStatement("field_{$this->getId()} " . $direction);

        return $record_query;
    }

    public function allowFilterInListView(): bool
    {
        return false;
    }

    public function getValidFieldProperties(): array
    {
        return [ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES];
    }

    public function getSupportedExtensions(): array
    {
        if (!$this->hasProperty(ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES)) {
            return [];
        }

        $file_types = $this->getProperty(ilDclBaseFieldModel::PROP_SUPPORTED_FILE_TYPES);

        return $this->parseSupportedExtensions($file_types);
    }

    protected function parseSupportedExtensions(string $input_value): array
    {
        $supported_extensions = explode(",", $input_value);

        $trim_function = function ($value) {
            return trim(trim(strtolower($value)), ".");
        };

        return array_map($trim_function, $supported_extensions);
    }
}
