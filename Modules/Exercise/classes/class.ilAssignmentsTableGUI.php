<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

/**
* Assignments table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilAssignmentsTableGUI extends ilTable2GUI
{
    /**
     * @var ilExAssignmentTypes
     */
    protected $types;

    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_exc_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        include_once("./Modules/Exercise/AssignmentTypes/classes/class.ilExAssignmentTypes.php");
        $this->types = ilExAssignmentTypes::getInstance();

        $this->exc_id = $a_exc_id;
        $this->setId("excass" . $this->exc_id);
        
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
        
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        include_once("./Modules/Exercise/classes/class.ilExPeerReview.php");
        $data = ilExAssignment::getAssignmentDataOfExercise($this->exc_id);
        foreach ($data as $idx => $row) {
            // #14450
            if ($row["peer"]) {
                $data[$idx]["peer_invalid"] = true;
                $peer_review = new ilExPeerReview(new ilExAssignment($row["id"]));
                $peer_reviews = $peer_review->validatePeerReviewGroups();
                $data[$idx]["peer_invalid"] = $peer_reviews["invalid"];
            }
            $data[$idx]["ass_type"] = $this->types->getById($row["type"]);
            $data[$idx]["type"] = $data[$idx]["ass_type"]->getTitle();
        }
        
        $this->setData($data);
    }
    
    public function numericOrdering($a_field)
    {
        // #12000
        if (in_array($a_field, array("order_val", "deadline", "start_time"))) {
            return true;
        }
        return false;
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($d)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("ID", $d["id"]);

        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        $ass = new ilExAssignment($d["id"]);

        if ($d["deadline"] > 0) {
            $dl = ilDatePresentation::formatDate(new ilDateTime($d["deadline"], IL_CAL_UNIX));
            if ($d["deadline2"] > 0) {
                $dl .= "<br />(" . ilDatePresentation::formatDate(new ilDateTime($d["deadline2"], IL_CAL_UNIX)) . ")";
            }
            $this->tpl->setVariable("TXT_DEADLINE", $dl);
        }
        if ($d["start_time"] > 0) {
            $this->tpl->setVariable(
                "TXT_START_TIME",
                ilDatePresentation::formatDate(new ilDateTime($d["start_time"], IL_CAL_UNIX))
            );
        }
        $this->tpl->setVariable(
            "TXT_INSTRUCTIONS",
            nl2br(trim(ilUtil::shortenText(strip_tags($d["instruction"]), 200, true)))
        );
        
        if ($d["mandatory"]) {
            $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("yes"));
        } else {
            $this->tpl->setVariable("TXT_MANDATORY", $lng->txt("no"));
        }
        
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $d["id"]);
        
        if ($d["peer"]) {
            $this->tpl->setVariable("TXT_PEER", $lng->txt("yes") . " (" . $d["peer_min"] . ")");
            
            if ($d["peer_invalid"]) {
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
        
        $this->tpl->setVariable("TXT_TITLE", $d["title"]);
        $this->tpl->setVariable("TXT_TYPE", $d["type"]);
        $this->tpl->setVariable("ORDER_VAL", $d["order_val"]);
        
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTarget($this->parent_obj, "editAssignment")
        );
    }
}
