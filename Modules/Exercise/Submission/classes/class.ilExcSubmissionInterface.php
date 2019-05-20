<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Submission repository Interface
 *
 * @author Jesús López <lopez@leifos.com>
 */
interface ilExcSubmissionRepositoryInterface
{
	/**
	 * Get User who submitted.
	 * @param int $submission_id
	 * @return int
	 */
	public function getUserId(int $submission_id): int;

	/**
	 * Get number of submissions from assignment id
	 * @param int $assignment_id
	 * @return int
	 */
	public function hasSubmissions(int $assignment_id): int;
}