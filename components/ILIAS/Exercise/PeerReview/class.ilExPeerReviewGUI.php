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
 * Class ilExPeerReviewGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilExPeerReviewGUI: ilFileSystemGUI, ilRatingGUI, ilExSubmissionTextGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilExPeerReviewGUI: ilMessageGUI
 */
class ilExPeerReviewGUI
{
    protected int $requested_giver_id;
    protected \ILIAS\Exercise\Notification\NotificationManager $notification;
    protected ilFSStorageExercise $fstorage;
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\Exercise\InternalGUIService $gui;
    protected \ILIAS\Exercise\InternalDomainService $domain;
    protected ilCtrl $ctrl;
    protected ilTabsGUI $tabs_gui;
    protected ilLanguage $lng;
    protected ilGlobalPageTemplate $tpl;
    protected ilObjUser $user;
    protected ilExAssignment $ass;
    protected ?ilExSubmission $submission;
    protected int $requested_review_giver_id = 0;
    protected int $requested_review_peer_id = 0;
    protected string $requested_review_crit_id = "";
    protected int $requested_peer_id = 0;
    protected string $requested_crit_id = "";

    public function __construct(
        ilExAssignment $a_ass,
        ilExSubmission $a_submission = null
    ) {
        global $DIC;

        $service = $DIC->exercise()->internal();
        $this->domain = $service->domain();
        $this->gui = $service->gui();
        $this->user = $this->domain->user();
        $this->ctrl = $this->gui->ctrl();
        $this->tabs_gui = $this->gui->tabs();
        $this->lng = $this->domain->lng();
        $this->tpl = $this->gui->ui()->mainTemplate();

        $this->ass = $a_ass;
        $this->submission = $a_submission;

        $request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_review_giver_id = $request->getReviewGiverId();
        $this->requested_review_peer_id = $request->getReviewPeerId();
        $this->requested_review_crit_id = $request->getReviewCritId();
        $this->requested_peer_id = $request->getPeerId();
        $this->requested_giver_id = $request->getGiverId();
        $this->requested_crit_id = $request->getCritId();
        $this->gui = $DIC->exercise()->internal()->gui();
        $this->notes = $DIC->notes();
        $this->ctrl->saveParameter($this, array("peer_id"));
        $this->notification = $this->domain->notification($request->getRefId());
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs_gui;

        if (!$this->ass->getPeerReview()) {
            $this->returnToParentObject();
        }

        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("showpeerreviewoverview");

        switch ($class) {
            case "ilfilesystemgui":
                $ilCtrl->saveParameter($this, array("fu"));

                // see self::downloadPeerReview()
                $giver_id = $this->requested_review_giver_id;
                $peer_id = $this->requested_review_peer_id;

                if (!$this->canGive()) {
                    $this->returnToParentObject();
                }

                $valid = false;
                $peer_items = $this->submission->getPeerReview()->getPeerReviewsByPeerId($peer_id, true);
                if (is_array($peer_items)) {
                    foreach ($peer_items as $item) {
                        if ($item["giver_id"] == $giver_id) {
                            $valid = true;
                        }
                    }
                }
                if (!$valid) {
                    $ilCtrl->redirect($this, "editPeerReview");
                }

                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "editPeerReview")
                );

                $fstorage = new ilFSStorageExercise($this->ass->getExerciseId(), $this->ass->getId());
                $fstorage->create();

                $fs_gui = new ilFileSystemGUI($fstorage->getPeerReviewUploadPath($peer_id, $giver_id));
                $fs_gui->setTableId("excfbpeer");
                $fs_gui->setAllowDirectories(false);
                $fs_gui->setTitle($this->ass->getTitle() . ": " .
                    $lng->txt("exc_peer_review") . " - " .
                    $lng->txt("exc_peer_review_give"));
                $this->ctrl->forwardCommand($fs_gui);
                break;

            case "ilratinggui":
                $peer_review = new ilExPeerReview($this->ass);
                $peer_review->updatePeerReviewTimestamp($this->requested_peer_id);

                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject(
                    $this->ass->getId(),
                    "ass",
                    $this->requested_peer_id,
                    "peer"
                );
                $this->ctrl->forwardCommand($rating_gui);
                $ilCtrl->redirect($this, "editPeerReview");
                break;

            case "ilexsubmissiontextgui":
                $ilTabs->clearTargets();
                if (!$this->submission->isTutor()) {
                    $ilTabs->setBackTarget(
                        $lng->txt("back"),
                        $ilCtrl->getLinkTarget($this, "editPeerReview")
                    );
                    $this->ctrl->setReturn($this, "editPeerReview");
                } else {
                    $ilTabs->setBackTarget(
                        $lng->txt("back"),
                        $ilCtrl->getLinkTarget($this, "showGivenPeerReview")
                    );
                    $this->ctrl->setReturn($this, "showGivenPeerReview");
                }
                $gui = new ilExSubmissionTextGUI(new ilObjExercise($this->ass->getExerciseId(), false), $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;

            case "ilmessagegui":
                $gui = $this->getMessagesGUI(
                    $this->requested_giver_id,
                    $this->requested_peer_id
                );
                $ilCtrl->forwardCommand($gui);
                break;

            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }

    public function returnToParentObject(): void
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * @throws ilDateTimeException
     */
    public static function getOverviewContent(
        ilInfoScreenGUI $a_info,
        ilExSubmission $a_submission
    ): void {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $gui = $DIC->exercise()
            ->internal()
            ->gui();

        $state = ilExcAssMemberState::getInstanceByIds($a_submission->getAssignment()->getId(), $a_submission->getUserId());

        $ass = $a_submission->getAssignment();

        $view_pc = "";
        $edit_pc = "";


        //if($ass->afterDeadlineStrict() &&
        //	$ass->getPeerReview())
        if ($state->hasSubmissionEndedForAllUsers() &&
            $ass->getPeerReview()) {
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "ass_id", $a_submission->getAssignment()->getId());

            $nr_missing_fb = $a_submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived();

            // before deadline (if any)
            // if(!$ass->getPeerReviewDeadline() ||
            //  	$ass->getPeerReviewDeadline() > time())
            if ($state->isPeerReviewAllowed()) {
                $dl_info = "";
                if ($ass->getPeerReviewDeadline()) {
                    $dl_info = " (" . sprintf(
                        $lng->txt("exc_peer_review_deadline_info_button"),
                        $state->getPeerReviewDeadlinePresentation()
                    ) . ")";
                }

                $b = $gui->link(
                    $lng->txt("exc_peer_review_give"),
                    $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExPeerReviewGUI"), "editPeerReview")
                )->emphasised();
                if ($nr_missing_fb) {
                    $b = $b->primary();
                }
                $edit_pc = $b->render();
            } elseif ($ass->getPeerReviewDeadline()) {
                $edit_pc = $lng->txt("exc_peer_review_deadline_reached");
            }

            // after deadline (if any)
            if ((!$ass->getPeerReviewDeadline() ||
                $ass->getPeerReviewDeadline() < time())) {
                // given peer review should be accessible at all times (read-only when not editable - see above)
                if ($ass->getPeerReviewDeadline() &&
                    $a_submission->getPeerReview()->countGivenFeedback(false)) {
                    $b = $gui->link(
                        $lng->txt("exc_peer_review_given"),
                        $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExPeerReviewGUI"), "showGivenPeerReview")
                    )->emphasised();
                    $view_pc = $b->render() . " ";
                }

                // did give enough feedback
                if (!$nr_missing_fb) {
                    // received any?
                    $received = (bool) sizeof($a_submission->getPeerReview()->getPeerReviewsByPeerId($a_submission->getUserId(), true));
                    if ($received) {
                        $b = $gui->link(
                            $lng->txt("exc_peer_review_show"),
                            $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExPeerReviewGUI"), "showReceivedPeerReview")
                        )->emphasised();
                        $view_pc .= $b->render();
                    }
                    // received none
                    else {
                        $view_pc .= $lng->txt("exc_peer_review_show_received_none");
                    }
                }
                // did not give enough
                else {
                    $view_pc .= $lng->txt("exc_peer_review_show_missing");
                }
            }
            /* must give before showing received
            else
            {
                $view_pc = $lng->txt("exc_peer_review_show_not_rated_yet");
            }
            */

            $sep = ($edit_pc != "" && $view_pc != "")
                ? "<br><br>"
                : "";

            $a_info->addProperty($lng->txt("exc_peer_review"), $edit_pc . $sep . $view_pc);

            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "ass_id", "");
        }
    }

    public function buildSubmissionPropertiesAndActions(\ILIAS\Exercise\Assignment\PropertyAndActionBuilderUI $builder): void
    {
        $f = $this->gui->ui()->factory();
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $submission = $this->submission;
        $ass = $submission->getAssignment();
        $state = $this->domain->assignment()->state($ass->getId(), $submission->getUserId());

        $view_pc = "";
        $edit_pc = "";


        //if($ass->afterDeadlineStrict() &&
        //	$ass->getPeerReview())
        if ($state->hasSubmissionEndedForAllUsers() &&
            $ass->getPeerReview()) {
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "ass_id", $submission->getAssignment()->getId());

            $nr_missing_fb = $submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived();

            $builder->addAdditionalHeadProperty(
                $this->lng->txt("exc_given_feedback"),
                $submission->getPeerReview()->countGivenFeedback() . " " .
                $this->lng->txt("exc_x_of_y") . " " .
                $submission->getAssignment()->getPeerReviewMin()
            );

            $builder->addAdditionalHeadProperty(
                $this->lng->txt("exc_received_feedback"),
                $submission->getPeerReview()->countReceivedFeedbacks($submission->getUserid())
            );

            // before deadline (if any)
            // if(!$ass->getPeerReviewDeadline() ||
            //  	$ass->getPeerReviewDeadline() > time())
            if ($state->isPeerReviewAllowed()) {
                $url = $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExPeerReviewGUI"), "editPeerReview");
                $button = $f->button()->primary(
                    $lng->txt("exc_peer_review_give"),
                    $url
                );
                $builder->addView(
                    "give_feedback",
                    $this->lng->txt("exc_given_feedback"),
                    $url
                );
                $builder->setMainAction(
                    $builder::SEC_PEER_FEEDBACK,
                    $button
                );
                if ($ass->getPeerReviewDeadline()) {
                    $deadline = $state->getPeerReviewDeadlinePresentation();
                } else {
                    $deadline = $lng->txt("exc_no_peer_feedback_deadline");
                }
                $builder->addProperty(
                    $builder::SEC_PEER_FEEDBACK,
                    $lng->txt("exc_peer_review_deadline"),
                    $deadline
                );
            } elseif ($ass->getPeerReviewDeadline()) {
                $edit_pc = $lng->txt("exc_peer_review_deadline_reached");
                $builder->addProperty(
                    $builder::SEC_PEER_FEEDBACK,
                    $lng->txt("exc_peer_review_deadline"),
                    $lng->txt("exc_peer_review_deadline_reached")
                );
            }
            if ($ass->getPeerReviewDeadline()) {
                $builder->addProperty(
                    $builder::SEC_SCHEDULE,
                    $lng->txt("exc_peer_review_deadline"),
                    $state->getPeerReviewDeadlinePresentation()
                );
            }

            // after deadline (if any)
            if ((!$ass->getPeerReviewDeadline() ||
                $ass->getPeerReviewDeadline() < time())) {
                // given peer review should be accessible at all times (read-only when not editable - see above)
                if ($ass->getPeerReviewDeadline() &&
                    $submission->getPeerReview()->countGivenFeedback(false)) {
                    $url = $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExPeerReviewGUI"), "showGivenPeerReview");
                    $link = $f->link()->standard(
                        $lng->txt("exc_peer_review_given"),
                        $url
                    );
                    $builder->addAction(
                        $builder::SEC_PEER_FEEDBACK,
                        $link
                    );
                    $builder->addView(
                        "give_feedback",
                        $this->lng->txt("exc_given_feedback"),
                        $url
                    );
                }

                if ($nr_missing_fb <= 0) {
                    // received any?
                    $received = (bool) sizeof($submission->getPeerReview()->getPeerReviewsByPeerId($submission->getUserId(), true));
                    if ($received) {
                        $url = $ilCtrl->getLinkTargetByClass(array(ilAssignmentPresentationGUI::class, "ilExSubmissionGUI", "ilExPeerReviewGUI"), "showReceivedPeerReview");
                        $link = $f->link()->standard(
                            $lng->txt("exc_peer_review_show"),
                            $url
                        );
                        $builder->addAction(
                            $builder::SEC_PEER_FEEDBACK,
                            $link
                        );
                        $builder->addView(
                            "receive_feedback",
                            $this->lng->txt("exc_received_feedback"),
                            $url
                        );
                    }
                    // received none
                    else {
                        $builder->addProperty(
                            $builder::SEC_PEER_FEEDBACK,
                            $lng->txt("exc_received_peer_feedback"),
                            $lng->txt("exc_peer_review_show_received_none")
                        );
                    }
                }
                // did not give enough
                else {
                    $builder->addProperty(
                        $builder::SEC_PEER_FEEDBACK,
                        $lng->txt("exc_peer_feedback_status"),
                        $lng->txt("exc_peer_review_show_missing")
                    );
                }
            }
            $ilCtrl->setParameterByClass("ilExPeerReviewGUI", "ass_id", "");
        }
    }

    protected function canGive(): bool
    {
        return ($this->submission->isOwner() &&
            $this->ass->afterDeadlineStrict() &&
            (!$this->ass->getPeerReviewDeadline() ||
                $this->ass->getPeerReviewDeadline() > time()));
    }

    protected function canView(): bool
    {
        return ($this->submission->isTutor() ||
            ($this->submission->isOwner() &&
            $this->ass->afterDeadlineStrict() &&
            (!$this->ass->getPeerReviewDeadline() ||
                $this->ass->getPeerReviewDeadline() < time())));
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     */
    public function showGivenPeerReviewObject(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->tabs_gui->activateTab("give_feedback");

        if (!$this->canView()) {
            $this->returnToParentObject();
        }

        $peer_items = $this->submission->getPeerReview()->getPeerReviewsByGiver($this->submission->getUserId());
        if ($peer_items === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_peer_review_no_peers"), true);
            $this->returnToParentObject();
        }

        $tpl->setTitle($this->ass->getTitle() . ": " . $lng->txt("exc_peer_review_given"));

        $this->gui->permanentLink()->setGivenFeedbackPermanentLink();

        $panel = $this->getReceivedFeedbackPanel($peer_items);

        $tpl->setContent($this->gui->ui()->renderer()->render($panel));
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     */
    public function showReceivedPeerReviewObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $this->tabs_gui->activateTab("receive_feedback");

        if (!$this->canView() ||
            (!$this->submission->isTutor() &&
            $this->submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived())) {
            $this->returnToParentObject();
        }

        $this->gui->permanentLink()->setReceivedFeedbackPermanentLink();

        /*
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "returnToParent"));*/

        $peer_items = $this->submission->getPeerReview()->getPeerReviewsByPeerId($this->submission->getUserId(), !$this->submission->isTutor());
        if ($peer_items === []) {
            // #11373
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_peer_review_no_peers_reviewed_yet"), true);
            $ilCtrl->redirect($this, "returnToParent");
        }

        $tpl->setTitle($this->ass->getTitle() . ": " . $lng->txt("exc_peer_review_show"));

        $info_widget = new ilInfoScreenGUI($this);

        $panel = $this->getReceivedFeedbackPanel($peer_items, true);

        $tpl->setContent($this->gui->ui()->renderer()->render($panel));
    }

    protected function getReceivedFeedbackPanel(
        array $a_peer_items,
        bool $a_by_peer = false
    ): \ILIAS\UI\Component\Panel\Standard {
        $lng = $this->lng;

        $sub_panels = [];
        if ($this->submission->isTutor()) {
            $props = [];
            $user_title = $a_by_peer
                ? $lng->txt("exc_peer_review_recipient")
                : $lng->txt("exc_peer_review_giver");
            $props[] = [
                "prop" => $lng->txt("name"),
                "value" => ilUserUtil::getNamePresentation($this->submission->getUserId(), false, false, "", true)
            ];
            $sub_panels[] = $this->getSubPanel(
                $user_title,
                $props
            );
        }

        if ($a_by_peer) {

            // list received feedbacks

            // submission
            $props = [];
            $submission = new ilExSubmission($this->ass, $this->submission->getUserId());
            $file_info = $submission->getDownloadedFilesInfoForTableGUIS();

            $props[] = [
                "prop" => $file_info["last_submission"]["txt"],
                "value" => $file_info["last_submission"]["value"] .
                    $this->getLateSubmissionInfo($submission)
            ];

            $sub_data = $this->getSubmissionContent($submission);
            if ($sub_data === '' || $sub_data === '0') {
                $sub_data = '<a href="' . $file_info["files"]["download_url"] . '">' . $lng->txt("download") . '</a>';
            }
            $props[] = [
                "prop" => $lng->txt("exc_submission"),
                "value" => $sub_data
            ];

            $sub_panels[] = $this->getSubPanel(
                $lng->txt("exc_your_submission"),
                $props
            );
        }

        foreach ($a_peer_items as $peer) {
            $props = [];
            if (!$a_by_peer) {
                $giver_id = $this->submission->getUserId();
                $peer_id = $peer["peer_id"];
                $id_title = $lng->txt("exc_peer_review_recipient");
                $user_id = $peer_id;
            } else {

                // list received feedbacks

                $giver_id = $peer["giver_id"];
                $peer_id = $this->submission->getUserId();
                $id_title = $lng->txt("exc_peer_review_giver");
                $user_id = $giver_id;
            }

            // peer info
            if ($this->submission->isTutor()) {
                $id_value = ilUserUtil::getNamePresentation($user_id, "", "", false, true);
            } elseif (!$this->ass->hasPeerReviewPersonalized()) {
                $id_value = $peer["seq"];
            } else {
                $id_value = ilUserUtil::getNamePresentation($user_id);
            }

            $mess_gui = $this->getMessagesGUI(
                $giver_id,
                $peer_id
            );

            // submission info

            if (!$a_by_peer) {
                $submission = new ilExSubmission($this->ass, $peer_id);
                $file_info = $submission->getDownloadedFilesInfoForTableGUIS();

                $props[] = [
                    "prop" => $file_info["last_submission"]["txt"],
                    "value" => $file_info["last_submission"]["value"] .
                        $this->getLateSubmissionInfo($submission)
                ];

                $sub_data = $this->getSubmissionContent($submission);
                if ($sub_data === '' || $sub_data === '0') {
                    if (isset($file_info["files"]["download_url"])) {
                        $sub_data = '<a href="' . $file_info["files"]["download_url"] . '">' . $lng->txt("download") . '</a>';
                    }
                }
                $props[] = [
                    "prop" => $lng->txt("exc_submission"),
                    "value" => $sub_data
                ];
            }


            // peer review items

            $values = $this->submission->getPeerReview()->getPeerReviewValues($giver_id, $peer_id);

            foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $item) {
                $crit_id = $item->getId()
                    ? $item->getId()
                    : $item->getType();

                $item->setPeerReviewContext(
                    $this->ass,
                    $giver_id,
                    $peer_id
                );

                $title = $item->getTitle();
                $html = $item->getHTML($values[$crit_id] ?? null);
                $props[] = [
                    "prop" => $title ?: "&nbsp;",
                    "value" => $html ?: "&nbsp;"
                ];
            }
            $sub_panels[] = $this->getSubPanel(
                $id_title . ": " . $id_value,
                $props,
                $mess_gui
            );
        }
        return $this->gui->ui()->factory()->panel()->standard(
            $a_by_peer
                ? $this->lng->txt("exc_received_feedback")
                : $this->lng->txt("exc_given_feedback"),
            $sub_panels
        );
    }

    protected function getSubPanel(
        string $title,
        array $props,
        ?ilMessageGUI $mess_gui = null
    ): \ILIAS\UI\Component\Panel\Sub {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $tpl = new \ilTemplate("tpl.panel_items.html", true, true, "components/ILIAS/Exercise/PeerReview");
        foreach ($props as $prop) {
            $tpl->setCurrentBlock("entry");
            $tpl->setVariable("LABEL", $prop["prop"]);
            $tpl->setVariable("VALUE", $prop["value"]);
            $tpl->parseCurrentBlock();
        }
        $mess_html = "";
        if ($mess_gui) {
            $mess_html = $r->render($f->divider()->horizontal()) .
                $mess_gui->getWidget();
        }

        return $f->panel()->sub(
            $title,
            $f->legacy($tpl->get() . $mess_html)
        );
    }

    protected function getLateSubmissionInfo(
        ilExSubmission $a_submission
    ): string {
        $lng = $this->lng;

        // #18966 - late files info
        foreach ($a_submission->getFiles() as $file) {
            if ($file["late"]) {
                return '<div class="warning">' . $lng->txt("exc_late_submission") . '</div>';
            }
        }
        return "";
    }

    /**
     * @throws ilDateTimeException
     */
    public function editPeerReviewObject(): void
    {
        $tpl = $this->tpl;

        $this->tabs_gui->activateTab("give_feedback");
        if (!$this->canGive()) {
            $this->returnToParentObject();
        }

        $peer_items = $this->submission->getPeerReview()->getPeerReviewsByGiver($this->submission->getUserId());
        if ($peer_items === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("exc_peer_review_no_peers"), true);
            $this->returnToParentObject();
        }

        $missing = $this->submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived();
        if ($missing !== 0) {
            $dl = $this->ass->getPeerReviewDeadline();
            if (!$dl || $dl < time()) {
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("exc_peer_review_missing_info"), $missing));
            } else {
                $this->tpl->setOnScreenMessage('info', sprintf(
                    $this->lng->txt("exc_peer_review_missing_info_deadline"),
                    $missing,
                    ilDatePresentation::formatDate(new ilDateTime($dl, IL_CAL_UNIX))
                ));
            }
        }

        /*$tbl = new ilExAssignmentPeerReviewTableGUI(
            $this,
            "editPeerReview",
            $this->ass,
            $this->submission->getUserId(),
            $peer_items
        );*/
        $panel = $this->getPeerReviewReceiverPanel(
            $this->ass,
            $this->submission->getUserId(),
            $peer_items
        );
        $tpl->setContent($this->gui->ui()->renderer()->render($panel));
    }

    protected function getPeerReviewReceiverPanel(
        ilExAssignment $ass,
        int $user_id,
        array $peer_data
    ): \ILIAS\UI\Component\Panel\Listing\Standard {
        if ($ass->hasPeerReviewFileUpload()) {
            $this->fstorage = new ilFSStorageExercise($ass->getExerciseId(), $ass->getId());
            $this->fstorage->create();
        }
        $personal = $ass->hasPeerReviewPersonalized();
        $f = $this->gui->ui()->factory();
        $lng = $this->lng;
        $items = [];
        foreach ($peer_data as $item) {
            $row = array();

            if (\ilObject::_lookupType($item["peer_id"]) !== "usr") {
                continue;
            }

            $row["giver_id"] = $item["giver_id"];
            $row["peer_id"] = $item["peer_id"];
            $row["tstamp"] = $item["tstamp"];

            if (!$personal) {
                $row["seq"] = $item["seq"];
            } else {
                $row["name"] = ilUserUtil::getNamePresentation($item["peer_id"]);
            }

            // validate
            $row["valid"] = $all_empty = true;
            $submission = new ilExSubmission($ass, $item["peer_id"]);
            $values = $submission->getPeerReview()->getPeerReviewValues($item["giver_id"], $item["peer_id"]);
            foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $crit) {
                $crit_id = $crit->getId()
                    ? $crit->getId()
                    : $crit->getType();
                $crit->setPeerReviewContext(
                    $this->ass,
                    $item["giver_id"],
                    $item["peer_id"]
                );
                if (!$crit->validate($values[$crit_id] ?? null)) {
                    $row["valid"] = false;
                }
                if ($crit->hasValue($values[$crit_id] ?? null)) {
                    $all_empty = false;
                }
            }
            if ($all_empty) {
                $row["valid"] = false;
            }
            if (!$row["valid"]) {
                //                $this->invalid++;
            }

            $ilCtrl = $this->ctrl;

            if (isset($row["seq"])) {
                $title = $this->lng->txt("exc_peer_review_recipient") . " " . $row["seq"];
            } else {
                $title = $row["name"];
            }

            if ($row["tstamp"]) {
                $row["tstamp"] = ilDatePresentation::formatDate(new ilDateTime($row["tstamp"], IL_CAL_DATETIME));
            }
            $props = [];
            $props[$lng->txt("last_update")] = $row["tstamp"];

            $props[$lng->txt("valid")] = $row["valid"]
                    ? $this->lng->txt("yes")
                    : $this->lng->txt("no");

            $ilCtrl->setParameter($this, "peer_id", $row["peer_id"]);
            $actions = [
                $f->button()->shy(
                    $this->lng->txt("edit"),
                    $ilCtrl->getLinkTarget($this, "editPeerReviewItem")
                )
            ];
            $ilCtrl->setParameter($this, "pid", "");

            $items[] = $f->item()->standard(
                $title
            )->withProperties($props)
                ->withActions($f->dropdown()->standard($actions));
        }

        $group = $f->item()->group("", $items);
        return $f->panel()->listing()->standard(
            $this->lng->txt("exc_peer_review_give"),
            [$group]
        );
    }

    public function editPeerReviewItemObject(
        ilPropertyFormGUI $a_form = null
    ): void {
        $tpl = $this->tpl;

        $this->tabs_gui->activateTab("give_feedback");

        if (!$this->canGive() && $this->canView()) {
            $this->ctrl->redirectByClass(self::class, "showGivenPeerReview");
        }

        if (!$this->canGive() ||
            !$this->isValidPeer($this->requested_peer_id)) {
            $this->returnToParentObject();
        }

        if ($a_form === null) {
            $a_form = $this->initPeerReviewItemForm($this->requested_peer_id);
        }

        $sep = $this->gui->ui()->renderer()->render($this->gui->ui()->factory()->divider()->horizontal());
        $peer_state = ilExcAssMemberState::getInstanceByIds($this->ass->getId(), $this->requested_peer_id);
        $message_html = "";

        // show only message gui, if peer is able to access received feedback
        if ($peer_state->isReceivedFeedbackAccessible()) {
            $message_gui = $this->getMessagesGUI(
                $this->user->getId(),
                $this->requested_peer_id
            );
            $message_html = $sep . $message_gui->getListHTML();
        }

        $this->gui->permanentLink()->setGivenFeedbackPermanentLink();

        $tpl->setContent($a_form->getHTML() . $message_html);
    }

    protected function getMessagesGUI(int $giver_id, int $peer_id): ilMessageGUI
    {
        $this->ctrl->setParameter($this, "giver_id", $giver_id);
        $this->ctrl->setParameter($this, "peer_id", $peer_id);
        $pr = $this->domain->peerReview($this->ass);
        $gui = $this->notes->gui()->getMessagesGUI(
            $peer_id,
            $this->ass->getExerciseId(),
            $pr->getReviewId($giver_id, $peer_id),
            "excpf"
        );
        if (!$this->ass->hasPeerReviewPersonalized()) {
            if ($giver_id === $this->user->getId()) {
                $counterpart_name = $this->lng->txt("exc_peer_review_recipient");
            } else {
                $counterpart_name = $this->lng->txt("exc_peer_review_giver");
            }
            $gui->setAnonymised(true, $counterpart_name);
        }

        $gui->addObserver(function (
            int $exc_id,
            int $pf_id,
            string $type,
            string $action,
            int $note_id
        ) use ($giver_id, $peer_id) {
            $this->observeMessageAction(
                $giver_id,
                $peer_id,
                $note_id,
                $action
            );
        });

        return $gui;
    }

    public function observeMessageAction(
        int $giver_id,
        int $peer_id,
        int $note_id,
        string $action
    ): void {
        if ($action !== "new") {
            return;
        }
        $note = $this->notes->domain()->getById($note_id);
        $text = $note->getText();

        if ($note->getAuthor() === $giver_id) {
            $this->notification->sendMessageFromPeerfeedbackGiverNotification(
                $this->ass->getId(),
                $peer_id,
                $text
            );
        } else {
            $this->notification->sendMessageFromPeerfeedbackRecipientNotification(
                $this->ass->getId(),
                $peer_id,
                $giver_id,
                $text
            );
        }
    }


    protected function isValidPeer(int $a_peer_id): bool
    {
        $peer_items = $this->submission->getPeerReview()->getPeerReviewsByGiver($this->submission->getUserId());
        foreach ($peer_items as $item) {
            if ($item["peer_id"] == $a_peer_id) {
                return true;
            }
        }
        return false;
    }

    protected function getSubmissionContent(
        ilExSubmission $a_submission
    ): string {
        if ($this->ass->getType() != ilExAssignment::TYPE_TEXT) {
            return "";
        }

        $text = $a_submission->getFiles();
        if ($text !== []) {
            $text = array_shift($text);
            if (trim($text["atext"]) !== '' && trim($text["atext"]) !== '0') {
                // mob id to mob src
                return nl2br(ilRTE::_replaceMediaObjectImageSrc($text["atext"], 1));
            }
        }
        return "";
    }

    /**
     * @throws ilDateTimeException
     */
    protected function initPeerReviewItemForm(
        int $a_peer_id
    ): ilPropertyFormGUI {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // get peer data
        $peer_items = $this->submission->getPeerReview()->getPeerReviewsByGiver($this->submission->getUserId());
        $peer = [];
        foreach ($peer_items as $item) {
            if ($item["peer_id"] == $a_peer_id) {
                $peer = $item;
                break;
            }
        }

        $ilCtrl->saveParameter($this, "peer_id");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "updatePeerReview"));

        $form->setTitle($this->ass->getTitle() . ": " . $lng->txt("exc_peer_review_give"));

        // peer info
        if (!$this->ass->hasPeerReviewPersonalized()) {
            $id_title = $lng->txt("id");
            $id_value = $peer["seq"];
        } else {
            $id_title = $lng->txt("exc_peer_review_recipient");
            $id_value = ilUserUtil::getNamePresentation($peer["peer_id"]);
        }
        $id = new ilNonEditableValueGUI($id_title);
        $id->setValue($id_value);
        $form->addItem($id);

        // submission info

        $submission = new ilExSubmission($this->ass, $peer["peer_id"]);
        $file_info = $submission->getDownloadedFilesInfoForTableGUIS();

        $last_sub = new ilNonEditableValueGUI($file_info["last_submission"]["txt"], "", true);
        $last_sub->setValue($file_info["last_submission"]["value"] .
            $this->getLateSubmissionInfo($submission));
        $form->addItem($last_sub);

        $sub_data = $this->getSubmissionContent($submission);
        if (($sub_data === '' || $sub_data === '0') && isset($file_info["files"]["download_url"])) {
            $sub_data = '<a href="' . $file_info["files"]["download_url"] . '">' . $lng->txt("download") . '</a>';
        }

        $sub = new ilNonEditableValueGUI($lng->txt("exc_submission"), "", true);
        $sub->setValue($sub_data);
        $form->addItem($sub);

        // peer review items

        $input = new ilFormSectionHeaderGUI();
        $input->setTitle($lng->txt("exc_peer_review"));
        $form->addItem($input);

        $values = $this->submission->getPeerReview()->getPeerReviewValues($this->submission->getUserId(), $a_peer_id);

        foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $item) {
            $crit_id = $item->getId()
                ? $item->getId()
                : $item->getType();

            $item->setPeerReviewContext(
                $this->ass,
                $this->submission->getUserId(),
                $peer["peer_id"],
                $form
            );
            $item->addToPeerReviewForm($values[$crit_id] ?? null);
        }

        $form->addCommandButton("updatePeerReview", $lng->txt("save"));
        $form->addCommandButton("editPeerReview", $lng->txt("cancel"));

        return $form;
    }

    public function updateCritAjaxObject(): void
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $tpl = $this->tpl;

        if (!$this->canGive() ||
            !$this->requested_peer_id ||
            !$this->requested_crit_id ||
            !$ilCtrl->isAsynch()) {
            exit();
        }

        $peer_id = $this->requested_peer_id;
        $crit_id = $this->requested_crit_id;
        $giver_id = $ilUser->getId();

        if (!is_numeric($crit_id)) {
            $crit = ilExcCriteria::getInstanceByType($crit_id);
        } else {
            $crit = ilExcCriteria::getInstanceById($crit_id);
        }
        $crit->setPeerReviewContext($this->ass, $giver_id, $peer_id);
        $html = $crit->updateFromAjax();

        $this->handlePeerReviewChange();

        echo $html;
        echo $tpl->getOnLoadCodeForAsynch();
        exit();
    }

    public function updatePeerReviewObject(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->canGive() ||
            !$this->isValidPeer($this->requested_peer_id)) {
            $this->returnToParentObject();
        }

        $peer_id = $this->requested_peer_id;

        $form = $this->initPeerReviewItemForm($peer_id);
        if ($form->checkInput()) {
            $valid = true;

            $values = array();
            foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $item) {
                $item->setPeerReviewContext(
                    $this->ass,
                    $this->submission->getUserId(),
                    $peer_id,
                    $form
                );
                $value = $item->importFromPeerReviewForm();
                if ($value !== null) {
                    $crit_id = $item->getId()
                        ? $item->getId()
                        : $item->getType();
                    $values[$crit_id] = $value;
                }
                if (!$item->validate($value)) {
                    $valid = false;
                }
            }

            if ($valid) {
                $this->submission->getPeerReview()->updatePeerReview($peer_id, $values);

                $this->handlePeerReviewChange();

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_peer_review_updated"), true);
                $ilCtrl->redirect($this, "editPeerReview");
            } else {
                $this->tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
            }
        }

        $form->setValuesByPost();
        $this->editPeerReviewItemObject($form);
    }

    protected function handlePeerReviewChange(): void
    {
        // (in)valid peer reviews could change assignment status
        $exercise = new ilObjExercise($this->ass->getExerciseId(), false);
        $exercise->processExerciseStatus(
            $this->ass,
            $this->submission->getUserIds(),
            $this->submission->hasSubmitted(),
            $this->submission->validatePeerReviews()
        );
    }

    public function downloadPeerReviewObject(): void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->canView() &&
            !$this->canGive()) {
            $this->returnToParentObject();
        }

        $giver_id = $this->requested_review_giver_id;
        $peer_id = $this->requested_review_peer_id;
        $crit_id = $this->requested_review_crit_id;

        if (!is_numeric($crit_id)) {
            $crit = ilExcCriteria::getInstanceByType($crit_id);
        } else {
            $crit = ilExcCriteria::getInstanceById($crit_id);
        }

        $crit->setPeerReviewContext($this->ass, $giver_id, $peer_id);
        $file = $crit->getFileByHash();
        if ($file) {
            ilFileDelivery::deliverFileLegacy($file, basename($file));
        }

        $ilCtrl->redirect($this, "returnToParent");
    }



    //
    // ADMIN
    //

    public function showPeerReviewOverviewObject(): void
    {
        $tpl = $this->tpl;

        if (!$this->ass ||
            !$this->ass->getPeerReview()) {
            $this->returnToParentObject();
        }

        $tbl = new ilExAssignmentPeerReviewOverviewTableGUI(
            $this,
            "showPeerReviewOverview",
            $this->ass
        );

        $panel = "";
        $panel_data = $tbl->getPanelInfo();
        if (is_array($panel_data) && count($panel_data) > 0) {
            $ptpl = new ilTemplate("tpl.exc_peer_review_overview_panel.html", true, true, "components/ILIAS/Exercise");
            foreach ($panel_data as $item) {
                $ptpl->setCurrentBlock("user_bl");
                foreach ($item["value"] as $user) {
                    $ptpl->setVariable("USER", $user);
                    $ptpl->parseCurrentBlock();
                }

                $ptpl->setCurrentBlock("item_bl");
                $ptpl->setVariable("TITLE", $item["title"]);
                $ptpl->parseCurrentBlock();
            }

            $f = $this->gui->ui()->factory();
            $r = $this->gui->ui()->renderer();
            $p = $f->panel()->standard(
                $this->lng->txt("exc_peer_review_overview_invalid_users"),
                $f->legacy($ptpl->get())
            );

            $panel = $r->render($p);
        }

        $tpl->setContent($tbl->getHTML() . $panel);
    }

    public function confirmResetPeerReviewObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs_gui;

        if (!$this->ass ||
            !$this->ass->getPeerReview()) {
            $this->returnToParentObject();
        }

        $ilTabs->clearTargets();

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setHeaderText(sprintf($this->lng->txt("exc_peer_review_reset_sure"), $this->ass->getTitle()));
        $cgui->setCancel($this->lng->txt("cancel"), "showPeerReviewOverview");
        $cgui->setConfirm($this->lng->txt("delete"), "resetPeerReview");

        $tpl->setContent($cgui->getHTML());
    }

    public function resetPeerReviewObject(): void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->ass ||
            !$this->ass->getPeerReview()) {
            $this->returnToParentObject();
        }

        $peer_review = new ilExPeerReview($this->ass);
        $all_giver_ids = $peer_review->resetPeerReviews();

        if (is_array($all_giver_ids)) {
            // if peer review is valid for completion, we have to re-calculate all assignment members
            $exercise = new ilObjExercise($this->ass->getExerciseId(), false);
            if ($exercise->isCompletionBySubmissionEnabled() &&
                $this->ass->getPeerReviewValid() != ilExAssignment::PEER_REVIEW_VALID_NONE) {
                foreach ($all_giver_ids as $user_id) {
                    $submission = new ilExSubmission($this->ass, $user_id);
                    $pgui = new self($this->ass, $submission);
                    $pgui->handlePeerReviewChange();
                }
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("exc_peer_review_reset_done"), true);
        $ilCtrl->redirect($this, "showPeerReviewOverview");
    }
}
