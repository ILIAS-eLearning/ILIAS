<?php declare(strict_types=1);

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
 * Covers the persistence of settings belonging to a study programme (SP).
 */
interface ilStudyProgrammeSettingsRepository
{
    /**
     * Create a record corresponding to a SP-Object and return representing settings.
     * Will throw if a record allready exists.
     */
    public function createFor(int $obj_id) : ilStudyProgrammeSettings;

    /**
     * Load settings belonging to a SP-Object.
     * Will throw if the record does not exist yet.
     */
    public function get(int $obj_id) : ilStudyProgrammeSettings;

    /**
     * Update settings belonging to a SP-Object.
     * Will throw if the record does not exist yet.
     */
    public function update(ilStudyProgrammeSettings $settings) : void;

    /**
     * Delete record corresponding to settings.
     * Will throw if the record does not exist yet.
     */
    public function delete(ilStudyProgrammeSettings $settings) : void;

    /**
     * Load SP settings by assigned type.
     * @return ilStudyProgrammeSettings[]
     */
    public function loadByType(int $type_id) : array;

    /**
     * Load SP setting-ids by assigned type.
     */
    public function loadIdsByType(int $type_id) : array;
}
