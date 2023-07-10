<?php

declare(strict_types=1);

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

/**
 * Persistence of "monitored" sources for automatic membership
 */
interface ilStudyProgrammeAutoMembershipsRepository
{
    /**
     * Read auto-membership sources of programme.
     *
     * @return ilStudyProgrammeAutoMembershipSource[]
     */
    public function getFor(int $prg_obj_id): array;

    /**
     * Build an auto-membership source.
     */
    public function create(
        int $prg_obj_id,
        string $source_type,
        int $source_id,
        bool $enabled,
        int $last_edited_usr_id = null,
        DateTimeImmutable $last_edited = null
    ): ilStudyProgrammeAutoMembershipSource;

    /**
     * Update an auto-membership source.
     */
    public function update(ilStudyProgrammeAutoMembershipSource $ams): void;

    /**
     * Delete a single source-setting.
     */
    public function delete(int $prg_obj_id, string $source_type, int $source_id): void;

    /**
     * Delete all auto-membership sources of a programme.
     */
    public function deleteFor(int $prg_obj_id): void;

    /**
     * Get all programmes' obj_ids monitoring the given source.
     *
     * @return int[]
     */
    public static function getProgrammesFor(string $source_type, int $source_id): array;
}
