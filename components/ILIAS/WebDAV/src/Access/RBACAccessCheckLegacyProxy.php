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

namespace ILIAS\WebDAV;

use ILIAS\WebDAV\Objects\Filter\Action;
use ILIAS\WebDAV\Objects\Type;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RBACAccessCheckLegacyProxy implements AccessCheck
{
    public function hasCurrentUserAccess(Action $action, ?int $ref_id = null): bool
    {
        if ($ref_id === null) {
            return false;
        }
        try {
            global $DIC;

            $access = $DIC->access();
            if ($access === null) {
                return false;
            }

            $user = $DIC->user();

            return $access->checkAccessOfUser($user->getId(), $action->value, '', $ref_id);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function canUserCreate(Type $type, ?int $ref_id = null): bool
    {
        if ($ref_id === null) {
            return false;
        }
        try {
            global $DIC;

            $access = $DIC->access();
            if ($access === null) {
                return false;
            }

            $user = $DIC->user();

            return $access->checkAccessOfUser($user->getId(), Action::CREATE->value . '_' . $type->value, '', $ref_id);
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
