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

declare(strict_types=1);

namespace ILIAS\Exercise;

use ILIAS\Exercise\Assignment\Assignment;
use ILIAS\Repository\IRSS\ResourceInformation;
use ILIAS\Exercise\Team\TeamMember;
use ILIAS\Exercise\Submission\Submission;
use ILIAS\Exercise\PeerReview\Criteria\CriteriaFile;
use ILIAS\Exercise\Settings\Settings;

/**
 * Internal factory for data objects
 */
class InternalDataService
{
    public function __construct()
    {
    }

    public function assignment(
        int $id,
        int $exc_id,
        string $title,
        int $order_nr,
        int $type,
        string $instructions,
        bool $mandatory,
        int $deadline_mode,
        int $deadline,
        int $deadline2,
        int $relative_deadline,
        int $rel_deadline_last_submission
    ): Assignment {
        return new Assignment(
            $id,
            $exc_id,
            $title,
            $order_nr,
            $type,
            $instructions,
            $mandatory,
            $deadline_mode,
            $deadline,
            $deadline2,
            $relative_deadline,
            $rel_deadline_last_submission
        );
    }

    public function resourceInformation(
        string $rid,
        string $title,
        int $size,
        int $creation_timestamp,
        string $mime_type,
        string $src
    ): ResourceInformation {
        return new ResourceInformation(
            $rid,
            $title,
            $size,
            $creation_timestamp,
            $mime_type,
            $src
        );
    }

    public function teamMember(
        int $team_id,
        int $assignment_id,
        int $user_id
    ): TeamMember {
        return new TeamMember(
            $team_id,
            $assignment_id,
            $user_id
        );
    }

    public function submission(
        int $id,
        int $ass_id,
        int $user_id,
        int $team_id = 0,
        string $title = "",
        string $text = "",
        string $rid = "",
        string $mimetype = "",
        string $timestamp = "",
        bool $late = false
    ): Submission {
        return new Submission(
            $id,
            $ass_id,
            $user_id,
            $team_id,
            $title,
            $text,
            $rid,
            $mimetype,
            $timestamp,
            $late
        );
    }

    public function criteriaFile(
        int $ass_id,
        int $giver_id,
        int $peer_id,
        int $criteria_id,
        string $rid,
        string $title
    ): CriteriaFile {
        return new CriteriaFile(
            $ass_id,
            $giver_id,
            $peer_id,
            $criteria_id,
            $rid,
            $title
        );
    }

    public function settings(
        int $obj_id = 0,
        string $instruction = "",
        int $time_stamp = 0,
        string $pass_mode = \ilObjExercise::PASS_MODE_ALL,
        int $nr_mandatory_random = 0,
        int $pass_nr = 0,
        bool $show_submissions = false,
        bool $compl_by_submission = false,
        int $certificate_visibility = 0,
        int $tfeedback = 7
    ): Settings {
        return new Settings(
            $obj_id,
            $instruction,
            $time_stamp,
            $pass_mode,
            $nr_mandatory_random,
            $pass_nr,
            $show_submissions,
            $compl_by_submission,
            $certificate_visibility,
            $tfeedback
        );
    }
}
