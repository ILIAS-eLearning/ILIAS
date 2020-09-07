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
class ilDclBooleanRecordFieldModel extends ilDclBaseRecordFieldModel
{
    public function parseValue($value)
    {
        return $value ? 1 : 0;
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
        return $value ? 1 : 0;
    }
}
