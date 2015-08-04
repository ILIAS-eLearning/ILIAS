<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaFile
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaFile extends ilExcCriteria
{
	public function getType()
	{
		return "file";
	}
	
	
	// PEER REVIEW
	
	public function addToPeerReviewForm($a_value = null)
	{
		
	}
	
	public function importFromPeerReviewForm()
	{
		
	}
	
	public function hasValue($a_value)
	{
		
	}
	
	public function addToInfo(ilInfoScreenGUI $a_info, $a_value)
	{
		
	}
	
	public function addToAccordion(array &$a_acc, $a_value)
	{
		
					/*
					$uploads = $this->peer_review->getPeerUploadFiles($a_set["uid"], $peer_id);
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
					*/
	}
	
	public function resetReview()
	{
		/*
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->assignment->getExerciseId(), $this->assignment_id);
		$storage->deletePeerReviewUploads();			 
		*/
	}
	
}
