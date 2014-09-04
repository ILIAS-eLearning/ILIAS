<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

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
	
	function __construct($a_parent_obj, $a_parent_cmd, ilExAssignment $a_ass, $a_show_peer_review = false, $a_disable_peer_review = false)
	{
		global $ilCtrl, $lng;
		
		$this->ass = $a_ass;
		$this->show_peer_review = (bool)$a_show_peer_review;
		$this->setId("excassltxt".$this->ass->getId());
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
	
		$this->setTitle($lng->txt("exc_list_text_assignment").
			": \"".$this->ass->getTitle()."\"");
		
		// if you add pagination and disable the unlimited setting:
		// fix saving of ordering of single pages!
		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("user"), "uname", "15%");
		$this->addColumn($this->lng->txt("exc_last_submission"), "udate", "10%");
		
		if($this->show_peer_review)
		{
			$this->addColumn($this->lng->txt("exc_files_returned_text"), "", "45%");		
			$this->addColumn($this->lng->txt("exc_peer_review"), "", "30%");
			
			include_once './Services/Rating/classes/class.ilRatingGUI.php';
			include_once './Services/Accordion/classes/class.ilAccordionGUI.php';
		}
		else
		{
			$this->addColumn($this->lng->txt("exc_files_returned_text"), "", "75%");		
		}
		
		$this->setDefaultOrderField("uname");
		$this->setDefaultOrderDirection("asc");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.exc_list_text_assignment_row.html", "Modules/Exercise");
	
		if(!$a_disable_peer_review &&
			$this->ass->getPeerReview() && 
			!$a_show_peer_review &&
			ilExAssignment::countGivenFeedback($this->ass->getId()))
		{
			$this->addCommandButton("listTextAssignmentWithPeerReview", $lng->txt("exc_show_peer_review"));
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
		if($this->show_peer_review)
		{
			$peer_data = $this->ass->getAllPeerReviews();
		}
		
		include_once "Services/User/classes/class.ilUserUtil.php";
		include_once "Services/RTE/classes/class.ilRTE.php";
		foreach(ilExAssignment::getAllDeliveredFiles($this->ass->getExerciseId(), $this->ass->getId()) as $file)
		{		
			if(trim($file["atext"]))
			{
				$data[$file["user_id"]] = array(
					"uid" => $file["user_id"],
					"uname" => ilUserUtil::getNamePresentation($file["user_id"]),
					"udate" => $file["ts"],
					"utext" => ilRTE::_replaceMediaObjectImageSrc($file["atext"], 1) // mob id to mob src
				);
												
				if(isset($peer_data[$file["user_id"]]))
				{
					$data[$file["user_id"]]["peer"] = $peer_data[$file["user_id"]];	
				}								
			}
		}		
		
		$this->setData($data);
	}

	protected function fillRow($a_set)
	{				
		global $ilCtrl;
		
		if($this->show_peer_review && isset($a_set["peer"]))
		{			
			$acc_data = array();
			
			foreach($a_set["peer"] as $peer_id => $peer_review)
			{	
				$peer_name = ilUserUtil::getNamePresentation($peer_id);
				$acc_item = $peer_name;
				
				if($peer_review[1])
				{
					$rating = new ilRatingGUI();
					$rating->setObject($this->ass->getId(), "ass", $a_set["uid"], "peer");
					$rating->setUserId($peer_id);			
					$acc_item .= " ".$rating->getHTML(false, false);	
				}
				
				if($peer_review[0])
				{					
					$acc_item .= '<div class="small">'.nl2br($peer_review[0])."</div>";
				}
								
				$uploads = $this->ass->getPeerUploadFiles($a_set["uid"], $peer_id);
				if($uploads)
				{					
					$acc_item .= '<div class="small">';

					$ilCtrl->setParameter($this->parent_obj, "fu", $peer_id."__".$a_set["uid"]);

					foreach($uploads as $file)
					{							
						$ilCtrl->setParameter($this->parent_obj, "fuf", md5($file));
						$dl = $ilCtrl->getLinkTarget($this->parent_obj, "downloadPeerReview");
						$ilCtrl->setParameter($this->parent_obj, "fuf", "");

						$acc_item .= '<a href="'.$dl.'">'.basename($file).'</a><br />';
					}						

					$ilCtrl->setParameter($this->parent_obj, "fu", "");

					$acc_item .= '</div>';
				}				
					
				$acc_data[$peer_id] = array("name" => $peer_name, "review" => $acc_item);
			}
			
			if($acc_data)
			{							
				$acc_data = ilUtil::sortArray($acc_data, "name", "asc");
				
				$acc = new ilAccordionGUI();
				$acc->setId($this->ass->getId()."_".$a_set["uid"]);
								
				$acc_html = "<ul>";
				foreach($acc_data as $acc_item)
				{
					$acc_html .= "<li>".$acc_item["review"]."</li>";					
				}
				$acc_html .= "</ul>";
				$acc->addItem($this->lng->txt("show")." (".sizeof($acc_data).")", $acc_html);
				
				$this->tpl->setCurrentBlock("peer_bl");
				$this->tpl->setVariable("PEER_REVIEW", $acc->getHTML());			
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->tpl->setVariable("USER_NAME", $a_set["uname"]);
		$this->tpl->setVariable("USER_DATE", 
				ilDatePresentation::formatDate(new ilDate($a_set["udate"], IL_CAL_DATETIME)));
		$this->tpl->setVariable("USER_TEXT", $a_set["utext"]);			
	}
}

?>