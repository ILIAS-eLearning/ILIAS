<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaRating
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaRating extends ilExcCriteria
{
	public function getType()
	{
		return "rating";
	}
	
	
	// PEER REVIEW
	
	public function addToPeerReviewForm($a_value = null)
	{
		global $tpl, $ilCtrl;
			
		$tpl->addJavaScript("Modules/Exercise/js/ilExcPeerReview.js");
		$tpl->addOnLoadCode("il.ExcPeerReview.setAjax('".
			$ilCtrl->getLinkTargetByClass("ilExPeerReviewGUI", "updateCritAjax", "", true, false).
			"')");
		
		$input = new ilCustomInputGUI($this->getTitle(), "prccc_rating_".$this->getId());
		$input->setInfo($this->getDescription());
		
		//  :TODO: see validate()
		// $input->setRequired($this->isRequired());
		
		$input->setHtml($this->renderWidget());
		$this->form->addItem($input);	
		
		$this->form_item = $input;
	}
	
	protected function renderWidget($a_read_only = false)
	{
		include_once './Services/Rating/classes/class.ilRatingGUI.php';
		$rating = new ilRatingGUI();
		$rating->setObject(
			$this->ass->getId(), 
			"ass", 
			$this->peer_id, 
			"peer_".(int)$this->getId()
		);
		$rating->setUserId($this->giver_id);
		
		$ajax_id = $this->getId()
			? (int)$this->getId()
			: "'rating'";
		
		if(!(bool)$a_read_only)
		{
			$html = '<div class="crit_widget">'.
				$rating->getHTML(false, true, "il.ExcPeerReview.saveCrit(this, ".$this->peer_id.", ".$ajax_id.", %rating%)").				
			'</div>';
		}
		else
		{
			$html = $rating->getHTML(false, false);		
		}
		
		return $html;
	}
	
	public function importFromPeerReviewForm()
	{
		// see updateFromAjax()
	}
	
	public function updateFromAjax()
	{						
		// save rating
		include_once './Services/Rating/classes/class.ilRating.php';
		ilRating::writeRatingForUserAndObject(
			$this->ass->getId(), 
			"ass", 
			$this->peer_id, 
			"peer_".(int)$this->getId(), 
			$this->giver_id, 
			$_POST["value"]
		);
		
		
		// render current rating
		
		// $ilCtrl->setParameter($this->parent_obj, "peer_id", $peer_id);		
		
		return $this->renderWidget($a_ass, $a_giver_id, $a_peer_id);					
	}
	
	public function validate($a_value)
	{
		global $lng;
		
		if($this->isRequired())
		{			
			if(!$this->hasValue($a_value))
			{
				if($this->form)
				{
					$this->form->getItemByPostVar("prccc_rating_".$this->getId())->setAlert($lng->txt("msg_input_is_required"));
				}
				return false;
			}
		}
		return true;
	}
	
	public function hasValue($a_value)
	{
		include_once './Services/Rating/classes/class.ilRating.php';
		return (bool)ilRating::getRatingForUserAndObject(
			$this->ass->getId(), 
			"ass", 
			$this->peer_id, 
			"peer_".(int)$this->getId(),
			$this->giver_id
		);					
	}		
	
	public function addToInfo(ilInfoScreenGUI $a_info, $a_value)
	{
		$rating = $this->renderWidget($this->ass, $this->giver_id, $this->peer_id, true);			
		
		$a_info->addProperty($this->getTitle(), $rating);
	}	
	
	public function addToAccordion(array &$a_acc, $a_value)
	{		
		$title = $this->getTitle()
			? $this->getTitle().": "
			: "";
		
		$a_acc[]= $title.
			$this->renderWidget($this->ass, $this->giver_id, $this->peer_id, true);	
	}
		
	public function resetReview()
	{
		include_once './Services/Rating/classes/class.ilRating.php';
		ilRating::resetRatingForUserAndObject(
			$this->ass->getId(), 
			"ass", 
			$this->peer_id, 
			"peer_".(int)$this->getId(),
			$this->giver_id
		);
	}
		
}
