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

namespace ILIAS\Authentication\KeyValueStorage;

use ILIAS\KeyValueStorage\SubjectPurge;

/**
 * Resolves {@see AuthenticatedSubjectPurge} from the global DIC during legacy bootstrap.
 *
 * The user-deletion listener is not a component consumer, so this is the composition root
 * for that call.
 */
final readonly class DicAuthenticatedSubjectPurge
{
    public function get(): AuthenticatedSubjectPurge
    {
        global $DIC;

        /** @var SubjectPurge $subject_purge */
        $subject_purge = $DIC[SubjectPurge::class];

        return new AuthenticatedSubjectPurge($subject_purge);
    }
}
