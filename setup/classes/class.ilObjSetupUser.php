<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilObjUser.php';

/**
 * Class ilObjSetupUser
 * A class derived from ilObjUser for authentication purposes in the ILIAS setup
 */
class ilObjSetupUser extends ilObjUser
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Do not call the parent constructor here
    }
}
