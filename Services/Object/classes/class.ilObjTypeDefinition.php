<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjTypeDefinition
 *
 * CLASS IS DEPRECATED
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjTypeDefinition extends ilObject
{
    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_id = 0, $a_call_by_reference = false)
    {
        parent::__construct($a_id, $a_call_by_reference);
        $this->type = "typ";
    }
}
