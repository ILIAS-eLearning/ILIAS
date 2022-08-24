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

use ILIAS\Exercise\Assignment\Mandatory;

/**
 * Assignments table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAssignmentsTableGUI extends ilTable2GUI
{
    protected ilExAssignmentTypes $types;
    protected Mandatory\RandomAssignmentsManager $random_manager;
    protected int $exc_id;

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_exc_id
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->types = ilExAssignmentTypes::getInstance();

        $this->exc_id = $a_exc_id;
        $this->setId("excass" . $this->exc_id);

        $request = $DIC->exercise()->internal()->gui()->request();
        $this->random_manager = $DIC->exercise()->internal()->domain()->assignment()->randomAssignments(
            $request->getExercise()
        );

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("exc_assignments"));
        $this->setTopCommands(true);

        // if you add pagination and disable the unlimited setting:
        // fix saving of ordering of single pages!
        $this->setLimit(9999);

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("exc_assignment_type"), "type");
        $this->addColumn($this->lng->txt("exc_presentation_order"), "order_val");
        $this->addColumn($this->lng->txt("exc_start_time"), "start_time");
        $this->addColumn($this->lng->txt("exc_deadline"), "deadline");
        $this->addColumn($this->lng->txt("exc_mandatory"), "mandatory");
        $this->addColumn($this->lng->txt("exc_peer_review"), "peer");
        $this->addColumn($this->lng->txt("exc_instruction"), "", "30%");
        $this->addColumn($this->lng->txt("actions"));

        $this->setDefaultOrderField("val_order");
        $this->setDefaultOrderDirection("asc");

        //$this->setDefaultOrderField("name");
        //$this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_assignments_row.html", "Modules/Exercise");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->setSelectAllCheckbox("id");

        $this->addMultiCommand("confirmAssignmentsDeletion", $lng->txt("delete"));

        $this->addCommandButton("orderAssignmentsByDeadline", $lng->txt("exc_order_by_deadline"));
        $this->addCommandButton("saveAssignmentOrder", $lng->txt("exc_save_order"));
        //$this->addCommandButton("addAssignment", $lng->txt("exc_add_assignment"));

        $data = ilExAssignment::getAssignmentDataOfExercise($this->exc_id);
        foreach ($data as $idx => $row) {
            // #14450
            if ($row["peer"]) {
                $data[$idx]["peer_invalid"] = true;
                $peer_review = new ilExPeerReview(new ilExAssignment($row["id"]));
                $peer_reviews = $peer_review->validatePeerReviewGroups();
                if (is_array($peer_reviews)) {
                    $data[$idx]["peer_invalid"] = $peer_reviews["invalid"];
                }
            }
            $data[$idx]["ass_type"] = $this->types->getById($row["type"]);
            $data[$idx]["type"] = $data[$idx]["ass_type"]->getTitle();
        }
        $this->setData($data);
    }

    public function numericOrdering(string $a_field): bool
    {
        // #12000
        if (in_array($a_field, array("order_val", "deadline", "start_time"))) {
            return true;
        }
        return false;
    }

    /**
     * @throws ilDateTimeException
     */
    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("ID", $a_set["id"]);

        $ass = new ilExAssignment($a_set["id"]);

        $dl = "";
        if ($ass->getDeadlineMode() == ilExAssignment::DEADLINE_ABSOLUTE) {
            if ($a_set["deadline"] > 0) {
                $dl = ilDatePresentation::formatDate(new ilDateTime($a_set["deadline"], IL_CAL_UNIX));
                if ($a_set["deadline2"] > 0) {
                    $dl .= "<br />(" . ilDatePresentation::formatDate(new ilDateTime(
                        $a_set["deadline2"],
                        IL_CAL_UNIX
                    )) . ")";
                }
                $this->tpl->setVariable("TXT_DEADLINE", $dl);
            } else {
                $this->tpl->setVariable("TXT_DEADLINE", "-");
            }
        } else {
            if ($ass->getRelativeDeadline() > 0) {
                $dl = "" . $ass->getRelativeDeadline() . " " . $this->lng->txt("days");
            }
            if ($ass->getRelDeadlineLastSubmission() > 0) {
                if ($dl != "") {
                    $dl .= " / ";
                }
                $dl .= ilDatePresentation::formatDate(new ilDateTime($ass->getRelDeadlineLastSubmission(), IL_CAL_UNIX));
            }
            $this->tpl->setVariable("TXT_DEADLINE", $dl);
        }
        if ($a_set["start_time"] > 0) {
            $this->tpl->setVariable(
                "TXT_START_TIME",
                ilDatePresentation::formatDate(new ilDateTime($a_set["start_time"], IL_CAL_UNIX))
            );
        }
        $this->tpl->setVariable(
            "TXT_INSTRUCTIONS",
            nl2br(trim(ilStr::shortenTextExtended(strip_tags($a_set["instruction"]), 200, true)))
        );

        if (!$this->random_manager->isActivated()) {
            if ($a_set["mandatory"]) {
                $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("yes"));
            } else {
                $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("no"));
            }
        } else {
            $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("exc_random"));
        }

        $ilCtrl->setParameter($this->parent_obj, "ass_id", $a_set["id"]);

        if ($a_set["peer"]) {
            $this->tpl->setVariable("TXT_PEER", $lng->txt("yes") . " (" . $a_set["peer_min"] . ")");

            if ($a_set["peer_invalid"]) {
                $this->tpl->setVariable("TXT_PEER_INVALID", $lng->txt("exc_peer_reviews_invalid_warning"));
            }

            if ($ass->afterDeadlineStrict()) {	// see #22246
                $this->tpl->setVariable("TXT_PEER_OVERVIEW", $lng->txt("exc_peer_review_overview"));
                $this->tpl->setVariable(
                    "CMD_PEER_OVERVIEW",
                    $ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showPeerReviewOverview")
                );
            }
        } else {
            $this->tpl->setVariable("TXT_PEER", $lng->txt("no"));
        }

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_TYPE", $a_set["type"]);
        $this->tpl->setVariable("ORDER_VAL", $a_set["order_val"]);

        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTarget($this->parent_obj, "editAssignment")
        );

        $ilCtrl->setParameter($this->parent_obj, "ass_id", null);
    }
}
