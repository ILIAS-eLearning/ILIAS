<?php

declare(strict_types = 1);

/**
 * Persistence of "monitored" categories for a StudyProgramme.
 */
interface ilStudyProgrammeAutoCategoryRepository
{
	/**
	 * Read category-surveillance setting of programme.
	 * @return ilStudyProgrammeAutoCategory[]
	 */
	public function readFor(int $prg_obj_id): array;

	/**
	 * Build an auto-category object.
	 * @return ilStudyProgrammeAutoCategory[]
	 */
	public function create(
		int $prg_obj_id,
		int $category_ref_id,
		string $title,
		int $last_edited_usr_id = null,
		\DateTimeImmutable $last_edited = null
	): ilStudyProgrammeAutoCategory;

	/**
	 * Read category-surveillance setting of programme.
	 * @return ilStudyProgrammeAutoCategory[]
	 */
	public function update(ilStudyProgrammeAutoCategory $ac);

	/**
	 * Delete a single category-surveillance.
	 */
	public function delete(int $prg_obj_id, array $cat_ref_ids);

	/**
	 * Delete all category-surveillance settings for a StudyProgramme.
	 */
	public function deleteFor(int $prg_obj_id);

}
