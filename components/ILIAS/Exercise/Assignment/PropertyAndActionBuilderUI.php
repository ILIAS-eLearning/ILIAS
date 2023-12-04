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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;
use ILIAS\UI\Component\Button\Shy as ButtonShy;
use ILIAS\UI\Component\Link\Standard as LinkStandard;
use ILIAS\UI\Component\Button\Primary as ButtonPrimary;
use ILIAS\UI\Component\Component;

class PropertyAndActionBuilderUI
{
    public const PROP_DEADLINE = "deadline";
    public const PROP_REQUIREMENT = "requirement";
    public const PROP_SUBMISSION = "submission";
    public const PROP_TYPE = "type";
    public const PROP_GRADING = "grading";
    public const PROP_MARK = "mark";

    public const SEC_INSTRUCTIONS = "instructions";
    public const SEC_INSTRUCTIONS_OV = "instructions_overview";
    public const SEC_FILES = "files";
    public const SEC_SCHEDULE = "schedule";
    public const SEC_TEAM = "team";
    public const SEC_SUBMISSION = "submission";
    public const SEC_PEER_FEEDBACK = "peer_feedback";
    public const SEC_TUTOR_EVAL = "tutor_eval";
    public const SEC_SAMPLE_SOLUTION = "sample_solution";
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;
    protected \ilLanguage $lng;
    protected MandatoryAssignmentsManager $mandatory_manager;
    protected \ilCtrl $ctrl;
    protected \ilExAssignmentTypes $types;

    protected int $user_builded = 0;
    protected int $ass_builded = 0;
    protected \ilExcAssMemberState $state;
    protected \ilExSubmission $submission;
    protected \ilExAssignmentTypesGUI $types_gui;
    protected \ilObjExercise $exc;
    protected \ilExAssignment $ex_ass;
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_type;
    protected Assignment $assignment;
    protected \ilExAssignmentInfo $info;

    protected int $user_id;
    protected string $lead_text = "";
    protected array $head_properties = [];
    protected array $properties = [];
    protected array $actions = [];
    protected array $views = [];
    protected array $main_action = [];
    protected array $additional_head_properties = [];
    protected bool $instructions_hidden = false;

    public function __construct(
        \ilObjExercise $exc,
        MandatoryAssignmentsManager $mandatory_manager,
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        global $DIC;

        $this->exc = $exc;
        $this->media_type = $DIC->mediaObjects()->internal()->domain()->mediaType();
        $this->domain = $domain_service;
        $this->gui = $gui_service;
        $this->lng = $domain_service->lng();
        $this->mandatory_manager = $mandatory_manager;
        $this->lng->loadLanguageModule("exc");
        $this->ctrl = $gui_service->ctrl();
        $this->types_gui = $gui_service->assignment()->types();
        $this->types = \ilExAssignmentTypes::getInstance();
    }

    public function build(
        Assignment $ass,
        int $user_id
    ): void {
        if ($this->user_builded === $user_id && $this->ass_builded === $ass->getId()) {
            return;
        }
        $this->assignment = $ass;
        $this->user_id = $user_id;
        $this->state = \ilExcAssMemberState::getInstanceByIds($ass->getId(), $user_id);
        $this->info = new \ilExAssignmentInfo($ass->getId(), $user_id);
        $this->ex_ass = new \ilExAssignment($this->assignment->getId());
        $this->submission = new \ilExSubmission($this->ex_ass, $user_id);
        $this->lead_text = "";
        $this->head_properties = [];
        $this->views = [];
        $this->additional_head_properties = [];
        $this->main_action = [];
        $this->actions = [];
        $this->buildHead();
        $this->buildBody();
        $this->user_builded = $user_id;
        $this->ass_builded = $ass->getId();
    }

    public function getSections(bool $include_schedule = true, bool $overview = false): array
    {
        $secs = [];
        $secs[self::SEC_INSTRUCTIONS] = $this->lng->txt("exc_instruction");
        if ($overview) {
            $secs[self::SEC_INSTRUCTIONS_OV] = "";
        }
        if ($include_schedule) {
            $secs[self::SEC_SCHEDULE] = $this->lng->txt("exc_schedule");
        }
        $secs[self::SEC_FILES] = $this->lng->txt("exc_files");
        $secs[self::SEC_TEAM] = $this->lng->txt("exc_team");
        $secs[self::SEC_SUBMISSION] = $this->lng->txt("exc_submission");
        $secs[self::SEC_PEER_FEEDBACK] = $this->lng->txt("exc_peer_review");
        $secs[self::SEC_TUTOR_EVAL] = $this->lng->txt("exc_feedback_from_tutor");
        $secs[self::SEC_SAMPLE_SOLUTION] = $this->lng->txt("exc_global_feedback_file");

        return $secs;
    }

    public function getSectionTitle(string $sec): string
    {
        $secs = $this->getSections();
        return $secs[$sec] ?? "";
    }

    public function getLeadText(): string
    {
        return $this->lead_text;
    }

    public function getHeadProperty(string $type): ?array
    {
        return $this->head_properties[$type] ?? null;
    }

    public function getAdditionalHeadProperties(): array
    {
        return $this->additional_head_properties;
    }

    protected function setLeadText(string $text): void
    {
        $this->lead_text = $text;
    }

    protected function setInstructionsHidden(bool $hidden): void
    {
        $this->instructions_hidden = $hidden;
    }

    public function getInstructionsHidden(): bool
    {
        return $this->instructions_hidden;
    }

    protected function setHeadProperty(string $type, string $prop, string $val): void
    {
        $this->head_properties[$type] = [
            "prop" => $prop,
            "val" => $val
        ];
    }

    public function addAdditionalHeadProperty(string $prop, string $val): void
    {
        $this->additional_head_properties[] = [
            "prop" => $prop,
            "val" => $val
        ];
    }

    public function addProperty(string $section, string $prop, string $val): void
    {
        $this->properties[$section][] = [
            "prop" => $prop,
            "val" => $val
        ];
    }

    public function getProperties(string $section): array
    {
        return $this->properties[$section] ?? [];
    }

    public function addAction(string $section, Component $button_or_link): void
    {
        $this->actions[$section][] = $button_or_link;
    }

    public function getActions(string $section): array
    {
        return $this->actions[$section] ?? [];
    }

    public function setMainAction(string $section, ButtonPrimary $button): void
    {
        $this->main_action[$section] = $button;
    }

    public function getMainAction(string $section): ?ButtonPrimary
    {
        return $this->main_action[$section] ?? null;
    }

    public function addView(string $id, string $txt, string $url): void
    {
        $this->views[] = [
            "id" => $id,
            "txt" => $txt,
            "url" => $url
        ];
    }

    public function getViews(): array
    {
        return $this->views;
    }

    protected function buildHead(): void
    {
        $state = $this->state;
        $lng = $this->lng;

        // after official deadline...
        if ($state->exceededOfficialDeadline()) {

            // both submission and peer review ended
            if ($state->hasEnded()) {
                $this->setLeadText(
                    $lng->txt("exc_ended")
                );
            } else {
                $this->setLeadText(
                    $state->getPeerReviewLeadText()
                );
            }

            $this->setHeadProperty(
                self::PROP_DEADLINE,
                $lng->txt("exc_ended_on"),
                $state->getCommonDeadlinePresentation()
            );

            // #14077 // this currently shows the feedback deadline during grace period
            if ($state->getPeerReviewDeadline()) {
                $this->addAdditionalHeadProperty(
                    $lng->txt("exc_peer_review_deadline"),
                    $state->getPeerReviewDeadlinePresentation()
                );
            }
            // not started yet
        } elseif (!$state->hasGenerallyStarted()) {
            if ($state->getRelativeDeadline()) {
                $prop = $lng->txt("exc_earliest_start_time");
            } else {
                $prop = $lng->txt("exc_starting_on");
            }
            $this->setLeadText(
                $prop . " " . $state->getGeneralStartPresentation()
            );
            $this->setHeadProperty(
                self::PROP_DEADLINE,
                $prop,
                $state->getGeneralStartPresentation()
            );
        } else {
            // deadline, but not reached
            if ($state->getCommonDeadline() > 0) {
                $this->setLeadText(
                    $state->getRemainingTimeLeadText()
                );
                $this->setHeadProperty(
                    self::PROP_DEADLINE,
                    $lng->txt("exc_edit_until"),
                    $state->getCommonDeadlinePresentation()
                );
                // relative deadline
            } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                $this->setHeadProperty(
                    self::PROP_DEADLINE,
                    $lng->txt("exc_rem_time_after_start"),
                    $state->getRelativeDeadlinePresentation()
                );
                $this->setLeadText(
                    $state->getRelativeDeadlineStartLeadText()
                );

                if ($state->getLastSubmissionOfRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                    $this->addAdditionalHeadProperty(
                        $lng->txt("exc_rel_last_submission"),
                        $state->getLastSubmissionOfRelativeDeadlinePresentation()
                    );
                }
            } elseif ($this->assignment->getDeadlineMode() === \ilExAssignment::DEADLINE_ABSOLUTE_INDIVIDUAL) {
                if ($state->needsIndividualDeadline()) {
                    if ($state->hasRequestedIndividualDeadline()) {
                        $this->setLeadText(
                            $this->lng->txt("exc_lead_wait_for_idl")
                        );
                    } else {
                        $this->setLeadText(
                            $this->lng->txt("exc_lead_request_idl")
                        );
                    }
                    $this->setHeadProperty(
                        self::PROP_DEADLINE,
                        $lng->txt("exc_deadline"),
                        $lng->txt("exc_deadline_not_set_yet")
                    );
                } else {
                    $this->setLeadText(
                        $state->getRemainingTimeLeadText()
                    );
                    $this->setHeadProperty(
                        self::PROP_DEADLINE,
                        $lng->txt("exc_edit_until"),
                        $state->getIndividualDeadlinePresentation()
                    );
                }
            } else {
                // no deadline
                $this->setLeadText(
                    $this->lng->txt("exc_submit_anytime")
                );
                $this->setHeadProperty(
                    self::PROP_DEADLINE,
                    $lng->txt("exc_edit_until"),
                    $lng->txt("exc_no_deadline")
                );
            }

            if ($state->getIndividualDeadline() > 0 &&
                $this->assignment->getDeadlineMode() !== \ilExAssignment::DEADLINE_ABSOLUTE_INDIVIDUAL) {
                $this->addAdditionalHeadProperty(
                    $lng->txt("exc_individual_deadline"),
                    $state->getIndividualDeadlinePresentation()
                );
            }
        }

        if ($this->mandatory_manager->isMandatoryForUser($this->assignment->getId(), $this->user_id)) {
            $this->setHeadProperty(
                self::PROP_REQUIREMENT,
                $lng->txt("exc_requirement"),
                $lng->txt("exc_mandatory")
            );
        } else {
            $this->setHeadProperty(
                self::PROP_REQUIREMENT,
                $lng->txt("exc_requirement"),
                $lng->txt("exc_optional")
            );
        }

        // submission property
        if ($this->submission->hasSubmitted()) {
            $last_sub = $this->submission->getLastSubmission();
            if ($last_sub) {
                $last_sub = \ilDatePresentation::formatDate(new \ilDateTime($last_sub, IL_CAL_DATETIME));
                $this->setHeadProperty(
                    self::PROP_SUBMISSION,
                    $this->lng->txt("exc_last_submission"),
                    $last_sub
                );
            }
        } else {
            $this->setHeadProperty(
                self::PROP_SUBMISSION,
                $this->lng->txt("exc_last_submission"),
                $this->lng->txt("exc_no_submission_yet")
            );
        }

        // type property
        $ass_type = $this->types->getById($this->assignment->getType());
        $this->setHeadProperty(
            self::PROP_TYPE,
            $this->lng->txt("exc_type"),
            $ass_type->getTitle()
        );

        // grading property
        if (!$this->state->isFuture()) {
            $status = $this->ex_ass->getMemberStatus($this->user_id)->getStatus();
            if ($status !== "") {
                $this->setHeadProperty(
                    self::PROP_GRADING,
                    $lng->txt("status"),
                    $lng->txt("exc_" . $status)
                );
            }
        }

        // mark
        if (!$this->state->isFuture()) {
            $mark = $this->ex_ass->getMemberStatus($this->user_id)->getMark();
            if ($mark !== "") {
                $this->setHeadProperty(
                    self::PROP_MARK,
                    $lng->txt("mark"),
                    $lng->txt($mark)
                );
            }
        }

        // status icon
        /*
        $tpl->setVariable(
            "ICON_STATUS",
            $this->getIconForStatus(
                $a_ass->getMemberStatus()->getStatus(),
                ilLPStatusIcons::ICON_VARIANT_SHORT
            )
        );*/
    }

    protected function buildBody(): void
    {
        // main view
        $this->ctrl->setParameterByClass(\ilAssignmentPresentationGUI::class, "ass_id", $this->assignment->getId());
        $this->addView(
            "ass",
            $this->lng->txt("overview"),
            $this->ctrl->getLinkTargetByClass(\ilAssignmentPresentationGUI::class, "")
        );

        if ($this->state->areInstructionsVisible()) {
            $this->buildInstructions();
            $this->buildFiles();
        } else {
            $this->setInstructionsHidden(true);
        }

        $this->buildSchedule();

        if ($this->state->hasSubmissionStarted()) {
            $this->buildSubmission();
            $this->buildPeerFeedback();
            $this->buildSampleSolution();
        }
    }


    protected function buildInstructions(): void
    {
        $inst = $this->info->getInstructionInfo();
        if (count($inst) > 0) {
            $this->addProperty(
                self::SEC_INSTRUCTIONS,
                "",
                $inst["instruction"]["value"]
            );
            $link = $this->gui->ui()->factory()->link()->standard(
                $this->lng->txt("exc_show_instructions"),
                $this->ctrl->getLinkTargetByClass(\ilAssignmentPresentationGUI::class, "")
            );
            $this->addAction(
                self::SEC_INSTRUCTIONS_OV,
                $link
            );
        }
    }

    /**
     * @throws \ilCtrlException
     * @throws \ilDateTimeException
     */
    protected function buildSchedule(): void
    {

        $info = $this->info;
        $schedule = $info->getScheduleInfo();
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $state = $this->state;

        if ($state->getGeneralStart() > 0) {
            $this->addProperty(
                self::SEC_SCHEDULE,
                $schedule["start_time"]["txt"],
                $schedule["start_time"]["value"]
            );
        }

        if ($state->getCommonDeadline()) {		// if we have a common deadline (target timestamp)
            $this->addProperty(
                self::SEC_SCHEDULE,
                $schedule["until"]["txt"],
                $schedule["until"]["value"]
            );
        } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
            $but = "";
            if ($state->hasGenerallyStarted()) {
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $this->assignment->getId());
                $but = $this->gui->ui()->factory()->button()->primary($lng->txt("exc_start_assignment"), $ilCtrl->getLinkTargetByClass("ilobjexercisegui", "startAssignment"));
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", null);
                $this->setMainAction(
                    self::SEC_SCHEDULE,
                    $but
                );
            }
            $this->addProperty(
                self::SEC_SCHEDULE,
                $schedule["time_after_start"]["txt"],
                $schedule["time_after_start"]["value"]
            );

            if ($state->getLastSubmissionOfRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                $this->addProperty(
                    self::SEC_SCHEDULE,
                    $lng->txt("exc_rel_last_submission"),
                    $state->getLastSubmissionOfRelativeDeadlinePresentation()
                );
            }
        } elseif ($state->needsIndividualDeadline()) {
            if ($state->hasRequestedIndividualDeadline()) {
                $this->addProperty(
                    self::SEC_SCHEDULE,
                    $this->lng->txt("exc_deadline"),
                    $this->lng->txt("exc_idl_tutor_needed")
                );
            } else {
                $this->addProperty(
                    self::SEC_SCHEDULE,
                    $this->lng->txt("exc_deadline"),
                    $this->lng->txt("exc_idl_request_and_tutor_needed")
                );
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $this->assignment->getId());
                $but = $this->gui->ui()->factory()->button()->primary($lng->txt("exc_request_deadline"), $ilCtrl->getLinkTargetByClass("ilobjexercisegui", "requestDeadline"));
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", null);
                $this->setMainAction(
                    self::SEC_SCHEDULE,
                    $but
                );
            }
        }

        if ($state->getOfficialDeadline() > $state->getCommonDeadline()) {
            $this->addProperty(
                self::SEC_SCHEDULE,
                $schedule["individual_deadline"]["txt"],
                $schedule["individual_deadline"]["value"]
            );
        }

        if ($state->hasSubmissionStarted()) {
            $this->addProperty(
                self::SEC_SCHEDULE,
                $schedule["time_to_send"]["txt"],
                $schedule["time_to_send"]["value"]
            );
        }
    }

    protected function builPublicSubmissions(): void
    {
        // submissions are visible, even if other users may still have a larger individual deadline
        if ($this->state->hasSubmissionEnded()) {
            $link = $this->gui->ui()->factory()->link()->standard(
                $this->lng->txt("exc_public_submission"),
                $this->getSubmissionLink("listPublicSubmissions")
            );
            $this->addAction(
                self::SEC_SUBMISSION,
                $link
            );
            $this->addView(
                "public_submissions",
                $this->lng->txt("exc_public_submission"),
                $this->getSubmissionLink("listPublicSubmissions")
            );
        } else {
            $this->addProperty(
                self::SEC_SUBMISSION,
                $this->lng->txt("exc_public_submission"),
                $this->lng->txt("exc_msg_public_submission")
            );
        }
    }

    protected function buildFiles(): void
    {
        $lng = $this->lng;
        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        $ass = $this->ex_ass;
        $files = $ass->getFiles();
        if (count($files) > 0) {
            $cnt = 0;
            foreach ($files as $file) {
                $cnt++;
                // get mime type
                $mime = \ilObjMediaObject::getMimeType($file['fullpath']);
                $output_filename = htmlspecialchars($file['name']);

                if ($this->media_type->isImage($mime)) {
                    $item_id = "il-ex-modal-img-" . $ass->getId() . "-" . $cnt;


                    $image = $ui_renderer->render($ui_factory->image()->responsive($file['fullpath'], $output_filename));
                    $image_lens = \ilUtil::getImagePath("enlarge.svg");

                    $modal = \ilModalGUI::getInstance();
                    $modal->setId($item_id);
                    $modal->setType(\ilModalGUI::TYPE_LARGE);
                    $modal->setBody($image);
                    $modal->setHeading($output_filename);
                    $modal = $modal->getHTML();

                    $img_tpl = new \ilTemplate("tpl.image_file.html", true, true, "components/ILIAS/Exercise");
                    $img_tpl->setCurrentBlock("image_content");
                    $img_tpl->setVariable("MODAL", $modal);
                    $img_tpl->setVariable("ITEM_ID", $item_id);
                    $img_tpl->setVariable("IMAGE", $image);
                    $img_tpl->setvariable("IMAGE_LENS", $image_lens);
                    $img_tpl->setvariable("ALT_LENS", $lng->txt("exc_fullscreen"));
                    $img_tpl->parseCurrentBlock();

                    $this->addProperty(
                        self::SEC_FILES,
                        $output_filename,
                        $img_tpl->get()
                    );

                } elseif ($this->media_type->isAudio($mime) || $this->media_type->isVideo($mime)) {
                    $media_tpl = new \ilTemplate("tpl.media_file.html", true, true, "components/ILIAS/Exercise");

                    if ($this->media_type->isAudio($mime)) {
                        $p = $ui_factory->player()->audio($file['fullpath']);
                    } else {
                        $p = $ui_factory->player()->video($file['fullpath']);
                    }
                    $media_tpl->setVariable("MEDIA", $ui_renderer->render($p));

                    $but = $ui_factory->button()->shy(
                        $lng->txt("download"),
                        $this->getSubmissionLink("downloadFile", array("file" => urlencode($file["name"])))
                    );
                    $media_tpl->setVariable("DOWNLOAD_BUTTON", $ui_renderer->render($but));
                    $this->addProperty(
                        self::SEC_FILES,
                        $output_filename,
                        $media_tpl->get()
                    );
                } else {
                    $l = $ui_factory->link()->standard(
                        $lng->txt("download"),
                        $this->getSubmissionLink("downloadFile", array("file" => urlencode($file["name"])))
                    );
                    $this->addProperty(
                        self::SEC_FILES,
                        $output_filename,
                        $ui_renderer->render($l)
                    );
                }
            }
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function buildSubmission(): void
    {

        if (!$this->submission->canView()) {
            return;
        }

        $this->ctrl->setParameterByClass(
            "ilExSubmissionGUI",
            "ass_id",
            $this->assignment->getId()
        );

        if ($this->submission->getAssignment()->hasTeam()) {
            $team_gui = $this->gui->getTeamSubmissionGUI($this->exc, $this->submission);
            $team_gui->buildSubmissionPropertiesAndActions($this);
        }

        $type_gui = $this->types_gui->getById($this->ex_ass->getType());
        $type_gui->setSubmission($this->submission);
        $type_gui->setExercise($this->exc);
        $type_gui->buildSubmissionPropertiesAndActions($this);

        $last_sub = null;
        if ($this->submission->hasSubmitted()) {
            $last_sub = $this->submission->getLastSubmission();
            if ($last_sub) {
                $last_sub = \ilDatePresentation::formatDate(new \ilDateTime($last_sub, IL_CAL_DATETIME));
                $this->addProperty(
                    self::SEC_SUBMISSION,
                    $this->lng->txt("exc_last_submission"),
                    $last_sub
                );
            }
        } else {
            $this->addProperty(
                self::SEC_SUBMISSION,
                $this->lng->txt("exc_last_submission"),
                $this->lng->txt("exc_no_submission_yet")
            );
        }

        if ($this->exc->getShowSubmissions()) {
            $this->builPublicSubmissions();
        }
    }

    protected function buildPeerFeedback(): void
    {
        if (!$this->submission->canView()) {
            return;
        }
        $peer_review_gui = $this->gui->peerReview()->getPeerReviewGUI(
            $this->ex_ass,
            $this->submission
        );
        $peer_review_gui->buildSubmissionPropertiesAndActions($this);
    }

    protected function buildSampleSolution(): void
    {
        $ass = $this->ex_ass;
        $state = $this->state;
        $submission = $this->submission;

        $last_sub = null;
        if ($submission->hasSubmitted()) {
            $last_sub = $submission->getLastSubmission();
        }

        // global feedback / sample solution
        if ($ass->getFeedbackDate() === \ilExAssignment::FEEDBACK_DATE_DEADLINE) {
            $show_global_feedback = ($state->hasSubmissionEndedForAllUsers() && $ass->getFeedbackFile());
        }
        //If it is not well configured...(e.g. show solution before deadline)
        //the user can get the solution before he summit it.
        //we can check in the elseif $submission->hasSubmitted()
        elseif ($ass->getFeedbackDate() === \ilExAssignment::FEEDBACK_DATE_CUSTOM) {
            $show_global_feedback = ($ass->afterCustomDate() && $ass->getFeedbackFile());
        } else {
            $show_global_feedback = ($last_sub && $ass->getFeedbackFile());
        }
        $this->buildSubmissionFeedback($show_global_feedback);
    }

    protected function buildSubmissionFeedback(
        bool $a_show_global_feedback
    ): void {

        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();

        $ass = $this->ex_ass;
        $feedback_id = $this->submission->getFeedbackId();
        $lng = $this->lng;

        $storage = new \ilFSStorageExercise($ass->getExerciseId(), $ass->getId());
        $cnt_files = $storage->countFeedbackFiles($feedback_id);

        $lpcomment = $ass->getMemberStatus()->getComment();
        $mark = $ass->getMemberStatus()->getMark();
        $status = $ass->getMemberStatus()->getStatus();

        if ($lpcomment != "" ||
            $mark != "" ||
            $status !== "notgraded" ||
            $cnt_files > 0 ||
            $a_show_global_feedback) {

            if ($lpcomment !== "") {
                $this->addProperty(
                    self::SEC_TUTOR_EVAL,
                    $lng->txt("exc_comment"),
                    nl2br($lpcomment)
                );
            }
            if ($mark !== "") {
                $this->addProperty(
                    self::SEC_TUTOR_EVAL,
                    $lng->txt("exc_mark"),
                    $mark
                );
            }

            if ($status !== "" && $status !== "notgraded") {
                $this->addProperty(
                    self::SEC_TUTOR_EVAL,
                    $lng->txt("status"),
                    $lng->txt("exc_" . $status)
                );
            }

            if ($cnt_files > 0) {
                $files = $storage->getFeedbackFiles($feedback_id);
                foreach ($files as $file) {
                    $link = $f->link()->standard(
                        $lng->txt("download"),
                        $this->getSubmissionLink("downloadFeedbackFile", array("file" => urlencode($file)))
                    );
                    $this->addProperty(
                        self::SEC_TUTOR_EVAL,
                        $file,
                        $r->render($link)
                    );
                }
            }

            // #15002 - global feedback
            if ($a_show_global_feedback) {
                $link = $f->link()->standard(
                    $lng->txt("download"),
                    $this->getSubmissionLink("downloadGlobalFeedbackFile")
                );
                $this->addProperty(
                    self::SEC_SAMPLE_SOLUTION,
                    $ass->getFeedbackFile(),
                    $r->render($link)
                );
            }
        }
    }

    /**
     * Get time string for deadline
     * @throws ilDateTimeException
     */
    protected function getTimeString(int $a_deadline): string
    {
        $lng = $this->lng;

        if ($a_deadline == 0) {
            return $lng->txt("exc_submit_convenience_no_deadline");
        }

        if ($a_deadline - time() <= 0) {
            $time_str = $lng->txt("exc_time_over_short");
        } else {
            $time_str = \ilLegacyFormElementsUtil::period2String(new \ilDateTime($a_deadline, IL_CAL_UNIX));
        }

        return $time_str;
    }

    protected function getSubmissionLink(
        string $a_cmd,
        array $a_params = null
    ): string {
        $ilCtrl = $this->ctrl;

        if (is_array($a_params)) {
            foreach ($a_params as $name => $value) {
                $ilCtrl->setParameterByClass("ilexsubmissiongui", $name, $value);
            }
        }

        $ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", $this->assignment->getId());
        $url = $ilCtrl->getLinkTargetByClass([\ilAssignmentPresentationGUI::class, "ilexsubmissiongui"], $a_cmd);
        $ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");

        if (is_array($a_params)) {
            foreach ($a_params as $name => $value) {
                $ilCtrl->setParameterByClass("ilexsubmissiongui", $name, "");
            }
        }

        return $url;
    }

    /**
     * Get the rendered icon for a status (failed, passed or not graded).
     */
    protected function getIconForStatus(string $status, int $variant = \ilLPStatusIcons::ICON_VARIANT_LONG): string
    {
        $icons = \ilLPStatusIcons::getInstance($variant);
        $lng = $this->lng;

        switch ($status) {
            case "passed":
                return $icons->renderIcon(
                    $icons->getImagePathCompleted(),
                    $lng->txt("exc_" . $status)
                );

            case "failed":
                return $icons->renderIcon(
                    $icons->getImagePathFailed(),
                    $lng->txt("exc_" . $status)
                );

            default:
                return $icons->renderIcon(
                    $icons->getImagePathNotAttempted(),
                    $lng->txt("exc_" . $status)
                );
        }
    }
}
