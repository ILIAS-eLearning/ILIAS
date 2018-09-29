<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssClozeTestCombinationVariantsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssClozeTestCombinationVariantsInputGUI extends ilTextInputGUI
{
	public function checkInput()
	{
		
	}
	
	public function insert($a_tpl)
	{
		$tpl = new ilTemplate('tpl.prop_gap_combi_answers_input.html', true, true, 'Modules/TestQuestionPool');
		
		$gaps = array();
		
		foreach($this->value as $varId => $variant)
		{
			foreach($variant['gaps'] as $gapIndex => $answer)
			{
				$gaps[$gapIndex] = $gapIndex;

				$tpl->setCurrentBlock('gap_answer');
				$tpl->setVariable('GAP_ANSWER', $answer);
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock('variant');
			$tpl->setVariable('POINTS', $variant['points']);
			$tpl->parseCurrentBlock();
		}
		
		foreach($gaps as $gapIndex)
		{
			$tpl->setCurrentBlock('gap_header');
			$tpl->setVariable('GAP_HEADER', 'Gap '.($gapIndex + 1));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable('POINTS_HEADER', 'Points');
			
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}