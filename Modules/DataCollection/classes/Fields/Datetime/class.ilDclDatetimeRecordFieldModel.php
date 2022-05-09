<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldModel
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 */
class ilDclDatetimeRecordFieldModel extends ilDclBaseRecordFieldModel
{
    /**
     * @param int|string $value
     */
    public function parseValue($value) : string
    {
        return $value;
    }

    public function getValueFromExcel(ilExcel $excel, int $row, int $col) : ?string
    {
        $value = parent::getValueFromExcel($excel, $row, $col);

        return date('Y-m-d', strtotime($value));
    }

    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     * @param string $value
     */
    public function parseExportValue($value) : ?string
    {
        return substr($value, 0, 10);
    }

    /**
     * Returns sortable value for the specific field-types
     * @param string $value
     */
    public function parseSortingValue($value, bool $link = true) : ?int
    {
        return strtotime($value);
    }
}
