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

namespace ILIAS\Forum\Posting;

enum BindingDenial: string
{
    case INCOMPLETE_SELECTORS = 'incomplete_selectors';
    case UNKNOWN_POSTING = 'unknown_posting';
    case UNKNOWN_DRAFT = 'unknown_draft';
    case FOREIGN_FORUM = 'foreign_forum';
    case FOREIGN_THREAD = 'foreign_thread';
    case FOREIGN_POSTING = 'foreign_posting';
    case FOREIGN_AUTHOR = 'foreign_author';
    case BOUND_TO_THREAD = 'bound_to_thread';

    public function logMessage(): string
    {
        return match ($this) {
            self::INCOMPLETE_SELECTORS => 'The request did not address the forum object and the posting or draft',
            self::UNKNOWN_POSTING => 'Addressed posting does not exist',
            self::UNKNOWN_DRAFT => 'Addressed draft does not exist',
            self::FOREIGN_FORUM => 'Addressed posting or draft belongs to another forum',
            self::FOREIGN_THREAD => 'Addressed posting or draft belongs to another thread',
            self::FOREIGN_POSTING => 'Addressed draft replies to another posting',
            self::FOREIGN_AUTHOR => 'Addressed draft was written by another user',
            self::BOUND_TO_THREAD => 'Addressed draft is bound to a thread',
        };
    }
}
