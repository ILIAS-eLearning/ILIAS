<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeRange.php";

class ilADTDateSearchBridgeRange extends ilADTSearchBridgeRange
{	
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTDateDefinition);
	}
	
	
	// table2gui / filter	
	
	public function loadFilter()
	{
		$value = $this->readFilter();
		if($value !== null)
		{		
			if($value["lower"])
			{
				$this->getLowerADT()->setDate(new ilDate($value["lower"], IL_CAL_DATE));
			}
			if($value["upper"])
			{
				$this->getUpperADT()->setDate(new ilDate($value["upper"], IL_CAL_DATE));
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

			$check = new ilCheckboxInputGUI($this->getTitle(), $this->addToElementId("tgl"));
			$check->setValue(1);

			$date_from = new ilDateTimeInputGUI($lng->txt('from'), $this->addToElementId("lower"));
			$date_from->setShowTime(false);
			$check->addSubItem($date_from);

			$date_until = new ilDateTimeInputGUI($lng->txt('until'), $this->addToElementId("upper"));
			$date_until->setShowTime(false);
			$check->addSubItem($date_until);

			$this->addToParentElement($check);
		}
		else
		{
			include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
			include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
			
			$item = new ilCombinationInputGUI($this->getTitle(), $this->getElementId());			
			
			$lower = new ilDateTimeInputGUI("", $this->addToElementId("lower"));							
			$item->addCombinationItem("lower", $lower, $lng->txt("from"));
			
			if($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull())
			{
				$lower->setDate($this->getLowerADT()->getDate());
			}
			
			$upper = new ilDateTimeInputGUI("", $this->addToElementId("upper"));
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
		if($this->getForm() instanceof ilPropertyFormGUI)
		{
			return (bool)$a_post["tgl"];
		}
		return parent::shouldBeImportedFromPost($a_post);
	}
	
	protected function handleFilterPost($a_post)
	{
		global $ilUser;
		
		// see ilDateTimeInputGUI::checkInput()
		
		$a_post["date"] = ilUtil::stripSlashes($a_post["date"]);
		
		if($a_post["date"])
		{
			switch($ilUser->getDateFormat())
			{
				case ilCalendarSettings::DATE_FORMAT_DMY:
					$date = explode(".", $a_post["date"]);
					$dt['mday'] = (int)$date[0];
					$dt['mon'] = (int)$date[1];
					$dt['year'] = (int)$date[2];
					break;

				case ilCalendarSettings::DATE_FORMAT_YMD:
					$date = explode("-", $a_post["date"]);
					$dt['mday'] = (int)$date[2];
					$dt['mon'] = (int)$date[1];
					$dt['year'] = (int)$date[0];
					break;

				case ilCalendarSettings::DATE_FORMAT_MDY:
					$date = explode("/", $a_post["date"]);
					$dt['mday'] = (int)$date[1];
					$dt['mon'] = (int)$date[0];
					$dt['year'] = (int)$date[2];
					break;
			}
			
			return mktime(12, 0, 0, $dt["mon"], $dt["mday"], $dt["year"]);
		}		
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
	
		if($post && $this->shouldBeImportedFromPost($post))
		{			
			$start = $end = null;
			
			if(!$this->getForm() instanceof ilPropertyFormGUI)
			{
				$start = $this->handleFilterPost($post["lower"]);
				$end = $this->handleFilterPost($post["upper"]);
			}
			else
			{
				// if checkInput() is called before, this will not work
				
				if($post["lower"]["date"])
				{
					$start = mktime(12, 0, 0,
						$post["lower"]["date"]["m"], 
						$post["lower"]["date"]["d"], 
						$post["lower"]["date"]["y"]);
				}

				if($post["upper"]["date"])
				{
					$end = mktime(12, 0, 0,			
						$post["upper"]["date"]["m"], 
						$post["upper"]["date"]["d"], 
						$post["upper"]["date"]["y"]);
				}
			}
			
			if($start && $end && $start > $end)
			{
				$tmp = $start;
				$start = $end;
				$end = $tmp;
			}
			
			// :TODO: all dates are imported as valid 						
			
			$start = new ilDate($start, IL_CAL_UNIX);
			$end = new ilDate($end, IL_CAL_UNIX);
			
			if($this->getForm() instanceof ilPropertyFormGUI)
			{
				$item = $this->getForm()->getItemByPostVar($this->getElementId()."[lower]");		
				$item->setDate($start);

				$item = $this->getForm()->getItemByPostVar($this->getElementId()."[upper]");		
				$item->setDate($end);		

				$item = $this->getForm()->getItemByPostVar($this->getElementId()."[tgl]");		
				$item->setChecked(true);							
			}
			else if(array_key_exists($this->getElementId(), $this->table_filter_fields))
			{								
				$this->table_filter_fields[$this->getElementId()]->getCombinationItem("lower")->setDate($start);				
				$this->table_filter_fields[$this->getElementId()]->getCombinationItem("upper")->setDate($end);				
				$this->writeFilter(array(
					"lower" => $start->isNull() ? null: $start->get(IL_CAL_DATE),
					"upper" => $end->isNull() ? null : $end->get(IL_CAL_DATE)
				));
			}		
			
			$this->getLowerADT()->setDate($start);
			$this->getUpperADT()->setDate($end);
		}
		else
		{
			if($this->getForm() instanceof ilPropertyFormGUI)
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
				$sql[] = $a_element_id." >= ".$ilDB->quote($this->getLowerADT()->getDate()->get(IL_CAL_DATE), "date");
			}
			if(!$this->getLowerADT()->isNull())
			{
				$sql[] = $a_element_id." <= ".$ilDB->quote($this->getLowerADT()->getDate()->get(IL_CAL_DATE), "date");
			}
			return "(".implode(" AND ", $sql).")";
		}
	}
	
	public function isInCondition(ilADTDate $a_adt)
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
}

?>