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

namespace ILIAS\FileUpload\Activities;

/**
 * The permission rule the file activities share: a file may only be handled by
 * a user the system knows, never by the anonymous user.
 *
 * This is the whole authorisation of the low level activities on purpose - they
 * work on temporary files that only their own handles address. Activities that
 * touch real objects have to check RBAC.
 */
final class AccessCheck
{
    public static function isKnownUser(int $usr_id): bool
    {
        if ($usr_id <= 0) {
            return false;
        }

        return !defined('ANONYMOUS_USER_ID') || $usr_id !== ANONYMOUS_USER_ID;
    }
}
