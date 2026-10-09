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

use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectProvider;

/**
 * Names the fully authenticated ILIAS users as KeyValueStorage subjects; the
 * subject id is the user id.
 *
 * This names a subject. It does not discover who is logged in; that answer is
 * {@see \ILIAS\Authentication\Domain\AuthenticatedUser::id()}.
 */
final readonly class AuthenticatedUserSubjectProvider implements SubjectProvider
{
    public const string NAME = 'authentication';

    public function name(): string
    {
        return self::NAME;
    }

    public function subjectFor(int $user_id): SubjectId
    {
        if ($user_id <= 0) {
            throw new \InvalidArgumentException('User ID must be positive, got ' . $user_id . '.');
        }

        return new SubjectId($this, (string) $user_id);
    }
}
