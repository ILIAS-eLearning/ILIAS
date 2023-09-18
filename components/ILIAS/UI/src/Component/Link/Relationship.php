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

namespace ILIAS\UI\Component\Link;

/**
 * Note that not all valid values of the rel-attribute of anchor tags
 * are included here (see https://html.spec.whatwg.org/multipage/links.html#linkTypes),
 * as some of them are reserved for internal use by the KS.
 */
enum Relationship: string implements IsRelationship
{
    case ALTERNATE = 'alternate';
    case AUTHOR = 'author';
    case BOOKMARK = 'bookmark';
    case EXTERNAL = 'external';
    case LICENSE = 'license';
    case NOFOLLOW = 'nofollow';
    case NOOPENER = 'noopener';
    case NOREFERRER = 'noreferrer';
}
