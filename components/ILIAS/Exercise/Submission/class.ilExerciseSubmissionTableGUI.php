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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

abstract class ilExerciseSubmissionTableGUI extends ilTable2GUI
{
    public const MODE_BY_ASSIGNMENT = 1;
    public const MODE_BY_USER = 2;
    protected \ILIAS\Exercise\InternalDomainService $domain;
    protected \ILIAS\Exercise\Assignment\DomainService $ass_domain;
    protected ilExerciseSubmissionFeedbackGUI $feedback_gui;

    protected ilAccessHandler $access;
    protected ilObjExercise $exc;
    protected int $mode;
    protected array $filter;
    protected array $comment_modals = array();
    protected ?ilExAssignment $ass = null;

    protected array $cols_mandatory = array("name", "status");
    protected array $cols_default = array("login", "submission", "idl", "calc_deadline");
    protected array $cols_order = array("image", "name", "login", "team_members",
            "sent_time", "submission", "calc_deadline", "idl", "status", "mark", "status_time",
            "feedback_time", "comment", "notice");

    protected ilExAssignmentTypes $ass_types;
    protected ?ilExAssignmentTypeInterface $ass_type = null;

    protected Factory $ui_factory;
    protected Renderer $ui_renderer;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjExercise $a_exc,
        int $a_item_id,
        ilExerciseSubmissionFeedbackGUI $feedback_gui
    ) {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();

        $ilCtrl = $DIC->ctrl();

        $this->exc = $a_exc;
        $this->feedback_gui = $feedback_gui;

        $this->initMode($a_item_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setShowTemplates(true);

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_members_row.html", "components/ILIAS/Exercise");

        #25100
        if ($this->mode == self::MODE_BY_ASSIGNMENT) {
            $this->setDefaultOrderField("name");
            $this->setDefaultOrderDirection("asc");
        }


        // columns

        $this->addColumn("", "", "1", true);

        $selected = $this->getSelectedColumns();
        $columns = $this->parseColumns();
        foreach ($this->cols_order as $id) {
            if (in_array($id, $this->cols_mandatory) ||
                in_array($id, $selected)) {
                if (array_key_exists($id, $columns)) {
                    $col = $columns[$id];
                    $this->addColumn($col[0], $col[1] ?? "");
                }
            }
        }

        $this->addColumn($this->lng->txt("actions"));


        // multi actions

        $this->addMultiCommand("saveStatusSelected", $this->lng->txt("exc_save_selected"));

        // TODO get rid of the constant from ilExAssignment. Get this value from ilExAssignmentTypes
        if ($this->mode == self::MODE_BY_ASSIGNMENT && $this->ass->getType() == ilExAssignment::TYPE_TEXT) {
            $this->addMultiCommand("compareTextAssignments", $this->lng->txt("exc_compare_submissions"));
        }

        $this->setFormName("ilExcIDlForm");

        // see 0021530 and parseRow here with similar action per user
        if ($this->mode == self::MODE_BY_ASSIGNMENT &&
            $this->ass->hasActiveIDl() &&
            !$this->ass->hasReadOnlyIDl()) {
            $this->addMultiCommand("setIndividualDeadline", $this->lng->txt("exc_individual_deadline_action"));
        }

        if ($this->exc->hasTutorFeedbackMail() &&
            $this->mode == self::MODE_BY_ASSIGNMENT) {
            $this->addMultiCommand("redirectFeedbackMail", $this->lng->txt("exc_tbl_action_feedback_mail"));
        }

        $this->addMultiCommand("sendMembers", $this->lng->txt("exc_send_assignment"));

        if ($this->mode == self::MODE_BY_ASSIGNMENT) {
            $this->addMultiCommand("sendGradingNotification", $this->lng->txt("exc_send_grading_notification"));
        }

        if ($this->mode == self::MODE_BY_ASSIGNMENT &&
            $this->ass &&
            $this->ass->hasTeam()) {
            $this->addMultiCommand("createTeams", $this->lng->txt("exc_team_multi_create"));
            $this->addMultiCommand("dissolveTeams", $this->lng->txt("exc_team_multi_dissolve"));
        }

        if ($this->mode == self::MODE_BY_ASSIGNMENT) {
            $this->addMultiCommand("confirmDeassignMembers", $this->lng->txt("exc_deassign_members"));
        }

        $this->setFilterCommand($this->getParentCmd() . "Apply");
        $this->setResetCommand($this->getParentCmd() . "Reset");

        $this->initFilter();
        $this->setData($this->parseData());
        $this->ass_domain = $DIC->exercise()->internal()->domain()->assignment();
        $this->domain = $DIC->exercise()->internal()->domain();
    }

    public function initFilter(): void
    {
        if ($this->mode == self::MODE_BY_ASSIGNMENT) {
            $item = $this->addFilterItemByMetaType("flt_name", self::FILTER_TEXT, false, $this->lng->txt("name") . " / " . $this->lng->txt("login"));
            $this->filter["name"] = $item->getValue();
        }

        $this->lng->loadLanguageModule("search");
        $options = array(
            "" => $this->lng->txt("search_any"),
            "notgraded" => $this->lng->txt("exc_notgraded"),
            "passed" => $this->lng->txt("exc_passed"),
            "failed" => $this->lng->txt("exc_failed")
        );
        $item = $this->addFilterItemByMetaType("flt_status", self::FILTER_SELECT, false, $this->lng->txt("exc_tbl_status"));
        $item->setOptions($options);
        $this->filter["status"] = $item->getValue();

        $options = array(
            "" => $this->lng->txt("search_any"),
            "y" => $this->lng->txt("exc_tbl_filter_has_submission"),
            "n" => $this->lng->txt("exc_tbl_filter_has_no_submission")
        );
        $item = $this->addFilterItemByMetaType("flt_subm", self::FILTER_SELECT, false, $this->lng->txt("exc_tbl_filter_submission"));
        $item->setOptions($options);
        $this->filter["subm"] = $item->getValue();

        $item = $this->addFilterItemByMetaType("flt_subm_after", self::FILTER_DATE, false, $this->lng->txt("exc_tbl_filter_submission_after"));
        $this->filter["subm_after"] = $item->getDate();

        $item = $this->addFilterItemByMetaType("flt_subm_before", self::FILTER_DATE, false, $this->lng->txt("exc_tbl_filter_submission_before"));
        $this->filter["subm_before"] = $item->getDate();

        if ($this->mode == self::MODE_BY_ASSIGNMENT && !$this->ass->hasTeam()) {
            $this->initCourseGroupFilter();
        }
    }

    protected function initCourseGroupFilter(): void
    {
        $repo = new ilRepositorySelector2InputGUI(
            $this->lng->txt('exc_member_of_crs_grp'),
            'effective_from',
            false,
            ($this->isForwardingToFormDispatcher()) ? $this : null
        );
        $repo->getExplorerGUI()->setSelectableTypes(["crs", "grp"]);
        $repo->getExplorerGUI()->setTypeWhiteList(["root", "cat", "crs", "grp"]);
        $this->addFilterItem($repo);
        $repo->readFromSession();
        $this->filter['member_of'] = (int) $repo->getValue();
    }

    protected function isForwardingToFormDispatcher(): bool
    {
        return false;
    }



    abstract protected function initMode(int $a_item_id): void;

    abstract protected function parseData(): array;

    abstract protected function parseModeColumns(): array;

    public function getSelectableColumns(): array
    {
        $cols = array();

        $columns = $this->parseColumns();
        foreach ($this->cols_order as $id) {
            if (in_array($id, $this->cols_mandatory)) {
                continue;
            }

            if (array_key_exists($id, $columns)) {
                $col = $columns[$id];

                $cols[$id] = array(
                    "txt" => $col[0],
                    "default" => in_array($id, $this->cols_default)
                );
            }
        }
        return $cols;
    }

    protected function parseColumns(): array
    {
        $cols = $this->parseModeColumns();

        $cols["submission"] = array($this->lng->txt("exc_tbl_submission_date"), "submission");

        $cols["status"] = array($this->lng->txt("exc_tbl_status"), "status");
        $cols["mark"] = array($this->lng->txt("exc_tbl_mark"), "mark");
        $cols["status_time"] = array($this->lng->txt("exc_tbl_status_time"), "status_time");

        $cols["sent_time"] = array($this->lng->txt("exc_tbl_sent_time"), "sent_time");

        if ($this->exc->hasTutorFeedbackText() ||
            $this->exc->hasTutorFeedbackMail() ||
            $this->exc->hasTutorFeedbackFile()) {
            $cols["feedback_time"] = array($this->lng->txt("exc_tbl_feedback_time"), "feedback_time");
        }

        if ($this->exc->hasTutorFeedbackText()) {
            $cols["comment"] = array($this->lng->txt("exc_tbl_comment"), "comment");
        }

        $cols["notice"] = array($this->lng->txt("exc_tbl_notice"), "notice");

        return $cols;
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    protected function parseRow(
        int $a_user_id,
        ilExAssignment $a_ass,
        array $a_row
    ): void {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $has_no_team_yet = ($a_ass->hasTeam() &&
            !ilExAssignmentTeam::getTeamId($a_ass->getId(), $a_user_id));


        // static columns

        if ($this->mode == self::MODE_BY_ASSIGNMENT) {
            if (!$a_ass->hasTeam()) {
                $this->tpl->setVariable("VAL_NAME", $a_row["name"]);

                // #18327
                if (!$ilAccess->checkAccessOfUser($a_user_id, "read", "", $this->exc->getRefId()) &&
                    is_array($info = $ilAccess->getInfo())) {
                    $this->tpl->setCurrentBlock('access_warning');
                    $this->tpl->setVariable('PARENT_ACCESS', $info[0]["text"]);
                    $this->tpl->parseCurrentBlock();
                }
            } else {
                asort($a_row["team"]);
                foreach ($a_row["team"] as $team_member_id => $team_member_name) { // #10749
                    if (count($a_row["team"]) > 1) {
                        $ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", $team_member_id);
                        $url = $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "confirmRemoveTeamMember");
                        $ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", "");

                        $this->tpl->setCurrentBlock("team_member_removal_bl");
                        $this->tpl->setVariable("URL_TEAM_MEMBER_REMOVAL", $url);

                        $rem_gl = $this->ui_factory->symbol()->glyph()->remove($url);
                        $this->tpl->setVariable(
                            "TXT_TEAM_MEMBER_REMOVAL",
                            $this->ui_renderer->render($rem_gl)
                        );

                        $this->tpl->parseCurrentBlock();
                    }

                    // #18327
                    if (!$ilAccess->checkAccessOfUser($team_member_id, "read", "", $this->exc->getRefId()) &&
                        is_array($info = $ilAccess->getInfo())) {
                        $this->tpl->setCurrentBlock('team_access_warning');
                        $this->tpl->setVariable('TEAM_PARENT_ACCESS', $info[0]["text"]);
                        $this->tpl->parseCurrentBlock();
                    }

                    $this->tpl->setCurrentBlock("team_member");
                    $this->tpl->setVariable("TXT_MEMBER_NAME", $team_member_name);
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("team_info");
                if ($has_no_team_yet) {
                    // #11957
                    $this->tpl->setVariable("TXT_TEAM_INFO", $this->lng->txt("exc_no_team_yet"));
                } else {
                    $this->tpl->setVariable("TXT_TEAM_INFO", "(" . $a_row["submission_obj"]->getTeam()->getId() . ")");
                }
            }
        } else {
            $this->tpl->setVariable("VAL_NAME", $a_row["name"]);
        }

        // do not grade or mark if no team yet
        if (!$has_no_team_yet) {
            // status
            $this->tpl->setVariable("SEL_" . strtoupper($a_row["status"] ?? ""), ' selected="selected" ');
            $this->tpl->setVariable("TXT_NOTGRADED", $this->lng->txt("exc_notgraded"));
            $this->tpl->setVariable("TXT_PASSED", $this->lng->txt("exc_passed"));
            $this->tpl->setVariable("TXT_FAILED", $this->lng->txt("exc_failed"));
        } else {
            $nt_colspan = in_array("mark", $this->getSelectedColumns())
                ? 2
                : 1;

            $this->tpl->setVariable("NO_TEAM_COLSPAN", $nt_colspan);
        }

        // selectable columns

        foreach ($this->getSelectedColumns() as $col) {
            $include_seconds = false;
            switch ($col) {
                case "image":
                    if (!$a_ass->hasTeam()) {
                        /** @var $usr_obj ilObjUser */
                        if ($usr_obj = ilObjectFactory::getInstanceByObjId($a_user_id, false)) {
                            $this->tpl->setVariable("VAL_IMAGE", $usr_obj->getPersonalPicturePath("xxsmall"));
                            $this->tpl->setVariable("TXT_IMAGE", $this->lng->txt("personal_picture"));
                        }
                    }
                    break;

                case "team_members":
                    if ($a_ass->hasTeam()) {
                        if (count($a_row["team"]) === 0) {
                            $this->tpl->setVariable("VAL_TEAM_MEMBER", $this->lng->txt("exc_no_team_yet"));
                        } else {
                            foreach ($a_row["team"] as $name) {
                                $this->tpl->setCurrentBlock("team_member_bl");
                                $this->tpl->setVariable("VAL_TEAM_MEMBER", $name);
                                $this->tpl->parseCurrentBlock();
                            }
                        }
                    } else {
                        $this->tpl->setVariable("VAL_TEAM_MEMBER", "&nbsp;");
                    }
                    break;

                case "calc_deadline":
                case "idl":

                    if ($a_ass->getDeadlineMode() === ilExAssignment::DEADLINE_ABSOLUTE_INDIVIDUAL && ($a_row[$col] ?? 0) == 0) {
                        if ($a_row["requested_idl"] ?? false) {
                            $this->tpl->setVariable(
                                "VAL_" . strtoupper($col),
                                $this->lng->txt("exc_deadline_requested")
                            );
                        } else {
                            $this->tpl->setVariable(
                                "VAL_" . strtoupper($col),
                                "&nbsp;"
                            );
                        }
                    } else {
                        $this->tpl->setVariable(
                            "VAL_" . strtoupper($col),
                            isset($a_row[$col])
                                ? ilDatePresentation::formatDate(new ilDateTime($a_row[$col], IL_CAL_UNIX))
                                : "&nbsp;"
                        );
                    }
                    break;

                case "mark":
                    if (!$has_no_team_yet) {
                        $this->tpl->setVariable(
                            "VAL_" . strtoupper($col),
                            ilLegacyFormElementsUtil::prepareFormOutput(
                                trim((string) ($a_row[$col] ?? ""))
                            )
                        );
                    }
                    break;

                case "notice":
                    // see #22076
                    $this->tpl->setVariable(
                        "VAL_" . strtoupper($col),
                        ilLegacyFormElementsUtil::prepareFormOutput(trim((string) $a_row[$col]))
                    );
                    break;

                case "comment":
                    // for js-updating
                    $comment_id = "excasscomm_" . $a_ass->getId() . "_" . $a_user_id;
                    $this->tpl->setVariable("LCOMMENT_ID", $comment_id . "_snip");

                    // see #22076
                    $this->tpl->setVariable("VAL_" . strtoupper($col), (trim((string) $a_row[$col]) !== "")
                        ? nl2br(trim((string) $a_row[$col]))
                        : "&nbsp;");
                    break;

                case "feedback_time":
                case "status_time":
                case "sent_time":
                case "submission":
                    if ($col == "submission" && $a_row["submission_obj"]) {
                        $include_seconds = true;
                        $sm = $this->domain->submission($a_row["submission_obj"]->getAssignment()->getId());
                        foreach ($sm->getSubmissionsOfUser($a_row["submission_obj"]->getUserId()) as $sub) {
                            if ($sub->getLate()) {
                                $this->tpl->setVariable("TXT_LATE", $this->lng->txt("exc_late_submission"));
                                break;
                            }
                        }
                    }
                    $this->tpl->setVariable(
                        "VAL_" . strtoupper($col),
                        ($a_row[$col] ?? false)
                            ? ilDatePresentation::formatDate(
                                new ilDateTime($a_row[$col], IL_CAL_DATETIME),
                                false,
                                false,
                                $include_seconds
                            )
                            : "&nbsp;"
                    );
                    break;

                case "login":
                    if (!$a_ass->hasTeam()) {
                        $this->tpl->setVariable("VAL_" . strtoupper($col), $a_row[$col]
                            ? trim((string) $a_row[$col])
                            : "&nbsp;");
                    }
                    break;

                default:
                    $this->tpl->setVariable("VAL_" . strtoupper($col), $a_row[$col]
                        ? trim((string) $a_row[$col])
                        : "&nbsp;");
                    break;
            }
        }


        // actions

        $items = array();

        $file_info = $a_row["submission_obj"]->getDownloadedFilesInfoForTableGUIS();

        $counter = $file_info["files"]["count"];
        if ($counter) {
            if (isset($file_info["files"]["download_url"])) {
                $items[] = $this->ui_factory->button()->shy(
                    $file_info["files"]["download_txt"] . " (" . $counter . ")",
                    $file_info["files"]["download_url"]
                );
            }

            if (isset($file_info["files"]["download_new_url"])) {
                $items[] = $this->ui_factory->button()->shy(
                    $file_info["files"]["download_new_txt"],
                    $file_info["files"]["download_new_url"]
                );
            }
        }

        $ass_type = $this->ass_type ?: ilExAssignmentTypes::getInstance()->getById($a_ass->getType());

        if ($ass_type->supportsWebDirAccess() && $a_row['submission_obj']->hasSubmittedPrintVersion()) {
            $url = $ilCtrl->getLinkTarget($this->getParentObject(), "openSubmissionView");
            $items[] = $this->ui_factory->link()->standard($this->lng->txt("exc_tbl_action_open_submission"), $url)->withOpenInNewViewport(true);
            if ($a_row['submission_obj']->hasSubmittedPrintVersion()) {
                $url = $ilCtrl->getLinkTarget($this->getParentObject(), "openSubmissionPrintView");
                $items[] = $this->ui_factory->link()->standard($this->lng->txt("exc_print_pdf"), $url)->withOpenInNewViewport(true);
            }
        }
        $ind_deadline_mode = ($a_ass->getDeadlineMode() === ilExAssignment::DEADLINE_ABSOLUTE_INDIVIDUAL);
        if (!$has_no_team_yet &&
            $a_ass->hasActiveIDl() &&
            !$a_ass->hasReadOnlyIDl() &&
            (!is_null($a_row["calc_deadline"] ?? null) || $a_ass->getDeadline() || $ind_deadline_mode)) {    // calculated or common deadline given
            $idl_id = $a_ass->hasTeam()
                ? "t" . ilExAssignmentTeam::getTeamId($a_ass->getId(), $a_user_id)
                : $a_user_id;

            $this->tpl->setVariable("VAL_IDL_ID", $a_ass->getId() . "_" . $idl_id);

            $assignment_id = $a_ass->getId();
            $items[] = $this->ui_factory->button()->shy($this->lng->txt("exc_individual_deadline_action"), "#")
                ->withOnLoadCode(function ($id) use ($idl_id, $assignment_id) {
                    return "$('#$id').on('click', function() {il.ExcIDl.trigger('$idl_id', '$assignment_id'); return false;})";
                });
        }

        // feedback mail
        if ($this->exc->hasTutorFeedbackMail()) {
            $items[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_tbl_action_feedback_mail"),
                $ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail")
            );
        }

        // feedback files
        if ($a_ass->canParticipantReceiveFeedback($a_user_id)) {
            if ($this->exc->hasTutorFeedbackFile()) {
                $tutor_feedback_manager = $this->ass_domain->tutorFeedbackFile($a_ass->getId());
                if ($tutor_feedback_manager->hasCollection($a_user_id)) {
                    // IRSS
                    $counter = $tutor_feedback_manager->count($a_user_id);
                    $counter = $counter
                        ? " (" . $counter . ")"
                        : "";
                    $items[] = $this->ui_factory->button()->shy(
                        $this->lng->txt("exc_tbl_action_feedback_file") . $counter,
                        $ilCtrl->getLinkTargetByClass(ilResourceCollectionGUI::class, "")
                    );
                }
            }

            // comment (modal)
            if ($this->exc->hasTutorFeedbackText()) {
                $components = $this->feedback_gui->getComponents($a_ass->getId(), $a_user_id);
                foreach ($components as $type => $c) {
                    if ($type === "modal") {
                        $this->comment_modals[] = $this->ui_renderer->render($c);
                    } elseif ($type === "button") {
                        $items[] = $c;
                    }
                }
            }
        }

        // peer review
        if (($peer_review = $a_row["submission_obj"]->getPeerReview()) && $a_ass->afterDeadlineStrict()) {	// see #22246
            $counter = $peer_review->countGivenFeedback(true, $a_user_id);
            $counter = $counter
                ? " (" . $counter . ")"
                : "";
            $items[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_tbl_action_peer_review_given") . $counter,
                $ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showGivenPeerReview")
            );

            $counter = count($peer_review->getPeerReviewsByPeerId($a_user_id, true));
            $counter = $counter !== 0
                ? " (" . $counter . ")"
                : "";

            $items[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_tbl_action_peer_review_received") . $counter,
                $ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showReceivedPeerReview")
            );
        }

        // team
        if ($has_no_team_yet) {
            $items[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_create_team"),
                $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "createSingleMemberTeam")
            );
        } elseif ($a_ass->hasTeam()) {
            $items[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_tbl_action_team_log"),
                $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "showTeamLog")
            );
        }

        $actions = $this->ui_factory->dropdown()->standard($items);

        $this->tpl->setVariable("ACTIONS", $this->ui_renderer->render($actions));
    }

    public function render(): string
    {
        return parent::render() .
            implode("\n", $this->comment_modals);
    }
}
