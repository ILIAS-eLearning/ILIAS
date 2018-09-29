<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssSingleChoiceCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssMultipleChoiceCorrectionsInputGUI extends ilSingleChoiceWizardInputGUI
{
	/**
	 * @var assSingleChoice
	 */
	protected $qstObject;
	
	public function checkInput()
	{
		
	}
	
	public function insert($a_tpl)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		$lng = $DIC->language();
		
		$tpl = new ilTemplate("tpl.prop_multiplechoicecorrection_input.html", true, true, "Modules/TestQuestionPool");
		
		$i = 0;
		
		foreach ($this->values as $value)
		{
			if (strlen($value->getImage()))
			{
				$imagename = $this->qstObject->getImagePathWeb() . $value->getImage();
				if (($this->getSingleline()) && ($this->qstObject->getThumbSize()))
				{
					if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getImage()))
					{
						$imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getImage();
					}
				}
				
				$tpl->setCurrentBlock('image');
				$tpl->setVariable('SRC_IMAGE', $imagename);
				$tpl->setVariable('IMAGE_NAME', $value->getImage());
				$tpl->setVariable('ALT_IMAGE', ilUtil::prepareFormOutput($value->getAnswertext()));
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock("answer");
			$tpl->setVariable("ANSWER", ilUtil::prepareFormOutput($value->getAnswertext()));
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("POINTS_POST_VAR", 'POINTS');
			$tpl->setVariable("POINTS_ROW_NUMBER", $i);
			$tpl->setVariable("PROPERTY_VALUE_CHECKED", ilUtil::prepareFormOutput($value->getPointsChecked()));
			$tpl->setVariable("PROPERTY_VALUE_UNCHECKED", ilUtil::prepareFormOutput($value->getPointsUnchecked()));
			$tpl->parseCurrentBlock();
			
			$i++;
		}
		
		$tpl->setCurrentBlock("image_heading");
		$tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
		$tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("points_heading");
		$tpl->setVariable("POINTS_CHECKED_TEXT", $lng->txt('points_checked'));
		$tpl->setVariable("POINTS_UNCHECKED_TEXT", $lng->txt('points_unchecked'));
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}