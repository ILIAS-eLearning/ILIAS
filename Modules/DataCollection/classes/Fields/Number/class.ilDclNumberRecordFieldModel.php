<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclNumberRecordFieldModel
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDclNumberRecordFieldModel extends ilDclBaseRecordFieldModel
{
    public function parseValue($value)
    {
        return ($value == '') ? null : $value; //SW, Ilias Mantis #0011799: Return null otherwise '' is casted to 0 in DB
    }
}
