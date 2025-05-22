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

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilExerciseSubmissionFeedbackGUI
{
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;
    protected ?ilObjExercise $exercise;
    protected ILIAS\Exercise\Notification\NotificationManager $notification;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        ?ilObjExercise $exercise,
        ILIAS\Exercise\Notification\NotificationManager $notification
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
        $lng = $domain->lng();
        $lng->loadLanguageModule("exc");
        $this->exercise = $exercise;
        $this->notification = $notification;
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("showFeedbackForm");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "showFeedbackForm",
                    "validateAndSubmitFeedbackForm",
                    "saveCommentForLearners"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function getComponents(int $ass_id, int $usr_id): array
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $ctrl->setParameter($this, "ass_id", $ass_id);
        $ctrl->setParameter($this, "member_id", $usr_id);

        $components = $this
            ->gui
            ->modal($lng->txt("exc_tbl_action_feedback_text"))
            ->getAsyncTriggerButtonComponents(
                $lng->txt("exc_tbl_action_feedback_text"),
                $ctrl->getLinkTarget($this, "showFeedbackForm", "", true),
                true
            );

        return $components;
    }

    protected function showFeedbackForm(): void
    {
        $lng = $this->domain->lng();
        $this->gui
            ->modal($lng->txt("exc_tbl_action_feedback_text"))
            ->form($this->getFeedbackForm())
            ->send();
    }

    protected function getFeedbackForm(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $request = $this->gui->request();

        $ass_id = $request->getAssId();
        $ass = $this->domain->assignment()->getAssignment($ass_id);
        $user_id = $request->getMemberId();

        $ctrl->setParameter($this, "ass_id", $ass_id);
        $ctrl->setParameter($this, "member_id", $user_id);

        $form = $this
            ->gui
            ->form(self::class, "validateAndSubmitFeedbackForm")
            ->asyncModal()
            ->textarea(
                "comment",
                $lng->txt("exc_comment_for_learner"),
                $lng->txt("exc_comment_for_learner_info"),
                $ass->getMemberStatus($user_id)->getComment()
            );

        return $form;
    }

    protected function validateAndSubmitFeedbackForm(): void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $request = $this->gui->request();
        $form = $this->getFeedbackForm();
        if (!$form->isValid()) {
            $this->gui->modal($lng->txt("exc_tbl_action_feedback_text"))
                      ->form($form)
                      ->send();
        }

        $user_id = $request->getMemberId();
        $ass_id = $request->getAssId();
        $comment = $form->getData("comment");

        $ctrl->setParameter($this, "member_id", $user_id);
        $ctrl->setParameter($this, "ass_id", $ass_id);
        $ctrl->setParameter($this, "comment", $comment);

        $target = $ctrl->getLinkTarget($this, "saveCommentForLearners");
        $this->gui->send("<script>window.location.href = '" . $target . "';</script>");
    }

    protected function saveCommentForLearners(): void
    {
        $ctrl = $this->gui->ctrl();
        $request = $this->gui->request();

        $ass_id = $request->getAssId();
        $ass = $this->domain->assignment()->getAssignment($ass_id);
        $user_id = $request->getMemberId();
        $comment = trim($request->getComment());

        if ($ass_id && $user_id) {
            $submission = new ilExSubmission($ass, $user_id);
            $user_ids = $submission->getUserIds();

            $all_members = new ilExerciseMembers($this->exercise);
            $all_members = $all_members->getMembers();

            $reci_ids = array();
            foreach ($user_ids as $user_id) {
                if (in_array($user_id, $all_members)) {
                    $member_status = $ass->getMemberStatus($user_id);
                    $member_status->setComment(ilUtil::stripSlashes($comment));
                    $member_status->setFeedback(true);
                    $member_status->update();

                    if (trim($comment) !== '' && trim($comment) !== '0') {
                        $reci_ids[] = $user_id;
                    }
                }
            }

            if ($reci_ids !== []) {
                // send notification
                $this->notification->sendFeedbackNotification(
                    $ass_id,
                    $reci_ids,
                    "",
                    true
                );
            }
        }

        $ctrl->redirectByClass("ilexercisemanagementgui", $request->getParticipantId()
            ? "showParticipant"
            : "members");
    }
}
