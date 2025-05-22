<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilAssClozeTestCombinationVariantsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilAssClozeTestCombinationVariantsInputGUI extends ilAnswerWizardInputGUI
{
    public function setValue($a_value): void
    {
        foreach ($this->forms_helper->transformPoints($a_value) as $index => $value) {
            $this->values[$index]['points'] = $value;
        }
    }

    public function checkInput(): bool
    {
        // check points
        $points = $this->forms_helper->checkPointsInputEnoughPositive($this->raw($this->getPostVar()), true);
        if (!is_array($points)) {
            $this->setAlert($this->lng->txt($points));
            return false;
        }
        return true;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tpl.prop_gap_combi_answers_input.html', true, true, 'components/ILIAS/TestQuestionPool');
        $gaps = [];

        foreach ($this->values as $variant) {
            foreach ($variant['gaps'] as $gap_index => $answer) {
                $gaps[$gap_index] = $gap_index;

                $tpl->setCurrentBlock('gap_answer');
                $tpl->setVariable('GAP_ANSWER', $answer);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('variant');
            $tpl->setVariable('POSTVAR', $this->getPostVar());
            $tpl->setVariable('POINTS', $variant['points']);
            $tpl->parseCurrentBlock();
        }

        foreach ($gaps as $gap_index) {
            $tpl->setCurrentBlock('gap_header');
            $tpl->setVariable('GAP_HEADER', 'Gap ' . ($gap_index + 1));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('POINTS_HEADER', 'Points');

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
