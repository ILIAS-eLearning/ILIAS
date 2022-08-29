<?php

declare(strict_types=1);

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

/**
 * Immutable class for external Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkItemExternal extends ilWebLinkItem
{
    public function isInternal(): bool
    {
        return false;
    }

    public function getResolvedLink(bool $with_parameters = true): string
    {
        $link = $this->getTarget();

        if (!$with_parameters) {
            return $link;
        }

        foreach ($this->getParameters() as $parameter) {
            $link = $parameter->appendToLink($link);
        }

        return $link;
    }
}
