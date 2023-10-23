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

namespace ILIAS\Exercise\Notification;

use ILIAS\Exercise\InternalDomainService;

class NotificationManager
{
    protected int $ref_id;
    protected \ILIAS\Exercise\Object\ObjectManager $object;

    public function __construct(
        InternalDomainService $domain,
        int $ref_id
    ) {
        $this->object = $domain->object($ref_id);
        $this->ref_id = $ref_id;
    }


    public function sendUploadNotification(int $ass_id): void
    {
        $users = \ilNotification::getNotificationsForObject(
            \ilNotification::TYPE_EXERCISE_SUBMISSION,
            $this->object->getId()
        );

        $not = new \ilExerciseMailNotification();
        $not->setType(\ilExerciseMailNotification::TYPE_SUBMISSION_UPLOAD);
        $not->setAssignmentId($ass_id);
        $not->setRefId($this->ref_id);
        $not->setRecipients($users);
        $not->send();
    }

    public function sendFeedbackNotification(
        int $ass_id,
        array $user_ids,
        string $feedback_file = "",
        bool $is_text_feedback = false
    ): void {
        $type = $is_text_feedback
            ? \ilExerciseMailNotification::TYPE_FEEDBACK_TEXT_ADDED
            : \ilExerciseMailNotification::TYPE_FEEDBACK_FILE_ADDED;

        $not = new \ilExerciseMailNotification();
        $not->setType($type);
        $not->setAssignmentId($ass_id);
        $not->setRefId($this->ref_id);
        $not->setRecipients($user_ids);
        $not->send();
    }

    public function sendMessageFromPeerfeedbackGiverNotification(
        int $ass_id,
        int $rcp_id,
        string $text
    ): void {
        $not = new \ilExerciseMailNotification();
        $not->setType(\ilExerciseMailNotification::TYPE_MESSAGE_FROM_PF_GIVER);
        $not->setAssignmentId($ass_id);
        $not->setRefId($this->ref_id);
        $not->setRecipients([$rcp_id]);
        $not->setAdditionalText($text);
        $not->send();
    }

    public function sendMessageFromPeerfeedbackRecipientNotification(
        int $ass_id,
        int $peer_id,
        int $notification_rcp_id,
        string $text
    ): void {
        $not = new \ilExerciseMailNotification();
        $not->setType(\ilExerciseMailNotification::TYPE_MESSAGE_FROM_PF_RECIPIENT);
        $not->setAssignmentId($ass_id);
        $not->setRefId($this->ref_id);
        $not->setPeerId($peer_id);
        $not->setRecipients([$notification_rcp_id]);
        $not->setAdditionalText($text);
        $not->send();
    }

    public function sendDeadlineRequestNotification(int $ass_id): void
    {
        $users = \ilNotification::getNotificationsForObject(
            \ilNotification::TYPE_EXERCISE_SUBMISSION,
            $this->object->getId()
        );

        $not = new \ilExerciseMailNotification();
        $not->setType(\ilExerciseMailNotification::TYPE_DEADLINE_REQUESTED);
        $not->setAssignmentId($ass_id);
        $not->setRefId($this->ref_id);
        $not->setRecipients($users);
        $not->send();
    }

    public function sendDeadlineSetNotification(int $ass_id, int $part_id): void
    {
        $not = new \ilExerciseMailNotification();
        $not->setType(\ilExerciseMailNotification::TYPE_IDL_DEADLINE_SET);
        $not->setAssignmentId($ass_id);
        $not->setRefId($this->ref_id);
        $not->setRecipients([$part_id]);
        $not->send();
    }

}
