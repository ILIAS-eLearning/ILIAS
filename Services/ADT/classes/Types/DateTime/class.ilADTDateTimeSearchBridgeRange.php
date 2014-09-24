<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeRange.php";

class ilADTDateTimeSearchBridgeRange extends ilADTSearchBridgeRange
{	
	protected $text_input; // [bool]
	
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTDateTimeDefinition);
	}
	
	public function setTextInputMode($a_value)
	{
		$this->text_input = (bool)$a_value;
	}
		
	
	// table2gui / filter	
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{				
			if($value["lower"])
			{
				$this->getLowerADT()->setDate(new ilDateTime($value["lower"], IL_CAL_DATETIME));
			}
			if($value["upper"])
			{
				$this->getUpperADT()->setDate(new ilDateTime($value["upper"], IL_CAL_DATETIME));
			}
		}				
	}
	
	
	// form
	
	public function addToForm()
	{				
		global $lng;
		
		if($this->getForm() instanceof ilPropertyFormGUI)
		{
			// :TODO: use DateDurationInputGUI ?!

			if(!(bool)$this->text_input)
			{
				$check = new ilCheckboxInputGUI($this->getTitle(), $this->addToElementId("tgl"));
				$check->setValue(1);
				$checked = false;
			}
			else
			{
				$check = new ilCustomInputGUI($this->getTitle());				
			}
			
			$date_from = new ilDateTimeInputGUI($lng->txt('from'), $this->addToElementId("lower"));
			$date_from->setShowTime(true);
			$check->addSubItem($date_from);
			
			if($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull())
			{
				$date_from->setDate($this->getLowerADT()->getDate());
				$checked = true;
			}

			$date_until = new ilDateTimeInputGUI($lng->txt('until'), $this->addToElementId("upper"));
			$date_until->setShowTime(true);
			$check->addSubItem($date_until);
			
			if($this->getUpperADT()->getDate() && !$this->getUpperADT()->isNull())
			{
				$date_until->setDate($this->getUpperADT()->getDate());
				$checked = true;
			}
			
			if(!(bool)$this->text_input)
			{			
				$check->setChecked($checked);
			}
			else
			{
				$date_from->setMode(ilDateTimeInputGUI::MODE_INPUT);
				$date_until->setMode(ilDateTimeInputGUI::MODE_INPUT);
			}

			$this->addToParentElement($check);
		}
		else
		{
			// see ilTable2GUI::addFilterItemByMetaType()
			include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
			include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
			$item = new ilCombinationInputGUI($this->getTitle(), $this->getElementId());
			
			$lower = new ilDateTimeInputGUI("", $this->addToElementId("lower"));
			$lower->setShowTime(true);
			$item->addCombinationItem("lower", $lower, $lng->txt("from"));
			
			if($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull())
			{
				$lower->setDate($this->getLowerADT()->getDate());
			}
			
			$upper = new ilDateTimeInputGUI("", $this->addToElementId("upper"));
			$upper->setShowTime(true);
			$item->addCombinationItem("upper", $upper, $lng->txt("to"));
			
			if($this->getUpperADT()->getDate() && !$this->getUpperADT()->isNull())
			{
				$upper->setDate($this->getUpperADT()->getDate());
			}
			
			$item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
			$item->setMode(ilDateTimeInputGUI::MODE_INPUT);
			
			$this->addToParentElement($item);
		}
	}
	
	protected function shouldBeImportedFromPost(array $a_post)
	{
		if($this->getForm() instanceof ilPropertyFormGUI &&
			!(bool)$this->text_input)
		{
			return (bool)$a_post["tgl"];
		}
		return parent::shouldBeImportedFromPost($a_post);
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
		
		if($post && $this->shouldBeImportedFromPost($post))
		{	
			include_once "Services/ADT/classes/class.ilADTDateSearchUtil.php";
			
			if(!$this->getForm() instanceof ilPropertyFormGUI ||
				(bool)$this->text_input)
			{
				$start = ilADTDateSearchUtil::handleTextInputPost(ilADTDateSearchUtil::MODE_DATETIME, $post["lower"]);
				$end = ilADTDateSearchUtil::handleTextInputPost(ilADTDateSearchUtil::MODE_DATETIME, $post["upper"]);								
			}
			else
			{
				// if checkInput() is called before, this will not work
				
				$start = ilADTDateSearchUtil::handleSelectInputPost(ilADTDateSearchUtil::MODE_DATETIME, $post["lower"]);									
				$end = ilADTDateSearchUtil::handleSelectInputPost(ilADTDateSearchUtil::MODE_DATETIME, $post["upper"]);			
			}
			
			if($start && $end && $start > $end)
			{
				$tmp = $start;
				$start = $end;
				$end = $tmp;
			}
			
			// :TODO: all dates are imported as valid 
			
			if($start)
			{
				$start = new ilDateTime($start, IL_CAL_UNIX);
			}
			if($end)
			{
				$end =  new ilDateTime($end, IL_CAL_UNIX);
			}
			
			if($this->getForm() instanceof ilPropertyFormGUI)
			{
				$item = $this->getForm()->getItemByPostVar($this->getElementId()."[lower]");		
				$item->setDate($start);
				
				$item = $this->getForm()->getItemByPostVar($this->getElementId()."[upper]");		
				$item->setDate($end);		

				if(!(bool)$this->text_input)
				{
					$item = $this->getForm()->getItemByPostVar($this->getElementId()."[tgl]");		
					$item->setChecked(true);
				}
			}
			else if(array_key_exists($this->getElementId(), $this->table_filter_fields))
			{								
				$this->table_filter_fields[$this->getElementId()]->getCombinationItem("lower")->setDate($start);				
				$this->table_filter_fields[$this->getElementId()]->getCombinationItem("upper")->setDate($end);				
				$this->writeFilter(array(
					"lower" => (!$start || $start->isNull()) ? null: $start->get(IL_CAL_DATETIME),
					"upper" => (!$end || $end->isNull()) ? null : $end->get(IL_CAL_DATETIME)
				));
			}		
			
			$this->getLowerADT()->setDate($start);
			$this->getUpperADT()->setDate($end);
		}
		else
		{
			if($this->getForm() instanceof ilPropertyFormGUI &&
				!(bool)$this->text_input)
			{
				$item = $this->getForm()->getItemByPostVar($this->getElementId()."[tgl]");		
				$item->setChecked(false);		
			}
			
			$this->getLowerADT()->setDate();
			$this->getUpperADT()->setDate();
		}	
	}
	
	
	// db
	
	public function getSQLCondition($a_element_id)
	{
		global $ilDB;
		
		if(!$this->isNull() && $this->isValid())		
		{
			$sql = array();
			if(!$this->getLowerADT()->isNull())
			{
				$sql[] = $a_element_id." >= ".$ilDB->quote($this->getLowerADT()->getDate()->get(IL_CAL_DATETIME), "timestamp");
			}
			if(!$this->getUpperADT()->isNull())
			{
				$sql[] = $a_element_id." <= ".$ilDB->quote($this->getUpperADT()->getDate()->get(IL_CAL_DATETIME), "timestamp");
			}
			return "(".implode(" AND ", $sql).")";
		}
	}
	
	public function isInCondition(ilADTDateTime $a_adt)
	{
		if(!$this->getLowerADT()->isNull() && !$this->getUpperADT()->isNull())
		{
			return $a_adt->isInbetweenOrEqual($this->getLowerADT(), $this->getUpperADT());
		}
		else if(!$this->getLowerADT()->isNull())
		{
			return $a_adt->isLargerOrEqual($this->getLowerADT());
		}
		else 
		{
			return $a_adt->isSmallerOrEqual($this->getUpperADT());
		}
	}	
	
	
	//  import/export	
		
	public function getSerializedValue()
	{		
		if(!$this->isNull() && $this->isValid())		
		{
			$res = array();		
			if(!$this->getLowerADT()->isNull())
			{
				$res["lower"] = $this->getLowerADT()->getDate()->get(IL_CAL_DATETIME);
			}
			if(!$this->getUpperADT()->isNull())
			{
				$res["upper"] = $this->getUpperADT()->getDate()->get(IL_CAL_DATETIME);
			}
			return serialize($res);
		}		
	}
	
	public function setSerializedValue($a_value)
	{		
		$a_value = unserialize($a_value);
		if(is_array($a_value))
		{
			if(isset($a_value["lower"]))
			{
				$this->getLowerADT()->setDate(new ilDateTime($a_value["lower"], IL_CAL_DATETIME));
			}
			if(isset($a_value["upper"]))
			{
				$this->getUpperADT()->setDate(new ilDateTime($a_value["upper"], IL_CAL_DATETIME));
			}
		}		
	}
}

?>