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

interface RepositoryInterface
{
    public function getEntry(int $id): EntryInterface;

    /**
     * The default entry is returned first, and the remaining
     * entries are returned according to their position.
     */
    public function getAllEntries(): \Generator;

    /**
     * The default entry is returned first, and the remaining
     * entries are returned according to their position.
     * Outdated entries are skipped.
     */
    public function getActiveEntries(): \Generator;

    public function getDefaultEntry(): EntryInterface;

    public function deleteEntry(int $id): void;

    /**
     * Returns the ID of the newly created entry.
     */
    public function createEntry(
        string $title,
        string $description = '',
        bool $is_outdated = false,
        string $full_name = '',
        ?URI $link = null,
        URI|string $image = '',
        string $alt_text = ''
    ): int;

    public function updateEntry(
        int $id,
        string $title,
        string $description = '',
        bool $is_outdated = false,
        string $full_name = '',
        ?URI $link = null,
        URI|string $image = '',
        string $alt_text = ''
    ): void;

    /**
     * Updates the position of entries according to the order
     * their IDs are passed.
     */
    public function reorderEntries(int ...$ids): void;
}
