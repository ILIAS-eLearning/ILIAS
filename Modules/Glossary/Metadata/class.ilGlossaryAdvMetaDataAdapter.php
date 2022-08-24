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
 * Advanced meta data adapter
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryAdvMetaDataAdapter
{
    protected int $glo_ref_id;
    protected int $glo_id;
    protected ilDBInterface $db;
    protected ilLanguage $lng;

    public function __construct(
        int $a_glo_ref_id
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->glo_id = ilObject::_lookupObjectId($a_glo_ref_id);
        $this->glo_ref_id = $a_glo_ref_id;
    }


    /**
     * Get all advanced metadata fields
     */
    public function getAllFields(): array
    {
        $fields = array();
        $recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("glo", $this->glo_ref_id, "term");

        foreach ($recs as $record_obj) {
            foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_obj->getRecordId()) as $def) {
                $fields[$def->getFieldId()] = array(
                    "id" => $def->getFieldId(),
                    "title" => $def->getTitle(),
                    "type" => $def->getType()
                    );
            }
        }

        return $fields;
    }

    /**
     * Get column order
     */
    public function getColumnOrder(): array
    {
        $ilDB = $this->db;
        $lng = $this->lng;

        $columns = array();

        $set = $ilDB->query(
            "SELECT * FROM glo_advmd_col_order " .
                " WHERE glo_id = " . $ilDB->quote($this->glo_id, "integer") .
                " ORDER BY order_nr"
        );
        $order = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $order[$rec["field_id"]] = $rec["order_nr"];
        }
        //var_dump($order);
        // add term at beginning, if not included
        if (!isset($order[0])) {
            $columns[] = array("id" => 0,
                "text" => $lng->txt("cont_term"));
        }

        $fields = $this->getAllFields();

        // add all fields that have been already sorted
        foreach ($order as $id => $order_nr) {
            if (isset($fields[$id])) {
                $columns[] = array("id" => $id,
                    "text" => $fields[$id]["title"]);
                unset($fields[$id]);
            } elseif ($id == 0) {
                $columns[] = array("id" => 0,
                    "text" => $lng->txt("cont_term"));
            }
        }

        // add all fields that have not been sorted
        foreach ($fields as $f) {
            $columns[] = array("id" => $f["id"],
                "text" => $f["title"]);
        }

        return $columns;
    }

    /**
     * Save column order
     */
    public function saveColumnOrder(array $a_cols): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM glo_advmd_col_order WHERE " .
            " glo_id = " . $ilDB->quote($this->glo_id, "integer")
        );

        $nr = 10;
        $set = array();
        foreach ($a_cols as $c) {
            //var_dump($c);
            if (!isset($set[$c["id"]])) {
                $ilDB->manipulate("INSERT INTO glo_advmd_col_order " .
                        "(glo_id, field_id, order_nr) VALUES (" .
                        $ilDB->quote($this->glo_id, "integer") . "," .
                        $ilDB->quote($c["id"], "integer") . "," .
                        $ilDB->quote($nr += 10, "integer") .
                        ")");
                $set[$c["id"]] = $c["id"];
            }
        }
    }

    /**
     * Write single column order
     */
    public static function writeColumnOrder(
        int $a_glo_id,
        int $a_field_id,
        int $a_order_nr
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->replace(
            "glo_advmd_col_order",
            array("glo_id" => array("integer", $a_glo_id),
                "field_id" => array("integer", $a_field_id)),
            array("order_nr" => array("integer", $a_order_nr))
        );
    }
}
