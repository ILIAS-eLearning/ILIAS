<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/Exercise/classes/class.ilExAssignment.php';
include_once './Services/Rating/classes/class.ilRatingGUI.php';

/**
 * List all peers to be reviewed for user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExAssignmentPeerReviewTableGUI extends ilTable2GUI
{
    protected $ass; // [ilExAssignment]
    protected $user_id; // [int]
    protected $peer_data; // [array]
    protected $fstorage; // [ilFSStorageExercise]
    protected $invalid; // [int]
    
    /**
     * Constructor
     *
     * @param ilObject $a_parent_obj
     * @param string $a_parent_cmd
     * @param ilExAssignment $a_ass
     * @param int $a_user_id
     * @param array $a_peer_data
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilExAssignment $a_ass, $a_user_id, array $a_peer_data)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilCtrl = $DIC->ctrl();
                
        $this->ass = $a_ass;
        $this->user_id = $a_user_id;
        $this->peer_data = $a_peer_data;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setLimit(9999);
        
        $this->setTitle($a_ass->getTitle() . ": " . $this->lng->txt("exc_peer_review") .
            " - " . $this->lng->txt("exc_peer_review_give"));
                    
        if (!$this->ass->hasPeerReviewPersonalized()) {
            $this->addColumn($this->lng->txt("id"), "seq");
            #21260
            $this->setDefaultOrderField("seq");
        } else {
            $this->addColumn($this->lng->txt("exc_peer_review_recipient"), "name");
            #21260
            $this->setDefaultOrderField("name");
        }
        $this->addColumn($this->lng->txt("last_update"), "tstamp");
        $this->addColumn($this->lng->txt("valid"), "valid");
        $this->addColumn($this->lng->txt("action"), "");
                        
        $this->setRowTemplate("tpl.exc_peer_review_row.html", "Modules/Exercise");
        
        $this->disable("numinfo");
        
        $this->getItems();
        
        if ($this->ass->hasPeerReviewFileUpload()) {
            include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
            $this->fstorage = new ilFSStorageExercise($this->ass->getExerciseId(), $this->ass->getId());
            $this->fstorage->create();
        }
    }
    
    public function getInvalidItems()
    {
        return $this->invalid;
    }
    
    protected function getItems()
    {
        $data = array();
        
        $personal = $this->ass->hasPeerReviewPersonalized();
        
        if ($personal) {
            include_once "Services/User/classes/class.ilUserUtil.php";
        }
        
        $peer_review = new ilExPeerReview($this->ass);
                
        foreach ($this->peer_data as $item) {
            $row = array();
                        
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
            $submission = new ilExSubmission($this->ass, $item["peer_id"]);
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
                if (!$crit->validate($values[$crit_id])) {
                    $row["valid"] = false;
                }
                if ($crit->hasValue($values[$crit_id])) {
                    $all_empty = false;
                }
            }
            if ($all_empty) {
                $row["valid"] = false;
            }
            if (!$row["valid"]) {
                $this->invalid++;
            }
            
            $data[] = $row;
        }
        
        $this->setData($data);
    }
    
    public function numericOrdering($a_field)
    {
        if (in_array($a_field, array("seq"))) {
            return true;
        }
        return false;
    }

    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;
                    
        if (isset($a_set["seq"])) {
            $this->tpl->setVariable("VAL_SEQ", $a_set["seq"]);
        } else {
            $this->tpl->setVariable("VAL_SEQ", $a_set["name"]);
        }
            
        if ($a_set["tstamp"]) {
            $a_set["tstamp"] = ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_DATETIME));
        }
        $this->tpl->setVariable("VAL_TSTAMP", $a_set["tstamp"]);
        
        $this->tpl->setVariable(
            "VAL_STATUS",
            $a_set["valid"]
            ? $this->lng->txt("yes")
            : $this->lng->txt("no")
        );
        
        $ilCtrl->setParameter($this->parent_obj, "peer_id", $a_set["peer_id"]);
        $url = $ilCtrl->getLinkTarget($this->parent_obj, "editPeerReviewItem");
        $ilCtrl->setParameter($this->parent_obj, "pid", "");
                
        $this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit"));
        $this->tpl->setVariable("URL_ACTION", $url);
    }
}
