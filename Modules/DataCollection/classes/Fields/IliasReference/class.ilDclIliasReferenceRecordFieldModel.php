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
 * Class ilDclIliasReferenceRecordFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclIliasReferenceRecordFieldModel extends ilDclBaseRecordFieldModel
{
    protected int $dcl_obj_id;

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);

        $dclTable = ilDclCache::getTableCache($this->getField()->getTableId());
        $this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
    }

    /**
     * @return false|object
     */
    public function getStatus()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $usr_id = $ilUser->getId();
        $obj_ref = $this->getValue();
        $obj_id = ilObject2::_lookupObjectId($obj_ref);
        $query
            = "  SELECT status_changed, status
                    FROM ut_lp_marks
                    WHERE usr_id = " . $usr_id . " AND obj_id = " . $obj_id;
        $result = $ilDB->query($query);

        return ($result->numRows() == 0) ? false : $result->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
    }

    public function getValueForRepresentation() : string
    {
        $ref_id = $this->getValue();

        return ilObject2::_lookupTitle(ilObject2::_lookupObjectId($ref_id)) . ' [' . $ref_id . ']';
    }

    public function getExportValue() : string
    {
        $link = ilLink::_getStaticLink($this->getValue());

        return $link;
    }
}
