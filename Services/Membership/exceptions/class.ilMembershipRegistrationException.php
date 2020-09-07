<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Exceptions/classes/class.ilException.php';
/**
* membership registration exception
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
class ilMembershipRegistrationException extends ilException
{
    //Error Codes
    const OBJECT_IS_FULL = 123;
    const ADDED_TO_WAITINGLIST = 124;
    const ADMISSION_LINK_INVALID = 125;
    const REGISTRATION_CODE_DISABLED = 456;
    const OUT_OF_REGISTRATION_PERIOD = 789;
    const REGISTRATION_INVALID_OFFLINE = 126;
    const REGISTRATION_INVALID_AVAILABILITY = 127;

    /**
     * Constructor
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
