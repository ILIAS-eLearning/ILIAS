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

namespace ILIAS\MetaData\Copyright;

use ILIAS\Data\URI;

class NullRepository implements RepositoryInterface
{
    public function getEntry(int $id): EntryInterface
    {
        return new NullEntry();
    }

    public function getAllEntries(): \Generator
    {
        yield from [];
    }

    public function getActiveEntries(): \Generator
    {
        yield from [];
    }

    public function getDefaultEntry(): EntryInterface
    {
        return new NullEntry();
    }

    public function deleteEntry(int $id): void
    {
    }

    public function createEntry(
        string $title,
        string $description = '',
        bool $is_outdated = false,
        string $full_name = '',
        ?URI $link = null,
        URI|string $image = '',
        string $alt_text = ''
    ): int {
        return 0;
    }

    public function updateEntry(
        int $id,
        string $title,
        string $description = '',
        bool $is_outdated = false,
        string $full_name = '',
        ?URI $link = null,
        URI|string $image = '',
        string $alt_text = ''
    ): void {
    }

    public function reorderEntries(int ...$ids): void
    {
    }
}
