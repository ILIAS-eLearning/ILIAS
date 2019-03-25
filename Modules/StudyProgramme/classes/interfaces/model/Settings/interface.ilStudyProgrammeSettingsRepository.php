<?php declare(strict_types = 1);

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
	public function read(int $obj_id) : ilStudyProgrammeSettings;

	/**
	 * Update settings belonging to a SP-Object.
	 * Will throw if the record does not exist yet.
	 */
	public function update(ilStudyProgrammeSettings $settings);

	/**
	 * Delete record corresponding to settings.
	 * Will throw if the record does not exist yet.
	 */
	public function delete(ilStudyProgrammeSettings $settings);

	/**
	 * Load SP settings by assigned type.
	 */
	public function loadByType(int $type_id) : array;

	/**
	 * Load SP setting-ids by assigned type.
	 */
	public function loadIdsByType(int $type_id) : array;
}