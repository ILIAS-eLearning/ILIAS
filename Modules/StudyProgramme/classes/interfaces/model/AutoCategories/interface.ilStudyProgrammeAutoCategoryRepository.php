<?php

declare(strict_types = 1);

/**
 * Persistence of "monitored" categories for a StudyProgramme.
 */
interface ilStudyProgrammeAutoCategoryRepository
{
	/**
	 * Read category-surveillance settings of programme.
	 * @param int $prg_obj_id
	 * @return ilStudyProgrammeAutoCategory[]
	 */
	public function readFor(int $prg_obj_id): array;

	/**
	 * Build an auto-category object.
	 * @return ilStudyProgrammeAutoCategory
	 */
	public function create(
		int $prg_obj_id,
		int $category_ref_id,
		int $last_edited_usr_id = null,
		\DateTimeImmutable $last_edited = null
	): ilStudyProgrammeAutoCategory;

	/**
	 * Store a category-surveillance setting.
	 * @param ilStudyProgrammeAutoCategory $ac
	 */
	public function update(ilStudyProgrammeAutoCategory $ac);

	/**
	 * Delete a single category-surveillance.
	 * @param int $prg_obj_id
	 * @param int[] $cat_ref_ids
	 */
	public function delete(int $prg_obj_id, array $cat_ref_ids);

	/**
	 * Delete all category-surveillance settings for a StudyProgramme.
	 * @param int $prg_obj_id
	 */
	public function deleteFor(int $prg_obj_id);

	/**
	 * Get all programmes' ref_ids monitoring the given category.
	 * @return int[]
	 */
	public static function getProgrammesFor(int $cat_ref_id): array;
}
