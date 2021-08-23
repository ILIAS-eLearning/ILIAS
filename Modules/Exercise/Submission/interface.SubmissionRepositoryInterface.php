<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise\Submission;

/**
 * Submission repository Interface
 *
 * @author Jesús López <lopez@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
interface SubmissionRepositoryInterface
{
    // Get User ID for a submission ID
    public function getUserId(int $submission_id) : int;

    // Get number of submissions from assignment id
    public function hasSubmissions(int $assignment_id) : int;
}
