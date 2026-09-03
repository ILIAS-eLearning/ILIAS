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

namespace ILIAS\Forum\Files\Access;

enum DenialReason: string
{
    case INCOMPLETE_SELECTORS = 'incomplete_selectors';
    case UNKNOWN_CONTAINER = 'unknown_container';
    case FOREIGN_FORUM = 'foreign_forum';
    case FOREIGN_THREAD = 'foreign_thread';
    case FOREIGN_AUTHOR = 'foreign_author';

    public function logMessage(): string
    {
        return match ($this) {
            self::INCOMPLETE_SELECTORS => 'Attachment request did not address both a forum object and a container',
            self::UNKNOWN_CONTAINER => 'Addressed attachment container does not exist',
            self::FOREIGN_FORUM => 'Addressed attachment container is owned by another forum object',
            self::FOREIGN_THREAD => 'Addressed attachment container belongs to another thread',
            self::FOREIGN_AUTHOR => 'Addressed attachment container was authored by another user',
        };
    }
}
