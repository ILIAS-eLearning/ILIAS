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

use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;
use ILIAS\Exercise\GUIRequest;

/**
 * Exercise submission base gui
 *
 * This is an abstract base class for all types of submissions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilExSubmissionBaseGUI
{
    protected \ILIAS\Exercise\Notification\NotificationManager $notification;
    protected \ILIAS\Exercise\InternalDomainService $domain;
    protected \ILIAS\Exercise\InternalGUIService $gui;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjExercise $exercise;
    protected ilExSubmission $submission;
    protected ilExAssignment $assignment;
    protected MandatoryAssignmentsManager $mandatory_manager;
    protected ContextServices $tool_context;
    protected ilExAssignmentTypesGUI $type_guis;
    protected int $requested_ref_id;
    protected GUIRequest $request;

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function __construct(
        ilObjExercise $a_exercise,
        ilExSubmission $a_submission
    ) {
        global $DIC;

        $service = $DIC->exercise()->internal();
        $this->gui = $service->gui();
        $this->domain = $service->domain();

        $this->ctrl = $this->gui->ctrl();
        $this->tabs_gui = $this->gui->tabs();
        $this->lng = $this->domain->lng();
        $this->tpl = $this->gui->ui()->mainTemplate();

        $this->exercise = $a_exercise;
        $this->submission = $a_submission;
        $this->assignment = $a_submission->getAssignment();

        $this->mandatory_manager = $this->domain
            ->assignment()
            ->mandatoryAssignments($this->exercise);

        $this->request = $this->gui->request();
        $this->requested_ref_id = $this->request->getRefId();
        $this->notification = $this->domain->notification($this->requested_ref_id);

        $this->type_guis = $this->gui->assignment()->types();
        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->gui = $DIC->exercise()
            ->internal()
            ->gui();
    }

    abstract public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ): void;

    protected function handleTabs(): void
    {
        return;
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "returnToParent")
        );

        $this->tabs_gui->addTab(
            "submission",
            $this->lng->txt("exc_submission"),
            $this->ctrl->getLinkTarget($this, "")
        );
        $this->tabs_gui->activateTab("submission");

        if ($this->assignment->hasTeam()) {
            ilExSubmissionTeamGUI::handleTabs();
        }
    }

    public function returnToParentObject(): void
    {
        $this->ctrl->returnToParent($this);
    }


    //
    // RETURNED/EXERCISE STATUS
    //

    protected function handleNewUpload(
        bool $a_no_notifications = false
    ): void {
        $has_submitted = $this->submission->hasSubmitted();

        $this->exercise->processExerciseStatus(
            $this->assignment,
            $this->submission->getUserIds(),
            $has_submitted,
            $this->submission->validatePeerReviews()
        );

        if ($has_submitted &&
            !$a_no_notifications) {
            $this->notification->sendUploadNotification($this->assignment->getId());
        }
    }

    protected function handleRemovedUpload(): void
    {
        // #16532 - always send notifications
        $this->handleNewUpload();
    }

    protected function triggerAssignmentTool(): void
    {
        $ass_ids = [$this->assignment->getId()];
        $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::SHOW_EXC_ASSIGNMENT_INFO, true);
        $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::EXC_ASS_IDS, $ass_ids);
    }
}
