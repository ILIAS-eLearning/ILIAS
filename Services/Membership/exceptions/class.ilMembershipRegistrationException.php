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
}
