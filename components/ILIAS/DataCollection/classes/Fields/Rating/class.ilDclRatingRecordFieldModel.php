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

class ilDclRatingRecordFieldModel extends ilDclBaseRecordFieldModel
{
    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation): void
    {
    }

    protected function loadValue(): void
    {
    }

    public function setValue($value, bool $omit_parsing = false): void
    {
    }

    public function doUpdate(): void
    {
    }

    protected function doRead(): void
    {
    }

    public function getExportValue(): string
    {
        $val = ilRating::getOverallRatingForObject(
            $this->getRecord()->getId(),
            "dcl_record",
            (int) $this->getField()->getId(),
            "dcl_field"
        );

        return round($val["avg"], 1) . " (" . $val["cnt"] . ")";
    }

    public function getValue(): array
    {
        return ilRating::getOverallRatingForObject(
            $this->getRecord()->getId(),
            "dcl_record",
            (int) $this->getField()->getId(),
            "dcl_field"
        );
    }

    public function delete(): void
    {
        $this->db->manipulate(
            "DELETE FROM il_rating WHERE " .
            "obj_id = " . $this->db->quote($this->getRecord()->getId(), "integer") . " AND " .
            "obj_type = " . $this->db->quote("dcl_record", "text") . " AND " .
            "sub_obj_id = " . $this->db->quote((int) $this->getField()->getId(), "integer") . " AND " .
            $this->db->equals("sub_obj_type", "dcl_field", "text", true)
        );

        $query2 = "DELETE FROM il_dcl_record_field WHERE id = " . $this->db->quote($this->getId(), "integer");
        $this->db->manipulate($query2);
    }
}
