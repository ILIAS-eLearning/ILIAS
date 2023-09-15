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
 * Covers the persistence of sp-type related information.
 */
interface ilStudyProgrammeTypeRepository
{
    /**
     * Create a type record and return an object representing it.
     */
    public function createType(string $default_language): ilStudyProgrammeType;

    /**
     * Create an amd-record record and return an object representing it.
     */
    public function createAMDRecord(): ilStudyProgrammeAdvancedMetadataRecord;

    /**
     * Create a type translation record and return an object representing it.
     */
    public function createTypeTranslation(): ilStudyProgrammeTypeTranslation;

    /**
     * Persist type properties.
     */
    public function updateType(ilStudyProgrammeType $type): void;

    /**
     * Persist amd-record properties.
     */
    public function updateAMDRecord(ilStudyProgrammeAdvancedMetadataRecord $rec): void;

    /**
     * Persist type translation properties.
     */
    public function updateTypeTranslation(ilStudyProgrammeTypeTranslation $tt): void;

    /**
     * Delete record corresponding to given object.
     */
    public function deleteType(ilStudyProgrammeType $type): void;

    /**
     * Delete record corresponding to given object.
     */
    public function deleteAMDRecord(ilStudyProgrammeAdvancedMetadataRecord $rec): void;

    /**
     * Delete record corresponding to given object.
     */
    public function deleteTypeTranslation(ilStudyProgrammeTypeTranslation $tt): void;

    /**
     * Delete all translation records corresponding to a type id.
     */
    public function deleteTypeTranslationByTypeId(int $type_id): void;

    /**
     * Get all persisted type-objects.
     * @return ilStudyProgrammeType[]
     */
    public function getAllTypes(): array;

    /**
     * Get a type with given type_id.
     */
    public function getType(int $type_id): ilStudyProgrammeType;


    /**
     * Get an assicative array of all persisted types id => title
     * @return array<int, string>
     */
    public function getAllTypesArray(): array;

    /**
     * @return ilAdvancedMDRecord[]
     */
    public function getAssignedAMDRecordsByType(int $type_id, bool $only_active = false): array;

    /**
     * @return int[]
     */
    public function getAssignedAMDRecordIdsByType(int $type_id, bool $only_active = false): array;

    /**
     * @return ilAdvancedMDRecord[]
     */
    public function getAllAMDRecords(): array;

    /**
     * @return int[]
     */
    public function getAllAMDRecordIds(): array;

    /**
     * @return ilStudyProgrammeAdvancedMetadataRecord[]
     */
    public function getAMDRecordsByTypeIdAndRecordId(int $type_id, int $record_id): array;

    /**
     * @return ilStudyProgrammeAdvancedMetadataRecord[]
     */
    public function getAMDRecordsByTypeId(int $type_id, bool $only_active = false): array;

    public function getTranslationsArrayByTypeIdAndLangCode(int $type_id, string $lang_code): array;

    /**
     * Get all prg-settings objects by corresponding type-id
     * @return ilStudyProgrammeSettings[]
     */
    public function getStudyProgrammesByTypeId(int $type_id): array;
    /**
     * Get all prg-settings ids by corresponding type-id
     * @return int[]
     */
    public function getStudyProgrammeIdsByTypeId(int $type_id): array;

    /**
     * @return ilStudyProgrammeAdvancedMetadataRecord[]
     */
    public function getAvailableAdvancedMDRecords(): array;

    /**
     * @return int[]
     */
    public function getAvailableAdvancedMDRecordIds(): array;

    /**
     * @return array<string, string>
     */
    public function getTranslationsByTypeAndLang(int $type_id, string $lang_code): array;
    public function getTranslationByTypeIdMemberLang(
        int $type_id,
        string $member,
        string $lang_code
    ): ?ilStudyProgrammeTypeTranslation;
}
