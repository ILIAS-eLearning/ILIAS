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

namespace ILIAS\GlobalScreen\UI\Footer\Entries;

use ILIAS\GlobalScreen\Scope\Footer\Collector\FooterMainCollector;

interface EntriesRepository
{
    public function syncWithGlobalScreen(FooterMainCollector $collector): void;

    public function store(Entry $entry): Entry;

    public function blank(): Entry;

    public function delete(Entry $entry): void;

    public function get(string $identifier): ?Entry;

    public function has(string $identifier): bool;

    /**
     * @return \Generator|Entry[]
     */
    public function all(): \Generator;

    /**
     * @return \Generator|Entry[]
     */
    public function allForParent(string $parent_identifier): \Generator;

    public function updatePositionById(string $id, int $position): void;
}
