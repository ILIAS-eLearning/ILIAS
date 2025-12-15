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

    /**
     * @description The current Handler cannot redirect the user to the target (e.g. due to missing permissions).
     */
    public function cannot(): CannotReach
    {
        return new CannotReach();
    }

    /**
     * @description The current Handler cannot handle the request (e.g. due to missing resource).
     */
    public function notFound(): CannotHandle
    {
        return new CannotHandle();
    }

    /**
     * @description The user needs to login first before the target may can be reached.
     */
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

    /**
     * @description Everything is fine, the target can be reached. Provide the URI path to it.
     */
    public function can(string $uri_path): CanHandleWithURIPath
    {
        return new CanHandleWithURIPath($uri_path);
    }
}
