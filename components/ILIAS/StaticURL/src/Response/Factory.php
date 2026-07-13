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

use ILIAS\StaticURL\Context;

/**
 * Produces {@see Response} objects for {@see \ILIAS\StaticURL\Handler\Handler}
 * implementations. Each factory method documents how the
 * {@see \ILIAS\StaticURL\Handler\HandlerService} will react when the Handler
 * returns that Response.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Factory
{
    public function __construct(
        private Context $context
    ) {
    }

    /**
     * The Handler cannot process the given Request at all, e.g. because the
     * Request is malformed or references an unknown sub-target. This is a
     * programmer/contract-level signal; it does NOT mean that the current
     * user merely lacks permission (use {@see cannotReach()} or
     * {@see loginFirst()} for that).
     *
     * The HandlerService will respond with HTTP 404.
     */
    public function cannot(): CannotHandle
    {
        return new CannotHandle();
    }

    /**
     * The target exists, but the CURRENT user has no permission to reach it.
     *
     * If the {@see \ILIAS\StaticURL\Request\Request} carries a ReferenceId,
     * the HandlerService walks up the repository tree and redirects to the
     * first parent the user can read (and displays the course/group join
     * message). If no readable parent is found, or no ReferenceId is given,
     * the user is redirected to their Starting Point / Dashboard.
     */
    public function cannotReach(): CannotReach
    {
        return new CannotReach();
    }

    /**
     * Convenience: the Handler cannot serve the target with the current
     * permissions. If the user is ANONYMOUS, returns
     * {@see MaybeCanHandlerAfterLogin} (the HandlerService redirects to
     * login.php with the original target preserved). If the user is already
     * LOGGED IN, returns {@see CannotReach} (see {@see cannotReach()} for
     * the parent-fallback behaviour).
     */
    public function loginFirst(): MaybeCanHandlerAfterLogin|CannotReach
    {
        if ($this->context->isUserLoggedIn()) {
            return $this->cannotReach();
        }

        return new MaybeCanHandlerAfterLogin();
    }

    /**
     * The Handler successfully resolved the target. The HandlerService will
     * redirect the user to the given URI path. Set $shift to true when the
     * returned path is relative to the parent of the StaticURL base URI.
     */
    public function can(string $uri_path, bool $shift = false): CanHandleWithURIPath
    {
        return new CanHandleWithURIPath($uri_path, $shift ? 1 : 0);
    }
}
