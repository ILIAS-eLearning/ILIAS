<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjBadgeAdministration
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBadgeAdministration extends ilObject
{
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "bdga";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function update()
    {
        if (!parent::update()) {
            return false;
        }

        return true;
    }
}
