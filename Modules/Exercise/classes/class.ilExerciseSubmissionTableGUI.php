<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
include_once("./Modules/Exercise/classes/class.ilExAssignmentTeam.php");
include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");

/**
 * Exercise submission table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
abstract class ilExerciseSubmissionTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $exc; // [ilObjExercise]
    protected $mode; // [int]
    protected $filter; // [array]
    protected $comment_modals = array(); // [array]
    
    const MODE_BY_ASSIGNMENT = 1;
    const MODE_BY_USER = 2;
    
    // needs PH P5.6 for array support
    protected $cols_mandatory = array("name", "status");
    protected $cols_default = array("login", "submission_date", "idl", "calc_deadline");
    protected $cols_order = array("image", "name", "login", "team_members",
            "sent_time", "submission", "calc_deadline", "idl", "status", "mark", "status_time",
            "feedback_time", "comment", "notice");
    
    /**
     * Constructor
     *
     * @param string $a_parent_obj
     * @param string $a_parent_cmd
     * @param ilObjExercise $a_exc
     * @param int $a_item_id
     * @return self
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilObjExercise $a_exc, $a_item_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();


        $ilCtrl = $DIC->ctrl();
        
        $this->exc = $a_exc;
        
        $this->initMode($a_item_id);
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setShowTemplates(true);
                
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_members_row.html", "Modules/Exercise");

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
                    $this->addColumn($col[0], $col[1]);
                }
            }
        }
        
        $this->addColumn($this->lng->txt("actions"));
        
        
        // multi actions
        
        $this->addMultiCommand("saveStatusSelected", $this->lng->txt("exc_save_selected"));
            
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
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
    }
                    
    public function initFilter()
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
    }
    
    abstract protected function initMode($a_item_id);
    
    abstract protected function parseData();
    
    abstract protected function parseModeColumns();
        
    public function getSelectableColumns()
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
            
    protected function parseColumns()
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
        
        $cols["notice"] = array($this->lng->txt("exc_tbl_notice"), "note");
        
        return $cols;
    }
    
    protected function parseRow($a_user_id, ilExAssignment $a_ass, array $a_row)
    {
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
                    if (sizeof($a_row["team"]) > 1) {
                        $ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", $team_member_id);
                        $url = $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "confirmRemoveTeamMember");
                        $ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", "");

                        include_once "Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php";

                        $this->tpl->setCurrentBlock("team_member_removal_bl");
                        $this->tpl->setVariable("URL_TEAM_MEMBER_REMOVAL", $url);
                        $this->tpl->setVariable(
                            "TXT_TEAM_MEMBER_REMOVAL",
                            ilGlyphGUI::get(ilGlyphGUI::CLOSE, $this->lng->txt("remove"))
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

                if ($has_no_team_yet) {
                    // #11957
                    $this->tpl->setCurrentBlock("team_info");
                    $this->tpl->setVariable("TXT_TEAM_INFO", $this->lng->txt("exc_no_team_yet"));
                } else {
                    $this->tpl->setCurrentBlock("team_info");
                    $this->tpl->setVariable("TXT_TEAM_INFO", "(" . $a_row["submission_obj"]->getTeam()->getId() . ")");
                }
            }
        } else {
            $this->tpl->setVariable("VAL_NAME", $a_row["name"]);
        }
        
        // do not grade or mark if no team yet
        if (!$has_no_team_yet) {
            // status
            $this->tpl->setVariable("SEL_" . strtoupper($a_row["status"]), ' selected="selected" ');
            $this->tpl->setVariable("TXT_NOTGRADED", $this->lng->txt("exc_notgraded"));
            $this->tpl->setVariable("TXT_PASSED", $this->lng->txt("exc_passed"));
            $this->tpl->setVariable("TXT_FAILED", $this->lng->txt("exc_failed"));
        } else {
            $nt_colspan = in_array("mark", $this->getSelectedColumns())
                ? 2
                : 1;
                        
            $this->tpl->setVariable("NO_TEAM_COLSPAN", $nt_colspan);
        }
                
        
        // comment modal
        
        if ($this->exc->hasTutorFeedbackText()) {
            $comment_id = "excasscomm_" . $a_ass->getId() . "_" . $a_user_id;

            $modal = ilModalGUI::getInstance();
            $modal->setId($comment_id);
            $modal->setHeading($this->lng->txt("exc_tbl_action_feedback_text"));

            $lcomment_form = new ilPropertyFormGUI();
            $lcomment_form->setId($comment_id);
            $lcomment_form->setPreventDoubleSubmission(false);

            $lcomment = new ilTextAreaInputGUI($this->lng->txt("exc_comment_for_learner"), "lcomment_" . $a_ass->getId() . "_" . $a_user_id);
            $lcomment->setInfo($this->lng->txt("exc_comment_for_learner_info"));
            $lcomment->setValue($a_row["comment"]);
            $lcomment->setCols(45);
            $lcomment->setRows(10);
            $lcomment_form->addItem($lcomment);

            $lcomment_form->addCommandButton("save", $this->lng->txt("save"));
            // $lcomment_form->addCommandButton("cancel", $lng->txt("cancel"));

            $modal->setBody($lcomment_form->getHTML());

            $this->comment_modals[] = $modal->getHTML();
            unset($modal);
        }
                        
        
        // selectable columns
            
        foreach ($this->getSelectedColumns() as $col) {
            switch ($col) {
                case "image":
                    if (!$a_ass->hasTeam()) {
                        include_once "./Services/Object/classes/class.ilObjectFactory.php";
                        if ($usr_obj = ilObjectFactory::getInstanceByObjId($a_user_id, false)) {
                            $this->tpl->setVariable("VAL_IMAGE", $usr_obj->getPersonalPicturePath("xxsmall"));
                            $this->tpl->setVariable("TXT_IMAGE", $this->lng->txt("personal_picture"));
                        }
                    }
                    break;
                    
                case "team_members":
                    if ($a_ass->hasTeam()) {
                        if (!sizeof($a_row["team"])) {
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
                    
                case "idl":
                    $this->tpl->setVariable(
                        "VAL_" . strtoupper($col),
                        $a_row[$col]
                            ? ilDatePresentation::formatDate(new ilDateTime($a_row[$col], IL_CAL_UNIX))
                            : "&nbsp;"
                    );
                    break;

                case "calc_deadline":
                    $this->tpl->setVariable(
                        "VAL_" . strtoupper($col),
                        $a_row[$col]
                            ? ilDatePresentation::formatDate(new ilDateTime($a_row[$col], IL_CAL_UNIX))
                            : "&nbsp;"
                    );
                    break;

                case "mark":
                    if ($has_no_team_yet) {
                        break;
                    }
                    // fallthrough
                    
                    // no break
                case "notice":
                    // see #22076
                    $this->tpl->setVariable("VAL_" . strtoupper($col), ilUtil::prepareFormOutput(trim($a_row[$col])));
                    break;
                    
                case "comment":
                    // for js-updating
                    $this->tpl->setVariable("LCOMMENT_ID", $comment_id . "_snip");

                    // see #22076
                    $this->tpl->setVariable("VAL_" . strtoupper($col), (trim($a_row[$col]) !== "")
                        ? nl2br(trim($a_row[$col]))
                        : "&nbsp;");
                    break;
                                
                case "submission":
                    if ($a_row["submission_obj"]) {
                        foreach ($a_row["submission_obj"]->getFiles() as $file) {
                            if ($file["late"]) {
                                $this->tpl->setVariable("TXT_LATE", $this->lng->txt("exc_late_submission"));
                                break;
                            }
                        }
                    }
                    // fallthrough
                    
                    // no break
                case "feedback_time":
                case "status_time":
                case "sent_time":
                    $this->tpl->setVariable(
                        "VAL_" . strtoupper($col),
                        $a_row[$col]
                            ? ilDatePresentation::formatDate(new ilDateTime($a_row[$col], IL_CAL_DATETIME))
                            : "&nbsp;"
                    );
                    break;
                    
                case "login":
                    if ($a_ass->hasTeam()) {
                        break;
                    }
                    // fallthrough
                
                    // no break
                default:
                    $this->tpl->setVariable("VAL_" . strtoupper($col), $a_row[$col]
                        ? trim($a_row[$col])
                        : "&nbsp;");
                    break;
            }
        }
        
        
        // actions
        
        include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setId($a_ass->getId() . "_" . $a_user_id);
        $actions->setListTitle($this->lng->txt("actions"));
                
        $file_info = $a_row["submission_obj"]->getDownloadedFilesInfoForTableGUIS($this->getParentObject(), $this->getParentCmd());
        
        $counter = $file_info["files"]["count"];
        if ($counter) {
            if ($file_info["files"]["download_url"]) {
                $actions->addItem(
                    $file_info["files"]["download_txt"] . " (" . $counter . ")",
                    "",
                    $file_info["files"]["download_url"]
                );
            }
            
            if ($file_info["files"]["download_new_url"]) {
                $actions->addItem(
                    $file_info["files"]["download_new_txt"],
                    "",
                    $file_info["files"]["download_new_url"]
                );
            }
        }
        
        if (!$has_no_team_yet &&
            $a_ass->hasActiveIDl() &&
            !$a_ass->hasReadOnlyIDl()) {
            $idl_id = $a_ass->hasTeam()
                ? "t" . ilExAssignmentTeam::getTeamId($a_ass->getId(), $a_user_id)
                : $a_user_id;
            
            $this->tpl->setVariable("VAL_IDL_ID", $a_ass->getId() . "_" . $idl_id);
            
            $actions->addItem(
                $this->lng->txt("exc_individual_deadline_action"),
                "",
                "#",
                "",
                "",
                "",
                "",
                false,
                "il.ExcIDl.trigger('" . $idl_id . "'," . $a_ass->getId() . ")"
            );
        }
        
        // feedback mail
        if ($this->exc->hasTutorFeedbackMail()) {
            $actions->addItem(
                $this->lng->txt("exc_tbl_action_feedback_mail"),
                "",
                $ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail")
            );
        }
        
        // feedback files
        if ($this->exc->hasTutorFeedbackFile()) {
            include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
            $storage = new ilFSStorageExercise($this->exc->getId(), $a_ass->getId());
            $counter = $storage->countFeedbackFiles($a_row["submission_obj"]->getFeedbackId());
            $counter = $counter
                ? " (" . $counter . ")"
                : "";
            $actions->addItem(
                $this->lng->txt("exc_tbl_action_feedback_file") . $counter,
                "",
                $ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles")
            );
        }

        // comment (modal - see above)
        if ($this->exc->hasTutorFeedbackText()) {
            $actions->addItem(
                $this->lng->txt("exc_tbl_action_feedback_text"),
                "",
                "#",
                "",
                "",
                "",
                "",
                false,
                "il.ExcManagement.showComment('" . $comment_id . "')"
            );
        }
        
        // peer review
        if (($peer_review = $a_row["submission_obj"]->getPeerReview()) && $a_ass->afterDeadlineStrict()) {	// see #22246
            $counter = $peer_review->countGivenFeedback(true, $a_user_id);
            $counter = $counter
                ? " (" . $counter . ")"
                : "";
            $actions->addItem(
                $this->lng->txt("exc_tbl_action_peer_review_given") . $counter,
                "",
                $ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showGivenPeerReview")
            );
            
            $counter = sizeof($peer_review->getPeerReviewsByPeerId($a_user_id, true));
            $counter = $counter
                ? " (" . $counter . ")"
                : "";
            $actions->addItem(
                $this->lng->txt("exc_tbl_action_peer_review_received") . $counter,
                "",
                $ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showReceivedPeerReview")
            );
        }
        
        // team
        if ($has_no_team_yet) {
            $actions->addItem(
                $this->lng->txt("exc_create_team"),
                "",
                $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "createSingleMemberTeam")
            );
        } elseif ($a_ass->hasTeam()) {
            $actions->addItem(
                $this->lng->txt("exc_tbl_action_team_log"),
                "",
                $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "showTeamLog")
            );
        }
        
        $this->tpl->setVariable("ACTIONS", $actions->getHTML());
    }
        
    public function render()
    {
        global $DIC;
        $ilCtrl = $this->ctrl;
        $tpl = $DIC->ui()->mainTemplate();
        
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), "saveCommentForLearners", "", true, false);
        
        $tpl->addJavaScript("Modules/Exercise/js/ilExcManagement.js");
        $tpl->addOnLoadCode('il.ExcManagement.init("' . $url . '");');
        
        return parent::render() .
            implode("\n", $this->comment_modals);
    }
}
