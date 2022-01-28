<?php declare(strict_types=1);/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * membership registration exception
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesMembership
 */
class ilMembershipRegistrationException extends ilException
{
    //Error Codes
    public const OBJECT_IS_FULL = 123;
    public const ADDED_TO_WAITINGLIST = 124;
    public const ADMISSION_LINK_INVALID = 125;
    public const REGISTRATION_CODE_DISABLED = 456;
    public const OUT_OF_REGISTRATION_PERIOD = 789;
    public const REGISTRATION_INVALID_OFFLINE = 126;
    public const REGISTRATION_INVALID_AVAILABILITY = 127;

    /**
     * Constructor
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
