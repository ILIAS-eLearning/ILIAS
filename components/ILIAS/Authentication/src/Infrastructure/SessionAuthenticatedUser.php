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

namespace ILIAS\Authentication\Infrastructure;

use ILIAS\Authentication\Domain\AuthenticatedUser;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result\Ok;

/**
 * Answers the query from the legacy session authentication.
 */
final readonly class SessionAuthenticatedUser implements AuthenticatedUser
{
    public function __construct(private \ilAuthSession $auth_session)
    {
    }

    public function id(): Result
    {
        if ($this->auth_session->isFullyAuthenticated()) {
            return new Ok($this->auth_session->getUserId());
        }

        if ($this->auth_session->isAnonymouslyAuthenticated()) {
            return new Error('Anonymous session actor is not an authenticated user.');
        }

        return new Error('No authenticated user in session.');
    }
}
