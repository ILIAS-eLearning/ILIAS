<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTextSubsetCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAssAnswerCorrectionsInputGUI extends ilAnswerWizardInputGUI
{
	/**
	 * @var bool
	 */
	protected $hidePointsEnabled = false;
	
	/**
	 * @return bool
	 */
	public function isHidePointsEnabled(): bool
	{
		return $this->hidePointsEnabled;
	}
	
	/**
	 * @param bool $hidePointsEnabled
	 */
	public function setHidePointsEnabled(bool $hidePointsEnabled)
	{
		$this->hidePointsEnabled = $hidePointsEnabled;
	}
	
	public function checkInput()
	{
		
	}
	
	public function insert($a_tpl)
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.prop_textsubsetcorrection_input.html", true, true, "Modules/TestQuestionPool");
		$i = 0;
		foreach ($this->values as $value)
		{
			if(!$this->isHidePointsEnabled())
			{
				$tpl->setCurrentBlock("points");
				$tpl->setVariable("POST_VAR", $this->getPostVar());
				$tpl->setVariable("ROW_NUMBER", $i);
				$tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
				$tpl->setVariable("POINTS", ilUtil::prepareFormOutput($value->getPoints()));
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ANSWER", ilUtil::prepareFormOutput($value->getAnswertext()));
			$tpl->parseCurrentBlock();
			$i++;
		}
		
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("ANSWER_TEXT", $this->getTextInputLabel($lng));
		
		if(!$this->isHidePointsEnabled())
		{
			$tpl->setVariable("POINTS_TEXT", $this->getPointsInputLabel($lng));
		}
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}