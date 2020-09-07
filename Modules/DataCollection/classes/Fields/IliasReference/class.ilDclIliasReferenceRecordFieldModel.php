<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordFieldModel.php';
require_once './Services/Object/classes/class.ilObject2.php';

/**
 * Class ilDclIliasReferenceRecordFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclIliasReferenceRecordFieldModel extends ilDclBaseRecordFieldModel
{

    /**
     * @var int
     */
    protected $dcl_obj_id;


    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);

        $dclTable = ilDclCache::getTableCache($this->getField()->getTableId());
        $this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
    }


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


    /**
     * @inheritDoc
     */
    public function getValueForRepresentation()
    {
        $ref_id = $this->getValue();

        return ilObject2::_lookupTitle(ilObject2::_lookupObjectId($ref_id)) . ' [' . $ref_id . ']';
    }


    /**
     * @return int|string
     */
    public function getExportValue()
    {
        $link = ilLink::_getStaticLink($this->getValue());

        return $link;
    }
}
