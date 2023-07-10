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
    public function getUserId(int $submission_id): int;

    // Get number of submissions from assignment id
    public function hasSubmissions(int $assignment_id): int;
}
