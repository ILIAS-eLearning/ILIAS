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
class ilAssSingleChoiceCorrectionsInputGUI extends ilSingleChoiceWizardInputGUI
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
		
		$tpl = new ilTemplate("tpl.prop_singlechoicecorrection_input.html", true, true, "Modules/TestQuestionPool");
		
		$i = 0;
		
		foreach ($this->values as $value) {
			if (strlen($value->getImage())) {
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
			
			$tpl->setCurrentBlock("prop_points_propval");
			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("row");
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("image_heading");
		$tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
		$tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("points_heading");
		$tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}