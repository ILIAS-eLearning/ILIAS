<?php
require_once("./Services/Form/classes/class.ilDurationInputGUI.php");
/**
 * Class ilSessionDurationInputGUI
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilSessionDurationInputGUI extends ilDurationInputGUI
{
	protected $minute_step_size = 0;


	/**
	* Insert property html
	*
	*/
	function render()
	{
		global $lng;

		$tpl = new ilTemplate("tpl.prop_duration.html", true, true, "Services/Form");

		if($this->getShowMonths())
		{
			$tpl->setCurrentBlock("dur_months");
			$tpl->setVariable("TXT_MONTHS", $lng->txt("form_months"));
			$val = array();
			for ($i=0; $i<=36; $i++)
			{
				$val[$i] = $i;
			}
			$tpl->setVariable("SELECT_MONTHS",
				ilUtil::formSelect($this->getMonths(), $this->getPostVar()."[MM]",
				$val, false, true, 0, '', '', $this->getDisabled()));
			$tpl->parseCurrentBlock();
		}
		if ($this->getShowDays())
		{
			$tpl->setCurrentBlock("dur_days");
			$tpl->setVariable("TXT_DAYS", $lng->txt("form_days"));
			$val = array();
			for ($i=0; $i<=366; $i++)
			{
				$val[$i] = $i;
			}
			$tpl->setVariable("SELECT_DAYS",
				ilUtil::formSelect($this->getDays(), $this->getPostVar()."[dd]",
				$val, false, true, 0, '', '', $this->getDisabled()));
			$tpl->parseCurrentBlock();
		}
		if ($this->getShowHours())
		{
			$tpl->setCurrentBlock("dur_hours");
			$tpl->setVariable("TXT_HOURS", $lng->txt("form_hours"));
			$val = array();
			for ($i=0; $i<=23; $i++)
			{
				$val[$i] = $this->addLeadingZero($i);
			}
			$tpl->setVariable("SELECT_HOURS",
				ilUtil::formSelect($this->getHours(), $this->getPostVar()."[hh]",
				$val, false, true, 0, '', '', $this->getDisabled()));
			$tpl->parseCurrentBlock();
		}
		if ($this->getShowMinutes())
		{
			$tpl->setCurrentBlock("dur_minutes");
			$tpl->setVariable("TXT_MINUTES", $lng->txt("form_minutes"));
			$val = array();
			$step = $this->getMinuteStepSize();
			for ($i=0; $i<=59; $i++)
			{
				$val[$i] = $this->addLeadingZero($i);
				$i += $this->getMinuteStepSize();
			}

			$tpl->setVariable("SELECT_MINUTES",
				ilUtil::formSelect($this->getMinutes(), $this->getPostVar()."[mm]",
				$val, false, true, 0, '', '', $this->getDisabled()));
			$tpl->parseCurrentBlock();
		}
		if ($this->getShowSeconds())
		{
			$tpl->setCurrentBlock("dur_seconds");
			$tpl->setVariable("TXT_SECONDS", $lng->txt("form_seconds"));
			$val = array();
			for ($i=0; $i<=59; $i++)
			{
				$val[$i] = $i;
			}
			$tpl->setVariable("SELECT_SECONDS",
				ilUtil::formSelect($this->getSeconds(), $this->getPostVar()."[ss]",
				$val, false, true, 0, '', '', $this->getDisabled()));
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * Set minute step size
	 *
	 * @param 	int 	$step_size
	 * @return 	void
	 */
	public function setMinuteStepSize($step_size)
	{
		$this->minute_step_size = $step_size - 1;
	}

	/**
	 * Get minute step size
	 *
	 * @return int
	 */
	public function getMinuteStepSize()
	{
		return $this->minute_step_size;
	}

	/**
	 * Add leading zero to one character digits
	 *
	 * @param 	int 	$num
	 * @return 	string
	 */
	public function addLeadingZero($num)
	{
		assert('is_int($num)');

		return str_pad($num, 2, "0", STR_PAD_LEFT);
	}
}