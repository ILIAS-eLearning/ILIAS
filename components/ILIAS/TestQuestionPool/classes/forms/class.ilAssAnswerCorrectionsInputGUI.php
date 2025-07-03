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
 * Class ilTextSubsetCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class ilAssAnswerCorrectionsInputGUI extends ilAnswerWizardInputGUI
{
    protected bool $hide_points_enabled = false;

    public function isHidePointsEnabled(): bool
    {
        return $this->hide_points_enabled;
    }

    public function setHidePointsEnabled(bool $hide_points_enabled): void
    {
        $this->hide_points_enabled = $hide_points_enabled;
    }

    public function setValue($a_value): void
    {
        foreach ($this->forms_helper->transformPoints($a_value) as $index => $value) {
            $this->values[$index]->setPoints($value);
        }
    }

    public function checkInput(): bool
    {
        $points = $this->forms_helper->checkPointsInputEnoughPositive($this->raw($this->getPostVar()), true);
        if (!$this->isHidePointsEnabled() && !is_array($points)) {
            $this->setAlert($this->lng->txt($points));
            return false;
        }

        return true;
    }

    public function insert(ilTemplate $a_tpl): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $tpl = new ilTemplate('tpl.prop_textsubsetcorrection_input.html', true, true, 'components/ILIAS/TestQuestionPool');
        $i = 0;
        foreach ($this->values as $value) {
            if (!$this->isHidePointsEnabled()) {
                $tpl->setCurrentBlock('points');
                $tpl->setVariable('POST_VAR', $this->getPostVar());
                $tpl->setVariable('ROW_NUMBER', $i);
                $tpl->setVariable('POINTS_ID', $this->getPostVar() . "[points][$i]");
                $tpl->setVariable('POINTS', $this->prepareFormOutput($value->getPoints()));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('row');
            $tpl->setVariable('ANSWER', $this->prepareFormOutput($value->getAnswertext()));
            $tpl->parseCurrentBlock();
            $i++;
        }

        $tpl->setVariable('ELEMENT_ID', $this->getPostVar());
        $tpl->setVariable('ANSWER_TEXT', $this->getTextInputLabel($lng));

        if (!$this->isHidePointsEnabled()) {
            $tpl->setVariable('POINTS_TEXT', $this->getPointsInputLabel($lng));
        }

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
