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

use ILIAS\Data\URI;

/**
 * Immutable class for internal Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkItemInternal extends ilWebLinkItem
{
    public function isInternal(): bool
    {
        return true;
    }

    /**
     * TODO: should be refactored to use URI data object everywhere
     */
    public function getResolvedLink(bool $with_parameters = true): string
    {
        $parts = explode("|", $this->getTarget());
        $type = (string) ($parts[0] ?? '');
        $ref_id = (int) ($parts[1] ?? 0);

        switch ($type) {
            case 'wpage':
                $uri = new URI(ilLink::_getStaticLink(
                    0,
                    'wiki',
                    true
                ));
                $uri = $uri->withParameter('target', 'wiki_wpage_' . $ref_id);
                $link = (string) $uri;
                break;

            case 'term':
                // #16894
                $uri = new URI(ilLink::_getStaticLink(
                    0,
                    "git",
                    true
                ));
                $uri = $uri->withParameter('target', 'git_' . $ref_id);
                $link = (string) $uri;
                break;

            case 'page':
                $type = "pg";
                $link = ilLink::_getStaticLink($ref_id, $type);
                break;

            default:
                $link = ilLink::_getStaticLink($ref_id, $type);
        }

        if (!$with_parameters) {
            return $link;
        }

        foreach ($this->getParameters() as $parameter) {
            $link = $parameter->appendToLink($link);
        }

        return $link;
    }
}
