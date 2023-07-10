<?php

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
    public function hasFileUploads($test_id): bool;

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
    public function getFileUploadPath($test_id, $active_id, $question_id = null): string;
}
