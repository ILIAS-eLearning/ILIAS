<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
include_once("./Modules/Exercise/classes/class.ilExPeerReview.php");

/**
* Assignments table
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilExAssignmentListTextTableGUI extends ilTable2GUI
{
    protected $ass; // [ilExAssignment]
    protected $show_peer_review; // [bool]
    protected $peer_review; // [ilExPeerReview]
    
    public function __construct($a_parent_obj, $a_parent_cmd, ilExAssignment $a_ass, $a_show_peer_review = false, $a_disable_peer_review = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->ass = $a_ass;
        $this->show_peer_review = (bool) $a_show_peer_review;
        $this->setId("excassltxt" . $this->ass->getId());
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
    
        $this->setTitle($lng->txt("exc_list_text_assignment") .
            ": \"" . $this->ass->getTitle() . "\"");
        
        // if you add pagination and disable the unlimited setting:
        // fix saving of ordering of single pages!
        $this->setLimit(9999);
        
        $this->addColumn($this->lng->txt("user"), "uname", "15%");
        $this->addColumn($this->lng->txt("exc_last_submission"), "udate", "10%");
        
        if ($this->show_peer_review) {
            $this->addColumn($this->lng->txt("exc_files_returned_text"), "", "45%");
            $this->addColumn($this->lng->txt("exc_peer_review"), "", "30%");
            
            include_once './Services/Rating/classes/class.ilRatingGUI.php';
            include_once './Services/Accordion/classes/class.ilAccordionGUI.php';
                        
            $this->peer_review = new ilExPeerReview($this->ass);
        } else {
            $this->addColumn($this->lng->txt("exc_files_returned_text"), "", "75%");
        }
        
        $this->setDefaultOrderField("uname");
        $this->setDefaultOrderDirection("asc");
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_list_text_assignment_row.html", "Modules/Exercise");
                    
        if (!$a_disable_peer_review &&
            $this->ass->getPeerReview() &&
            !$a_show_peer_review) {
            $this->addCommandButton("listTextAssignment", $lng->txt("exc_show_peer_review"));
        }

        $this->parse();
    }
    
    public function numericOrdering($a_field)
    {
        return ($a_field == "udate");
    }

    protected function parse()
    {
        $peer_data = array();
        if ($this->show_peer_review) {
            $peer_data = $this->peer_review->getAllPeerReviews();
        }
        
        include_once "Services/User/classes/class.ilUserUtil.php";
        include_once "Services/RTE/classes/class.ilRTE.php";
        foreach (ilExSubmission::getAllAssignmentFiles($this->ass->getExerciseId(), $this->ass->getId()) as $file) {
            if (trim($file["atext"])) {
                $data[$file["user_id"]] = array(
                    "uid" => $file["user_id"],
                    "uname" => ilUserUtil::getNamePresentation($file["user_id"]),
                    "udate" => $file["ts"],
                    "utext" => ilRTE::_replaceMediaObjectImageSrc($file["atext"], 1) // mob id to mob src
                );
                                                
                if (isset($peer_data[$file["user_id"]])) {
                    $data[$file["user_id"]]["peer"] = array_keys($peer_data[$file["user_id"]]);
                }
            }
        }
        
        $this->setData($data);
    }

    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->show_peer_review) {
            $peer_data = "&nbsp;";
            if (isset($a_set["peer"])) {
                $acc = new ilAccordionGUI();
                $acc->setId($this->ass->getId() . "_" . $a_set["uid"]);

                foreach ($a_set["peer"] as $peer_id) {
                    $peer_name = ilUserUtil::getNamePresentation($peer_id);
                    $acc_item = $peer_name;
                    
                    $submission = new ilExSubmission($this->ass, $a_set["uid"]);
                    $values = $submission->getPeerReview()->getPeerReviewValues($peer_id, $a_set["uid"]);
                    
                    $acc_html = array();
                    foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $crit) {
                        $crit_id = $crit->getId()
                            ? $crit->getId()
                            : $crit->getType();
                        $crit->setPeerReviewContext($this->ass, $peer_id, $a_set["uid"]);
                        
                        // see ilWikiAdvMetaDataBlockGUI
                        $acc_html[] = '<p>' .
                            '<div class="ilBlockPropertyCaption">' . $crit->getTitle() . '</div>' .
                            '<div>' . $crit->getHTML($values[$crit_id]) . '</div>' .
                            '</p>';
                    }
                    
                    $acc->addItem(
                        ilUserUtil::getNamePresentation($peer_id, false, false, "", true),
                        '<div style="margin-left:10px;">' . implode("\n", $acc_html) . '</div>'
                    );
                }
                    
                $peer_data = $acc->getHTML();
            }
            $this->tpl->setCurrentBlock("peer_bl");
            $this->tpl->setVariable("PEER_REVIEW", $peer_data);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("USER_NAME", $a_set["uname"]);
        $this->tpl->setVariable(
            "USER_DATE",
            ilDatePresentation::formatDate(new ilDate($a_set["udate"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("USER_TEXT", nl2br($a_set["utext"]));
    }
}
