<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * List all peer groups for assignment
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentPeerReviewOverviewTableGUI extends ilTable2GUI
{
    protected ilExAssignment $ass;
    protected array $panel_info = [];
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExAssignment $a_ass
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilCtrl = $DIC->ctrl();
                
        $this->ass = $a_ass;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setLimit(9999);
    
        $this->addColumn($this->lng->txt("exc_peer_review_recipient"), "recipient");
        $this->addColumn($this->lng->txt("exc_peer_review_giver"), "giver");
        $this->addColumn($this->lng->txt("status"), "status");
        
        $this->setDefaultOrderField("recipient");
                        
        $this->setRowTemplate("tpl.exc_peer_review_overview_row.html", "Modules/Exercise");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setTitle($a_ass->getTitle() . ": " . $this->lng->txt("exc_peer_review_overview"));
        
        $this->disable("numinfo");
        
        $this->getItems();
        
        $this->addCommandButton("confirmResetPeerReview", $this->lng->txt("exc_peer_review_reset"));
    }
    
    protected function translateUserIds(
        array $a_user_ids
    ) : array {
        if (!is_array($a_user_ids) && is_numeric($a_user_ids)) {
            $a_user_ids = array($a_user_ids);
        }
        
        $res = array();
        
        foreach (array_unique($a_user_ids) as $user_id) {
            $res[] = ilUserUtil::getNamePresentation($user_id);
        }
        
        return $res;
    }
    
    public function getPanelInfo() : array
    {
        return $this->panel_info;
    }
    
    protected function getItems() : void
    {
        $data = array();
        
        $peer_review = new ilExPeerReview($this->ass);
        $tmp = $peer_review->validatePeerReviewGroups();
        
        if (!is_array($tmp)) {
            return;
        }
        
        foreach ($tmp["reviews"] as $peer_id => $reviews) {
            $peer = current($this->translateUserIds([$peer_id]));
            
            foreach ($reviews as $giver_id => $status) {
                $data[] = array("recipient" => $peer,
                    "giver" => current($this->translateUserIds([$giver_id])),
                    "status" => ($status ? $this->lng->txt("valid") : ""));
            }
        }
        
        if ($tmp["missing_user_ids"]) {
            $this->panel_info[] = array(
                "title" => $this->lng->txt("exc_peer_review_missing_users"),
                "value" => $this->translateUserIds($tmp["missing_user_ids"])
            );
        }
        
        if ($tmp["not_returned_ids"]) {
            $this->panel_info[] = array(
                "title" => $this->lng->txt("exc_peer_review_not_returned_users"),
                "value" => $this->translateUserIds($tmp["not_returned_ids"])
            );
        }
        
        if ($tmp["invalid_peer_ids"]) {
            $this->panel_info[] = array(
                "title" => $this->lng->txt("exc_peer_review_invalid_peer_ids"),
                "value" => $this->translateUserIds($tmp["invalid_peer_ids"])
            );
        }
        
        if ($tmp["invalid_giver_ids"]) {
            $this->panel_info[] = array(
                "title" => $this->lng->txt("exc_peer_review_invalid_giver_ids"),
                "value" => $this->translateUserIds($tmp["invalid_giver_ids"])
            );
        }
        
        $this->setData($data);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("PEER", $a_set["recipient"]);
        $this->tpl->setVariable("GIVER", $a_set["giver"]);
        $this->tpl->setVariable("STATUS", $a_set["status"]);
    }
}
