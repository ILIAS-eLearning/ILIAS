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

class ilDclIliasReferenceRecordFieldModel extends ilDclBaseRecordFieldModel
{
    protected int $dcl_obj_id;

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);

        $dclTable = ilDclCache::getTableCache($this->getField()->getTableId());
        $this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
    }

    public function getStatus(): ?stdClass
    {
        $obj_ref = $this->getValue();
        if (!$obj_ref) {
            return null;
        }
        $usr_id = $this->user->getId();
        $obj_id = ilObject2::_lookupObjectId($obj_ref);
        $query = "  SELECT status_changed, status
                    FROM ut_lp_marks
                    WHERE usr_id = " . $usr_id . " AND obj_id = " . $obj_id;

        return $this->db->fetchObject($this->db->query($query));
    }

    public function getValueForRepresentation(): string
    {
        $ref_id = $this->getValue();

        if ($ref_id) {
            return ilObject2::_lookupTitle(ilObject2::_lookupObjectId($ref_id)) . ' [' . $ref_id . ']';
        } else {
            return "";
        }
    }

    public function getExportValue(): string
    {
        return ilLink::_getStaticLink((int)$this->getValue());
    }
}
