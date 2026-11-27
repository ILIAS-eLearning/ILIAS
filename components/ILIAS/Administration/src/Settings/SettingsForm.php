<?php

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

declare(strict_types=1);

namespace ILIAS\Administration\Settings;

/**
 * Settings forms of the administration which show settings of other components
 * (read only, with a link to edit them). The values correspond to the former
 * ilAdministrationSettingsFormHandler::FORM_* constants.
 */
enum SettingsForm: int
{
    case PRIVACY = 1;
    case SECURITY = 2;
    case LP = 4;
    case MAIL = 5;
    case COURSE = 6;
    case GROUP = 7;
    case REPOSITORY = 8;
    case LDAP = 9;
    case FORUM = 10;
    case ACCESSIBILITY = 11;
    case WSP = 12;
    case TAGGING = 13;
    case CERTIFICATE = 14;
    case META_COPYRIGHT = 15;
    case CALENDAR = 17;
    case PRG = 18;
}
