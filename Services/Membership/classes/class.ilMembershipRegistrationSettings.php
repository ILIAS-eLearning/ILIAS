<?php declare(strict_types=1);/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Registration settings
 * Currently only some constants used in sessions (@todo course, groups)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesMembership
 */
abstract class ilMembershipRegistrationSettings
{
    public const TYPE_NONE = 0;
    public const TYPE_DIRECT = 1;
    public const TYPE_PASSWORD = 2;
    public const TYPE_REQUEST = 3;
    public const TYPE_TUTOR = 4;

    public const REGISTRATION_LINK = 5;

    public const REGISTRATION_LIMITED_DURATION = 6;
    public const REGISTRATION_LIMITED_USERS = 7;
}
