<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Form/classes/class.ilFormPropertyGUI.php";

/**
 * This class represents a text property in a property form.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAccomodations
 */
class ilAccomodationsPeriodInputGUI extends ilFormPropertyGUI
{
	protected $start; // [YYYY-MM-DD]
	protected $end; // [YYYY-MM-DD]
	protected $value; // [array]
	
	const MODE_SIMPLE = 1;
	const MODE_OVERNIGHT = 2;
	
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("period");
		$this->setMode(self::MODE_SIMPLE);
	}
	
	public function setMode($a_value)
	{		
		$this->mode = $a_value;
	}
	
	public function setPeriod($a_start, $a_end)
	{
		if($a_start > $a_end)
		{			
			$tmp = $a_start;
			$a_start = $a_end;
			$a_end = $tmp;
		}
		
		$this->start = $a_start;
		$this->end = $a_end;
	}	
	
	public static function convertPeriodToDays($a_start, $a_end, $as_objects = false)
	{
		$today_obj = new ilDate($a_start, IL_CAL_DATE);	
		
		$res = array();
		$loop = 0;
		$today = $today_obj->get(IL_CAL_DATE);				
		while($today <= $a_end && $loop < 100)
		{			
			if(!$as_objects)
			{
				$res[] = $today;
			}
			else
			{
				$res[] = clone($today_obj);
			}
			
			$today_obj->increment(IL_CAL_DAY, 1);							
			$today = $today_obj->get(IL_CAL_DATE);				
			$loop++;
		}
		
		return $res;
	}
	
	public static function convertDaysToChunks($a_value)
	{
		$res = array();
		
		if(is_array($a_value))
		{
			$last = null;
			$start = $a_value;
			$start = array_shift($start);
			while(sizeof($a_value))
			{
				$today = array_shift($a_value);
				
				if($last)
				{
					$diff = new ilDate($today, IL_CAL_DATE);
					$diff->increment(IL_CAL_DAY, -1);
					$diff = $diff->get(IL_CAL_DATE);					
					if($diff != $last)
					{
						$res[] = array($start, $last);
						$start = $today;
					}
				}
				
				$last = $today;
			}
			
			$res[] = array($start, $last);
		}
		
		return $res;
	}
	
	protected function getPeriodAsDays($as_objects = false)
	{
		$start = $this->start;
		
		if($this->mode == self::MODE_OVERNIGHT)
		{
			// night before 1st day of period
			$start = new ilDate($this->start, IL_CAL_DATE);
			$start->increment(IL_CAL_DAY, -1); 
			$start = $start->get(IL_CAL_DATE);
		};
		
		return self::convertPeriodToDays($start, $this->end, $as_objects);
	}		
		
	public function setValue($a_value)
	{		
		if(is_array($a_value) && !sizeof($a_value))
		{
			$a_value = null;
		}
		$this->value = $a_value;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function setValueByMissing($a_missing)
	{					
		$value = array();
		
		foreach($this->getPeriodAsDays() as $today)
		{
			if(!is_array($a_missing) ||
				(is_array($a_missing) && !in_array($today, $a_missing)))
			{
				$value[] = $today;
			}
		}							
		
		$this->setValue($value);
	}
	
	public function setValueByChunks($a_value, $a_allow_default = false)
	{
		$value = array();
		
		if(!is_array($a_value) || !sizeof($a_value))
		{
			if($a_allow_default)
			{				
				$end = new ilDate($this->end, IL_CAL_DATE);
				$end->increment(IL_CAL_DAY, -1);
				$a_value = array(array($this->start, $end->get(IL_CAL_DATE)));
			}			
		}
		
		if(is_array($a_value))
		{						
			foreach($this->getPeriodAsDays() as $today)
			{
				foreach($a_value as $chunk)
				{
					if($today >= $chunk[0] && $today <= $chunk[1])
					{
						$value[] = $today;
						break;
					}				
				}
			}
		}
		
		$this->setValue($value);
	}
	
	public function setValueByArray($a_value)
	{
		$value = $a_value[$this->getPostVar()];		
		$this->setValue($value);		
	}
	
	public function checkInput()
	{
		global $lng;
		
		if ($this->getRequired() && !$_POST[$this->getPostVar()])
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}		
		return true;
	}
	
	protected function render()
	{
		global $lng;
			
		$value = $this->getValue();
	
		$tpl = new ilTemplate("tpl.period_input.html", true, true, "Services/Accomodations");
		
		// #4474
		$reldate = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);
		
		// overnight list of dates (from/to)
		if($this->mode == self::MODE_OVERNIGHT)
		{			
			$tpl->setVariable("OV_TXT_HEAD_FROM", $lng->txt("acco_period_input_from"));
			$tpl->setVariable("OV_TXT_HEAD_TO", $lng->txt("acco_period_input_to"));		
		
			foreach($this->getPeriodAsDays(true) as $today_obj)		
			{			
				$today = $today_obj->get(IL_CAL_DATE);								
				$tomorrow = clone($today_obj);
				$tomorrow->increment(IL_CAL_DAY ,1);

				$tpl->setCurrentBlock("overnight_rows_bl");
				$tpl->setVariable("OV_POSTVAR", $this->getPostVar());
				$tpl->setVariable("OV_VALUE", $today);

				if($this->getDisabled())
				{
					$tpl->setVariable("OV_DISABLED", ' disabled="disabled"');
				}

				if(is_array($value) && in_array($today, $value))
				{				
					$tpl->setVariable("OV_CHECKED", ' checked="checked"');
				}
				
				// night after last day
				if($today == $this->end)
				{										
					$tpl->setVariable("OV_TXT_FROM", ilDatePresentation::formatDate($today_obj));	
					$tpl->setVariable("OV_TXT_TO",  $lng->txt("acco_period_input_to_last"));				
				}
				// night in period
				else if($today >= $this->start)
				{							
					$tpl->setVariable("OV_TXT_FROM", ilDatePresentation::formatDate($today_obj));
					$tpl->setVariable("OV_TXT_TO", ilDatePresentation::formatDate($tomorrow));	
				}
				// night before 1st day
				else 
				{				
					$tpl->setVariable("OV_TXT_FROM",  $lng->txt("acco_period_input_from_first"));	
					$tpl->setVariable("OV_TXT_TO", ilDatePresentation::formatDate($tomorrow));					
				}

				$tpl->parseCurrentBlock();
			}
		}
		// simple list of dates
		else
		{						
			foreach($this->getPeriodAsDays(true) as $today_obj)		
			{			
				$today = $today_obj->get(IL_CAL_DATE);
				
				$tpl->setCurrentBlock("simple_rows_bl");
				$tpl->setVariable("SPL_POSTVAR", $this->getPostVar());
				$tpl->setVariable("SPL_VALUE", $today);

				if($this->getDisabled())
				{
					$tpl->setVariable("SPL_DISABLED", ' disabled="disabled"');
				}

				if(is_array($value) && in_array($today, $value))
				{				
					$tpl->setVariable("SPL_CHECKED", ' checked="checked"');
				}

				$tpl->setVariable("SPL_TXT", ilDatePresentation::formatDate($today_obj));
				$tpl->parseCurrentBlock();				
			}
		}
		
		ilDatePresentation::setUseRelativeDates($reldate);
		
		// keep value(s) if disabled
		if($this->getDisabled() && is_array($value))
		{
			$tpl->setCurrentBlock("hidden_bl");
			foreach($value as $part)
			{
				$tpl->setVariable("HIDDEN_POSTVAR", $this->getPostVar());
				$tpl->setVariable("HIDDEN_VALUE", $part);
				$tpl->parseCurrentBlock();
			}
		}		
		
		$tpl->setVariable("ID", $this->getFieldId());
		
		return $tpl->get();
	}
	
	public function insert($a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}
	
	public function parseInputMissing()
	{
		// parse input into list of missing days from period
		
		$res = array();
		
		$value = $_POST[$this->getPostVar()];
	
		foreach($this->getPeriodAsDays() as $today)		
		{			
			if(!is_array($value) || !in_array($today, $value))
			{
				$res[] = $today;
			}
		}		
	
		return $res;
	}
	
	public function parseInputChunks()
	{
		// parse input into chunks with consecutive days
		// chunk => from, to
		
		return self::convertDaysToChunks($_POST[$this->getPostVar()]);		
	}
}

?>