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

interface OrgUnitPositionRepository
{
    /**
     * @return ilOrgUnitPosition[]
     */
    public function get(int|string $value, string $field): array;

    public function getSingle(int|string $value, string $field): ?ilOrgUnitPosition;

    /**
     * @return ilOrgUnitPosition[]
     */
    public function getAllPositions(): array;

    public function getArray(?string $key, ?string $field): array;

    /**
     * @return ilOrgUnitPosition[]
     */
    public function getPositionsForOrgUnit(int $orgu_id): array;

    public function store(ilOrgUnitPosition $position): ilOrgUnitPosition;

    public function create(): ilOrgUnitPosition;

    public function delete(int $position_id): void;

    public function getAuthority(?int $id): ilOrgUnitAuthority;
}
