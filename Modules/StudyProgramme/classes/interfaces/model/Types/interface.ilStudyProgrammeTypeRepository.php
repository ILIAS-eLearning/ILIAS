<?php declare(strict_types = 1);

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
	public function updateType(ilStudyProgrammeType $type);

	/**
	 * Persist amd-record properties.
	 */
	public function updateAMDRecord(ilStudyProgrammeAdvancedMetadataRecord $rec);

	/**
	 * Persist type translation properties.
	 */
	public function updateTypeTranslation(ilStudyProgrammeTypeTranslation $tt);

	/**
	 * Delete record corresponding to given object.
	 */
	public function deleteType(ilStudyProgrammeType $type);

	/**
	 * Delete record corresponding to given object.
	 */
	public function deleteAMDRecord(ilStudyProgrammeAdvancedMetadataRecord $rec);

	/**
	 * Delete record corresponding to given object.
	 */
	public function deleteTypeTranslation(ilStudyProgrammeTypeTranslation $tt);

	/**
	 * Delete all translation records corresponding to a type id.
	 */
	public function deleteTypeTranslationByTypeId(int $type_id);

	/**
	 * Get all persisted type-objects.
	 */
	public function readAllTypes() : array;

	/**
	 * Get a type with given type_id.
	 */
	public function readType(int $type_id) : ilStudyProgrammeType;


	/**
	 * Get an assicative array of all persisted types id => title
	 */
	public function readAllTypesArray() : array;

	public function readAssignedAMDRecordsByType(int $type_id, bool $only_active = false) : array;
	public function readAssignedAMDRecordIdsByType(int $type_id, bool $only_active = false) : array;

	public function readAllAMDRecords() : array;
	public function readAllAMDRecordIds() : array;
	public function readAMDRecordsByTypeIdAndRecordId(int $type_id, int $record_id) : array;
	public function readAMDRecordsByTypeId(int $type_id, bool $only_active = false) : array;

	public function readTranslationsArrayByTypeIdAndLangCode(int $type_id, string $lang_code) : array;

	/**
	 * Get all prg-settings objects by corresponding type-id
	 */
	public function readStudyProgrammesByTypeId(int $type_id) : array;
	/**
	 * Get all prg-settings ids by corresponding type-id
	 */
	public function readStudyProgrammeIdsByTypeId(int $type_id) : array;

	public function getAvailableAdvancedMDRecords() : array;
	public function getAvailableAdvancedMDRecordIds() : array;

	public function readTranslationsByTypeAndLang(int $type_id, string $lang_code);
	public function readTranslationByTypeIdMemberLang(int $type_id, string $member, string $lang_code);

}