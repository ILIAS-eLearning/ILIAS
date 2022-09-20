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
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclRatingRecordFieldModel extends ilDclBaseRecordFieldModel
{
    protected int $dcl_obj_id;

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);

        $dclTable = ilDclCache::getTableCache($this->getField()->getTableId());
        $this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation): void
    {
    }

    /**
     * override the loadValue.
     */
    protected function loadValue(): void
    {
        // explicitly do nothing. we don't have to load the value as it is saved somewhere else.
    }

    /**
     * Set value for record field
     * @param mixed $value
     * @param bool  $omit_parsing If true, does not parse the value and stores it in the given format
     */
    public function setValue($value, bool $omit_parsing = false): void
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }

    public function doUpdate(): void
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }

    protected function doRead(): void
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }

    /**
     * return Export values
     * @return string
     */
    public function getExportValue(): string
    {
        $val = ilRating::getOverallRatingForObject(
            $this->getRecord()->getId(),
            "dcl_record",
            $this->getField()->getId(),
            "dcl_field"
        );

        return round($val["avg"], 1) . " (" . $val["cnt"] . ")";
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return ilRating::getOverallRatingForObject(
            $this->getRecord()->getId(),
            "dcl_record",
            $this->getField()->getId(),
            "dcl_field"
        );
    }

    /**
     * delete
     */
    public function delete(): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "DELETE FROM il_rating WHERE " .
            "obj_id = " . $ilDB->quote($this->getRecord()->getId(), "integer") . " AND " .
            "obj_type = " . $ilDB->quote("dcl_record", "text") . " AND " .
            "sub_obj_id = " . $ilDB->quote((int) $this->getField()->getId(), "integer") . " AND " .
            $ilDB->equals("sub_obj_type", "dcl_field", "text", true)
        );

        $query2 = "DELETE FROM il_dcl_record_field WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query2);
    }
}
