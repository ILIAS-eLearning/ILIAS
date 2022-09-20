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

/**
 * Container field data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterFieldData
{
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    public function getFilterSetForRefId(int $ref_id): ilContainerFilterSet
    {
        $db = $this->db;

        $filter = [];
        $set = $db->queryF(
            "SELECT * FROM cont_filter_field " .
            " WHERE ref_id = %s ",
            ["integer"],
            [$ref_id]
        );
        while ($rec = $db->fetchAssoc($set)) {
            if ($rec["record_set_id"] > 0 && !ilAdvancedMDFieldDefinition::exists($rec["field_id"])) {
                continue;
            }
            $filter[] = [
                "field" => new ilContainerFilterField($rec["record_set_id"], $rec["field_id"]),
                "sort" => ($rec["record_set_id"] * 100000) + $rec["field_id"]];
        }
        $filter = ilArrayUtil::sortArray($filter, "sort", "asc", true);

        $filter = array_map(static function (array $i): ilContainerFilterField {
            return $i["field"];
        }, $filter);

        return new ilContainerFilterSet($filter);
    }

    public function saveFilterSetForRefId(int $ref_id, ilContainerFilterSet $set): void
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM cont_filter_field WHERE " .
            " ref_id = %s",
            ["integer"],
            [$ref_id]
        );

        foreach ($set->getFields() as $f) {
            $db->insert("cont_filter_field", [
                "ref_id" => ["integer", $ref_id],
                "record_set_id" => ["integer", $f->getRecordSetId()],
                "field_id" => ["integer", $f->getFieldId()]
            ]);
        }
    }
}
