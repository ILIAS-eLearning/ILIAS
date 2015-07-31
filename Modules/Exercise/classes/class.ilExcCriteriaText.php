<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExcCriteria.php";

/**
 * Class ilExcCriteriaText
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaText extends ilExcCriteria
{
	protected function getType()
	{
		return "text";
	}
	
	
	//
	// EDITOR
	// 
	
	public function initCustomForm(ilPropertyFormGUI $a_form)
	{
		global $lng;
		
		$peer_char_tgl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_min_chars_tgl"), "peer_char_tgl");
		$a_form->addItem($peer_char_tgl);
		
		$peer_char = new ilNumberInputGUI($lng->txt("exc_peer_review_min_chars"), "peer_char");
		$peer_char->setInfo($lng->txt("exc_peer_review_min_chars_info"));
		$peer_char->setRequired(true);
		$peer_char->setSize(3);
		$peer_char_tgl->addSubItem($peer_char);
	}
	
	public function exportCustomForm(ilPropertyFormGUI $a_form)
	{
		$def = $this->getDefinition();
		if(is_array($def))
		{
			$a_form->getItemByPostVar("peer_char_tgl")->setChecked(true);
			$a_form->getItemByPostVar("peer_char")->setValue($def["chars"]);
		}
	}
	
	public function importCustomForm(ilPropertyFormGUI $a_form)
	{
		$def = null;
		
		if($a_form->getInput("peer_char_tgl"))
		{			
			$def = array("chars" => (int)$a_form->getInput("peer_char"));
		}
		
		$this->setDefinition($def);			
	}		
}
