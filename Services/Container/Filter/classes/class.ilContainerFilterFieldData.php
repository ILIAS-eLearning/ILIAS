<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container field data
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterFieldData
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Get filter for ref id
     *
     * @param int $ref_id
     * @return ilContainerFilterSet
     */
    public function getFilterSetForRefId(int $ref_id) : ilContainerFilterSet
    {
        $db = $this->db;

        $filter = [];
        $set = $db->queryF(
            "SELECT * FROM cont_filter_field " .
            " WHERE ref_id = %s ",
            array("integer"),
            array($ref_id)
            );
        while ($rec = $db->fetchAssoc($set)) {
            if ($rec["record_set_id"] > 0 && !ilAdvancedMDFieldDefinition::exists($rec["field_id"])) {
                continue;
            }
            $filter[] = [
                "field" => new ilContainerFilterField($rec["record_set_id"], $rec["field_id"]),
                "sort" => ($rec["record_set_id"] * 100000) + $rec["field_id"]];
        }
        $filter = ilUtil::sortArray($filter, "sort", "asc", true);

        $filter = array_map(function ($i) {
            return $i["field"];
        }, $filter);

        return new ilContainerFilterSet($filter);
    }

    /**
     * Save filter set for ref id
     * @param int $ref_id
     * @param ilContainerFilterSet $set
     */
    public function saveFilterSetForRefId(int $ref_id, ilContainerFilterSet $set)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM cont_filter_field WHERE " .
            " ref_id = %s",
            array("integer"),
            array($ref_id)
        );

        foreach ($set->getFields() as $f) {
            $db->insert("cont_filter_field", array(
                "ref_id" => array("integer", $ref_id),
                "record_set_id" => array("integer", $f->getRecordSetId()),
                "field_id" => array("integer", $f->getFieldId())
            ));
        }
    }
}
