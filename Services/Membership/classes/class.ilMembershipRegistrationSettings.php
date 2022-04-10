<?php declare(strict_types=1);
    
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
