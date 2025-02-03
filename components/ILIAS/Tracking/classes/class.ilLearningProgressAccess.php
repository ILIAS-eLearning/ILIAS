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

declare(strict_types=0);
/**
 * Learning progress access checks
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLearningProgressAccess
{
    /**
     * wrapper for rbac access checks
     */
    public static function checkPermission(
        string $a_permission,
        int $a_ref_id,
        ?int $a_user_id = null
    ): bool {
        global $DIC;

        if ($a_user_id === null) {
            $a_user_id = $DIC->user()->getId();
        }

        // position access
        if ($a_permission === 'read_learning_progress') {
            return $DIC->access()->checkRbacOrPositionPermissionAccess(
                ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
                ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
                $a_ref_id
            );
        }
        return $DIC->access()->checkAccessOfUser(
            $a_user_id,
            $a_permission,
            '',
            $a_ref_id
        );
    }

    /**
     * check access to learning progress
     */
    public static function checkAccess(
        int $a_ref_id,
        bool $a_allow_only_read = true
    ): bool {
        global $DIC;

        if ($DIC->user()->getId() == ANONYMOUS_USER_ID) {
            return false;
        }

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return false;
        }

        $olp = ilObjectLP::getInstance(ilObject::_lookupObjId($a_ref_id));
        if (
            $olp->isActive() && (
                $DIC->access()->checkAccess(
                    'read_learning_progress',
                    '',
                    $a_ref_id
                ) ||
                $DIC->access()->checkRbacOrPositionPermissionAccess(
                    ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
                    ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
                    $a_ref_id
                )
            )
        ) {
            return true;
        }

        if (!$DIC->access()->checkAccess('read', '', $a_ref_id)) {
            return false;
        }
        // edit learning progress is sufficient: #0029313
        if ($DIC->access()->checkAccess(
            'edit_learning_progress',
            '',
            $a_ref_id
        )) {
            return true;
        }

        if (!ilObjUserTracking::_hasLearningProgressLearner()) {
            return false;
        }

        if (!$olp->isActive()) {
            return false;
        }

        if ($a_allow_only_read) {
            return true;
        }
        return false;
    }
}
