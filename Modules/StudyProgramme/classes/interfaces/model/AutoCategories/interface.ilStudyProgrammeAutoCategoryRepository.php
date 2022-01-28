<?php declare(strict_types=1);

/**
 * Persistence of "monitored" categories for a StudyProgramme.
 */
interface ilStudyProgrammeAutoCategoryRepository
{
    /**
     * Read category-surveillance settings of programme.
     *
     * @return ilStudyProgrammeAutoCategory[]
     */
    public function getFor(int $prg_obj_id) : array;

    /**
     * Build an auto-category object.
     */
    public function create(
        int $prg_obj_id,
        int $category_ref_id,
        int $last_edited_usr_id = null,
        DateTimeImmutable $last_edited = null
    ) : ilStudyProgrammeAutoCategory;

    /**
     * Store a category-surveillance setting.
     */
    public function update(ilStudyProgrammeAutoCategory $ac) : void;

    /**
     * Delete a single category-surveillance.
     *
     * @param int[] $cat_ref_ids
     */
    public function delete(int $prg_obj_id, array $cat_ref_ids) : void;

    /**
     * Delete all category-surveillance settings for a StudyProgramme.
     */
    public function deleteFor(int $prg_obj_id) : void;

    /**
     * Get all programmes' ref_ids monitoring the given category.
     *
     * @return int[]
     */
    public static function getProgrammesFor(int $cat_ref_id) : array;
}
