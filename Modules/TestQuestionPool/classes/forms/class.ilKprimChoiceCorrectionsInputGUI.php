<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilKprimChoiceCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilKprimChoiceCorrectionsInputGUI extends ilKprimChoiceWizardInputGUI
{
	public function checkInput()
	{
		
	}
	
	public function insert($a_tpl)
	{
		$tpl = new ilTemplate("tpl.prop_kprimchoicecorrection_input.html", true, true, "Modules/TestQuestionPool");
		
		foreach ($this->values as $value)
		{
			/**
			 * @var ilAssKprimChoiceAnswer $value
			 */
			
			if (strlen($value->getImageFile()))
			{
				$imagename = $value->getImageWebPath();
				
				if (($this->getSingleline()) && ($this->qstObject->getThumbSize()))
				{
					if (@file_exists($value->getThumbFsPath()))
					{
						$imagename = $value->getThumbWebPath();
					}
				}
				
				$tpl->setCurrentBlock('image');
				$tpl->setVariable('SRC_IMAGE', $imagename);
				$tpl->setVariable('IMAGE_NAME', $value->getImageFile());
				$tpl->setVariable('ALT_IMAGE', ilUtil::prepareFormOutput($value->getAnswertext()));
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock("row");

			$tpl->setVariable("ANSWER", ilUtil::prepareFormOutput($value->getAnswertext()));
			
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("ROW_NUMBER", $value->getPosition());
			$tpl->setVariable("ID", $this->getPostVar() . "[answer][{$value->getPosition()}]");
			
			$tpl->setVariable(
				"CORRECTNESS_TRUE_ID", $this->getPostVar() . "[correctness][{$value->getPosition()}][true]"
			);
			$tpl->setVariable(
				"CORRECTNESS_FALSE_ID", $this->getPostVar() . "[correctness][{$value->getPosition()}][false]"
			);
			$tpl->setVariable("CORRECTNESS_TRUE_VALUE", 1);
			$tpl->setVariable("CORRECTNESS_FALSE_VALUE", 0);
			
			if( $value->getCorrectness() !== null )
			{
				if( $value->getCorrectness() )
				{
					$tpl->setVariable('CORRECTNESS_TRUE_SELECTED', ' checked="checked"');
				}
				else
				{
					$tpl->setVariable('CORRECTNESS_FALSE_SELECTED', ' checked="checked"');
				}
			}
			
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED_CORRECTNESS", " disabled=\"disabled\"");
			}
			
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("TRUE_TEXT", $this->qstObject->getTrueOptionLabelTranslation($this->lng, $this->qstObject->getOptionLabel()));
		$tpl->setVariable("FALSE_TEXT", $this->qstObject->getFalseOptionLabelTranslation($this->lng, $this->qstObject->getOptionLabel()));
		
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("DELETE_IMAGE_HEADER", $this->lng->txt('delete_image_header'));
		$tpl->setVariable("DELETE_IMAGE_QUESTION", $this->lng->txt('delete_image_question'));
		$tpl->setVariable("ANSWER_TEXT", $this->lng->txt('answer'));
		
		$tpl->setVariable("OPTIONS_TEXT", $this->lng->txt('options'));
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}