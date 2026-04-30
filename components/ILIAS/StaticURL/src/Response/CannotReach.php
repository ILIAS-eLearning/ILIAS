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
 * Returned by a Handler when the target exists but the CURRENT (logged-in)
 * user has no permission to reach it.
 *
 * The HandlerService tries to walk up the repository tree from the Request's
 * ReferenceId and redirects to the first parent the user can read (and shows
 * the course/group join message via `reg_goto_parent_membership_info`). If
 * no readable parent is found, or the Request carries no ReferenceId, the
 * user is redirected to their Starting Point / Dashboard.
 *
 * @see \ILIAS\StaticURL\Response\Factory::cannotReach()
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CannotReach implements Response
{
    public function getURIPath(): ?string
    {
        return null;
    }

    public function targetCanBeReached(): bool
    {
        return true;
    }

    public function shift(): int
    {
        return 0;
    }

}
