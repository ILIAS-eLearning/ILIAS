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
 * Returned by a Handler when it has successfully resolved the Request. The
 * HandlerService redirects the user to {@see self::getURIPath()} (optionally
 * shifting one segment off the base URI first, see {@see self::shift()}).
 *
 * @see \ILIAS\StaticURL\Response\Factory::can()
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CanHandleWithURIPath implements Response
{
    public function __construct(
        private string $uri_path,
        private int $shift = 0
    ) {
    }

    public function getURIPath(): ?string
    {
        return $this->uri_path;
    }

    public function targetCanBeReached(): bool
    {
        return true;
    }

    public function shift(): int
    {
        return $this->shift;
    }

}
