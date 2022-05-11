<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclBaseFieldModel
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 */
class ilDclBooleanRecordFieldModel extends ilDclBaseRecordFieldModel
{
    /**
     * @param int|string $value
     */
    public function parseValue($value) : int
    {
        return $value ? 1 : 0;
    }

    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     * @param int|string $value
     */
    public function parseExportValue($value) : int
    {
        return $value ? 1 : 0;
    }
}
