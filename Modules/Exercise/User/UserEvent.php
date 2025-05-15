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

namespace ILIAS\Exercise\User;

class UserEvent
{
    public function __construct(
        protected \ILIAS\Exercise\InternalRepoService $repo,
        protected \ILIAS\Exercise\InternalDomainService $domain
    ) {
    }

    public function handleDeletion(int $user_id): void
    {
        global $DIC;

        // get all exercises the user has submitted to
        // todo move to repo layer
        $db = $DIC->database();
        $set = $db->queryF(
            "SELECT DISTINCT obj_id FROM exc_returned " .
            " WHERE user_id = %s ",
            ["integer"],
            [$user_id]
        );
        // remove all user submissions from these exercises
        while ($rec = $db->fetchAssoc($set)) {
            $exc_id = (int) $rec['obj_id'];
            \ilExSubmission::deleteUser($exc_id, $user_id);
        }
    }
}
