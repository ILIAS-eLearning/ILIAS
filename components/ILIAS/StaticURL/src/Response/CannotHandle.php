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

namespace ILIAS\StaticURL\Response;

/**
 * Returned by a Handler when it cannot process the given Request at all,
 * e.g. because the Request is malformed or references an unknown sub-target.
 *
 * This is a programmer/contract-level signal; it does NOT mean that the
 * current user merely lacks permission (use {@see CannotReach} or
 * {@see MaybeCanHandlerAfterLogin} for that).
 *
 * The HandlerService will respond with HTTP 404.
 *
 * @see \ILIAS\StaticURL\Response\Factory::cannot()
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CannotHandle implements Response
{
    public function getURIPath(): ?string
    {
        return null;
    }

    public function targetCanBeReached(): bool
    {
        return false;
    }

    public function shift(): int
    {
        return 0;
    }

}
