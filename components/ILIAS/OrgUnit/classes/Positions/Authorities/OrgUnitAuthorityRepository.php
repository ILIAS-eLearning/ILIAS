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

interface OrgUnitAuthorityRepository
{
    /**
     * @return ilOrgUnitAuthority[]
     */
    public function get(int $id, string $field): array;

    public function store(ilOrgUnitAuthority $authority): ilOrgUnitAuthority;

    public function create(): ilOrgUnitAuthority;

    public function delete(int $id): void;

    public function deleteByPositionId(int $position_id): void;

    public function deleteLeftoverAuthorities(array $ids, int $position_id): void;
}
