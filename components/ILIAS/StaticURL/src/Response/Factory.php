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
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Factory
{
    public function __construct(
        private Context $context
    ) {
    }

    public function cannot(): CannotHandle
    {
        return new CannotHandle();
    }

    public function loginFirst(): MaybeCanHandlerAfterLogin|CannotReach
    {
        if ($this->context->isUserLoggedIn()) {
            return new CannotReach();
        }
        if (!$this->context->isUserLoggedIn() && !$this->context->isPublicSectionActive()) {
            return new CannotReach();
        }

        return new MaybeCanHandlerAfterLogin();
    }

    public function can(string $uri_path, bool $shift = false): CanHandleWithURIPath
    {
        return new CanHandleWithURIPath($uri_path, $shift ? 1 : 0);
    }
}
