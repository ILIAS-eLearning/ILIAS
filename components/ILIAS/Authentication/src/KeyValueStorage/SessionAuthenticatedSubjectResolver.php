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

use ILIAS\Authentication\Domain\AuthenticatedSubjectResolver;
use ILIAS\Authentication\Domain\AuthenticatedUser;
use ILIAS\KeyValueStorage\Subject\Subject;

/**
 * Maps {@see AuthenticatedUser::id()} onto a KeyValueStorage subject.
 */
final readonly class SessionAuthenticatedSubjectResolver implements AuthenticatedSubjectResolver
{
    public function __construct(
        private AuthenticatedUser $authenticated_user,
        private AuthenticatedUserSubjectProvider $provider
    ) {
    }

    public function subject(): Subject
    {
        $user_id = $this->authenticated_user->id();
        if ($user_id->isError()) {
            return Subject::anonymous();
        }

        $id = $user_id->value();
        if (!\is_int($id) || $id <= 0) {
            return Subject::anonymous();
        }

        return Subject::named($this->provider->subjectFor($id));
    }

    public function supportsPersistentStorage(): bool
    {
        return $this->subject()->isNamed();
    }
}
