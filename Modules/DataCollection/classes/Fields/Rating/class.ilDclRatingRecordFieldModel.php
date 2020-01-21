<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclRatingRecordFieldModel extends ilDclBaseRecordFieldModel
{

    /**
     * @var bool
     */
    protected $rated;
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


    public function addHiddenItemsToConfirmation(ilConfirmationGUI &$confirmation)
    {
        return;
    }


    /**
     * override the loadValue.
     */
    protected function loadValue()
    {
        // explicitly do nothing. we don't have to load the value as it is saved somewhere else.
    }


    /**
     * Set value for record field
     *
     * @param mixed $value
     * @param bool  $omit_parsing If true, does not parse the value and stores it in the given format
     */
    public function setValue($value, $omit_parsing = false)
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }


    public function doUpdate()
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }


    public function doRead()
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }


    /**
     * return Export values
     *
     * @return string
     */
    public function getExportValue()
    {
        $val = ilRating::getOverallRatingForObject($this->getRecord()->getId(), "dcl_record", $this->getField()->getId(), "dcl_field");

        return round($val["avg"], 1) . " (" . $val["cnt"] . ")";
    }


    /**
     * @return array
     */
    public function getValue()
    {
        return ilRating::getOverallRatingForObject($this->getRecord()->getId(), "dcl_record", $this->getField()->getId(), "dcl_field");
    }


    /**
     * delete
     */
    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "DELETE FROM il_rating WHERE " .
            "obj_id = " . $ilDB->quote((int) $this->getRecord()->getId(), "integer") . " AND " .
            "obj_type = " . $ilDB->quote("dcl_record", "text") . " AND " .
            "sub_obj_id = " . $ilDB->quote((int) $this->getField()->getId(), "integer") . " AND " .
            $ilDB->equals("sub_obj_type", "dcl_field", "text", true)
        );

        $query2 = "DELETE FROM il_dcl_record_field WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query2);
    }
}
