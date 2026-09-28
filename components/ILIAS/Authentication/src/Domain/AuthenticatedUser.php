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

namespace ILIAS\Authentication\Domain;

use ILIAS\Data\Result;

/**
 * Query port for a fully authenticated ILIAS user in the current request.
 *
 * Anonymous sessions and missing authentication return {@see Result\Error}.
 * Other components consume this via `$use[AuthenticatedUser::class]`.
 */
interface AuthenticatedUser
{
    /** @return Result<int> Positive when a logged-in user is present. */
    public function id(): Result;
}
