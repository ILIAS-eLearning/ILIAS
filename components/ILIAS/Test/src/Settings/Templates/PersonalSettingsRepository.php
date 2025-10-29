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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\Data\Order;
use ILIAS\Data\Range;

interface PersonalSettingsRepository
{
    /**
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getForUser(?Range $range, ?Order $order): array;

    public function countForUser(): int;

    /**
     * @param list<int> $ids
     * @return array<int, PersonalSettingsTemplate>
     */
    public function getByIds(array $ids): array;

    public function getById(int $id): ?PersonalSettingsTemplate;

    public function create(
        string $name,
        string $description,
        string $author,
        ?\DateTimeImmutable $timestamp = null
    ): PersonalSettingsTemplate;

    public function delete(PersonalSettingsTemplate $template): void;

    /**
     * @return int[]
     */
    public function lookupMarkSteps(int $template_id): array;

    /**
     * @param int[] $mark_ids
     */
    public function associateMarkSteps(int $template_id, array $mark_ids): void;

    /**
     * @param int[] $mark_ids
     */
    public function detachMarkSteps(int $template_id, array $mark_ids): void;
}
