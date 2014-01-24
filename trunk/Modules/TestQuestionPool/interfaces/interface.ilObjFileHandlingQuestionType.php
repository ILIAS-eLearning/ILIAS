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
	 * @param int $test_id
	 *
	 * @return boolean TRUE if file uploads exist, FALSE otherwise
	 */
	public function hasFileUploads($test_id);

	/**
	 * Generates a ZIP file containing all file uploads for a given test and the original id of the question
	 * 
	 * @param int $test_id
	 */
	public function getFileUploadZIPFile($test_id);
}