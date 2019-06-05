<?php

declare(strict_types = 1);

/**
 * Persistence of "monitored" categories for a StudyProgramme.
 */
interface ilStudyProgrammeAutoCategoryDBRepository
{
	/**
	 * Read category-surveillance setting of programme.
	 * @return ilStudyProgrammeAutoCategory[]
	 */
	public function readFor(int $prg_obj_id): array;

	/**
	 * Read category-surveillance setting of programme.
	 * @return ilStudyProgrammeAutoCategory[]
	 */
	public function update(ilStudyProgrammeAutoCategory $ac);

	/**
	 * Delete a single category-surveillance.
	 */
	public function delete(int $prg_obj_id, int $cat_ref_id);

	/**
	 * Delete all category-surveillance settings for a StudyProgramme.
	 */
	public function deleteFor(int $prg_obj_id);

}
