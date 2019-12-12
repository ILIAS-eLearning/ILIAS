<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDclDatetimeRecordFieldModel extends ilDclBaseRecordFieldModel
{
    public function parseValue($value)
    {
        return $value;
    }


    /**
     * @inheritDoc
     */
    public function getValueFromExcel($excel, $row, $col)
    {
        assert($excel instanceof ilExcel);

        $value = parent::getValueFromExcel($excel, $row, $col);

        return date('Y-m-d', strtotime($value));
    }


    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseExportValue($value)
    {
        return substr($value, 0, 10);
    }


    /**
     * Returns sortable value for the specific field-types
     *
     * @param                           $value
     * @param ilDclBaseRecordFieldModel $record_field
     * @param bool|true                 $link
     *
     * @return int|string
     */
    public function parseSortingValue($value, $link = true)
    {
        return strtotime($value);
    }
}
