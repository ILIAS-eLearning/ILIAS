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

namespace ILIAS\Test\Access;

use ILIAS\Language\Language;

enum ParticipantAccess: string
{
    case ALLOWED = 'access_granted';
    case NOT_INVITED = 'tst_user_not_invited';
    case INDIVIDUAL_CLIENT_IP_MISMATCH = 'individual_client_ip_mismatch';
    case TEST_LEVEL_CLIENT_IP_MISMATCH = 'test_level_client_ip_mismatch';
    case BROKEN_TEST = 'object_is_broken';

    public function getAccessForbiddenMessage(Language $lng): string
    {
        return match($this) {
            self::NOT_INVITED => $lng->txt('tst_user_not_invited'),
            self::INDIVIDUAL_CLIENT_IP_MISMATCH,
            self::TEST_LEVEL_CLIENT_IP_MISMATCH => $lng->txt('user_ip_outside_range'),
            self::BROKEN_TEST => $lng->txt('broken_test'),
            default => ''
        };
    }
}
