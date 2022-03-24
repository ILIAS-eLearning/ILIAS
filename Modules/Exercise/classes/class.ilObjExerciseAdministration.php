<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjExerciseAdministration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExerciseAdministration extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "excs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete() : bool
    {
        // DISABLED
        return false;
    }
}
