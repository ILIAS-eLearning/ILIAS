<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeRange.php";

class ilADTDateTimeSearchBridgeRange extends ilADTSearchBridgeRange
{	
	protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
	{
		return ($a_adt_def instanceof ilADTDateTimeDefinition);
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

			$check = new ilCheckboxInputGUI($this->getTitle(), $this->addToElementId("tgl"));
			$check->setValue(1);

			$date_from = new ilDateTimeInputGUI($lng->txt('from'), $this->addToElementId("lower"));
			$date_from->setShowTime(true);
			$check->addSubItem($date_from);

			$date_until = new ilDateTimeInputGUI($lng->txt('until'), $this->addToElementId("upper"));
			$date_until->setShowTime(true);
			$check->addSubItem($date_until);

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
		$a_post["time"] = ilUtil::stripSlashes($a_post["time"]);
		
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
			
			if($ilUser->getTimeFormat() == ilCalendarSettings::TIME_FORMAT_12)
			{
				$seconds = "";				
				if(preg_match("/([0-9]{1,2})\s*:\s*([0-9]{1,2})\s*".$seconds."(am|pm)/", trim(strtolower($a_post["time"])), $matches))
				{
					$dt['hours'] = (int)$matches[1];
					$dt['minutes'] = (int)$matches[2];
					if($seconds)
					{
						$dt['seconds'] = (int)$time[2];
						$ampm = $matches[4];
					}
					else
					{
						$dt['seconds'] = 0;
						$ampm = $matches[3];
					}
					if($dt['hours'] == 12)
					{
						if($ampm == "am")
						{
							$dt['hours'] = 0;
						}
					}
					else if($ampm == "pm")
					{
						$dt['hours'] += 12;
					}
				}
			}
			else
			{
				$time = explode(":", $a_post["time"]);
				$dt['hours'] = (int)$time[0];
				$dt['minutes'] = (int)$time[1];
				$dt['seconds'] = (int)$time[2];
			}			
			
			return mktime($dt["hours"], $dt["minutes"], $dt["seconds"], $dt["mon"], $dt["mday"], $dt["year"]);
		}		
	}
	
	public function importFromPost(array $a_post = null)
	{		
		$post = $this->extractPostValues($a_post);
		
		if($post && $this->shouldBeImportedFromPost($post))
		{	
			if(!$this->getForm() instanceof ilPropertyFormGUI)
			{
				$start = $this->handleFilterPost($post["lower"]);
				$end = $this->handleFilterPost($post["upper"]);
			}
			else
			{
				// if checkInput() is called before, this will not work
				
				$start = mktime(
					$post["lower"]["time"]["h"], 
					$post["lower"]["time"]["m"], 
					1, 
					$post["lower"]["date"]["m"], 
					$post["lower"]["date"]["d"], 
					$post["lower"]["date"]["y"]);

				$end = mktime(
					$post["upper"]["time"]["h"], 
					$post["upper"]["time"]["m"], 
					1, 
					$post["upper"]["date"]["m"], 
					$post["upper"]["date"]["d"], 
					$post["upper"]["date"]["y"]);
			}
			
			if($start && $end && $start > $end)
			{
				$tmp = $start;
				$start = $end;
				$end = $tmp;
			}
			
			// :TODO: all dates are imported as valid 
			
			$start = new ilDateTime($start, IL_CAL_UNIX);
			$end =  new ilDateTime($end, IL_CAL_UNIX);
			
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
					"lower" => $start->isNull() ? null: $start->get(IL_CAL_DATETIME),
					"upper" => $end->isNull() ? null : $end->get(IL_CAL_DATETIME)
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
				$sql[] = $a_element_id." >= ".$ilDB->quote($this->getLowerADT()->getDate()->get(IL_CAL_DATETIME), "timestamp");
			}
			if(!$this->getLowerADT()->isNull())
			{
				$sql[] = $a_element_id." <= ".$ilDB->quote($this->getLowerADT()->getDate()->get(IL_CAL_DATETIME), "timestamp");
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
}

?>