<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class class.ilAssMatchingPairCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssMatchingPairCorrectionsInputGUI extends ilMatchingPairWizardInputGUI
{
	public function checkInput()
	{
	}
	
	public function insert($a_tpl)
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.prop_matchingpaircorrection_input.html", true, true, "Modules/TestQuestionPool");
		$i = 0;
		
		foreach ($this->pairs as $pair)
		{
			$tpl->setCurrentBlock("row");

			foreach ($this->terms as $term)
			{
				if ($pair->term->identifier == $term->identifier)
				{
					$tpl->setVariable('TERM', $term->text);
				}
			}
			foreach ($this->definitions as $definition)
			{
				if ($pair->definition->identifier == $definition->identifier)
				{
					$tpl->setVariable('DEFINITION', $definition->text);
				}
			}
			
			$tpl->setVariable('POINTS_VALUE', $pair->points);
			$tpl->setVariable("ROW_NUMBER", $i);
			
			$tpl->setVariable("ID", $this->getPostVar() . "[$i]");
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			
			$tpl->parseCurrentBlock();
			
			$i++;
		}
		
		$tpl->setCurrentBlock('term_ids');
		$ids = array();
		foreach ($this->terms as $term)
		{
			array_push($ids, $term->identifier);
		}
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("TERM_IDS", join($ids, ","));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock('definition_ids');
		$ids = array();
		foreach ($this->definitions as $definition)
		{
			array_push($ids, $definition->identifier);
		}
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("DEFINITION_IDS", join($ids, ","));
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("TEXT_POINTS", $lng->txt('points'));
		$tpl->setVariable("TEXT_DEFINITION", $lng->txt('definition'));
		$tpl->setVariable("TEXT_TERM", $lng->txt('term'));
		$tpl->setVariable("TEXT_ACTIONS", $lng->txt('actions'));
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}