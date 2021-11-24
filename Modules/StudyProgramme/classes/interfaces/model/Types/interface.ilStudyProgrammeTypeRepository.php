<?php declare(strict_types=1);

/**
 * Covers the persistence of sp-type related information.
 */
interface ilStudyProgrammeTypeRepository
{
    /**
     * Create a type record and return an object representing it.
     */
    public function createType(string $default_language) : ilStudyProgrammeType;

    /**
     * Create an amd-record record and return an object representing it.
     */
    public function createAMDRecord() : ilStudyProgrammeAdvancedMetadataRecord;

    /**
     * Create a type translation record and return an object representing it.
     */
    public function createTypeTranslation() : ilStudyProgrammeTypeTranslation;

    /**
     * Persist type properties.
     */
    public function updateType(ilStudyProgrammeType $type) : void;

    /**
     * Persist amd-record properties.
     */
    public function updateAMDRecord(ilStudyProgrammeAdvancedMetadataRecord $rec) : void;

    /**
     * Persist type translation properties.
     */
    public function updateTypeTranslation(ilStudyProgrammeTypeTranslation $tt) : void;

    /**
     * Delete record corresponding to given object.
     */
    public function deleteType(ilStudyProgrammeType $type) : void;

    /**
     * Delete record corresponding to given object.
     */
    public function deleteAMDRecord(ilStudyProgrammeAdvancedMetadataRecord $rec) : void;

    /**
     * Delete record corresponding to given object.
     */
    public function deleteTypeTranslation(ilStudyProgrammeTypeTranslation $tt) : void;

    /**
     * Delete all translation records corresponding to a type id.
     */
    public function deleteTypeTranslationByTypeId(int $type_id) : void;

    /**
     * Get all persisted type-objects.
     */
    public function getAllTypes() : array;

    /**
     * Get a type with given type_id.
     */
    public function getType(int $type_id) : ilStudyProgrammeType;


    /**
     * Get an assicative array of all persisted types id => title
     */
    public function getAllTypesArray() : array;

    public function getAssignedAMDRecordsByType(int $type_id, bool $only_active = false) : array;
    public function getAssignedAMDRecordIdsByType(int $type_id, bool $only_active = false) : array;

    public function getAllAMDRecords() : array;
    public function getAllAMDRecordIds() : array;
    public function getAMDRecordsByTypeIdAndRecordId(int $type_id, int $record_id) : array;
    public function getAMDRecordsByTypeId(int $type_id, bool $only_active = false) : array;

    public function getTranslationsArrayByTypeIdAndLangCode(int $type_id, string $lang_code) : array;

    /**
     * Get all prg-settings objects by corresponding type-id
     */
    public function getStudyProgrammesByTypeId(int $type_id) : array;
    /**
     * Get all prg-settings ids by corresponding type-id
     */
    public function getStudyProgrammeIdsByTypeId(int $type_id) : array;

    public function getAvailableAdvancedMDRecords() : array;
    public function getAvailableAdvancedMDRecordIds() : array;

    public function getTranslationsByTypeAndLang(int $type_id, string $lang_code) : array;
    public function getTranslationByTypeIdMemberLang(
        int $type_id,
        string $member,
        string $lang_code
    ) : ?ilStudyProgrammeTypeTranslation;
}
