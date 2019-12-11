<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilObjFileHandlingQuestionType
 *
 * Thin interface to denote a question as a file handling question type.
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 */
interface ilObjFileHandlingQuestionType
{
    /**
     * Checks if file uploads exist for a given test and the original id of the question
     *
     * @param integer $test_id
     *
     * @return boolean TRUE if file uploads exist, FALSE otherwise
     */
    public function hasFileUploads($test_id);

    /**
     * Generates a ZIP file containing all file uploads for a given test and the original id of the question
     *
     * @param integer $ref_id
     * @param integer $test_id
     * @param string $test_title
     */
    public function deliverFileUploadZIPFile($ref_id, $test_id, $test_title);

    /**
     * Returns the path for uploaded files from given active in given test
     * on current or given question
     *
     * @param integer $test_id
     * @param integer $active_id
     * @param integer|null $question_id
     * @return string
     */
    public function getFileUploadPath($test_id, $active_id, $question_id = null);
}
