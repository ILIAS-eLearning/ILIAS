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

final readonly class PostingPlacement
{
    public function __construct(
        public int $posting_id,
        public int $obj_id,
        public int $thread_id
    ) {
    }

    public function belongsToForumObject(int $obj_id): bool
    {
        return $obj_id > 0 && $this->obj_id === $obj_id;
    }

    public function belongsToThread(int $thread_id): bool
    {
        return $thread_id > 0 && $this->thread_id === $thread_id;
    }
}
