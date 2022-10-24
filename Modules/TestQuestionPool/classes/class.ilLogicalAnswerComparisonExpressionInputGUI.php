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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilLogicalAnswerComparisonExpressionInputGUI extends ilAnswerWizardInputGUI
{
    public function setValues($modelValues): void
    {
        $formValues = array();

        foreach ($modelValues as $modelValue) {
            $formValues[] = new ASS_AnswerSimple(
                $modelValue->getExpression(),
                $modelValue->getPoints(),
                $modelValue->getOrderIndex() - 1,
                -1,
                0
            );
        }

        if (!count($formValues)) {
            $formValues[] = new ASS_AnswerSimple('', 0, 1);
        }

        parent::setValues($formValues);
    }

    public function getValues(): array
    {
        $formValues = parent::getValues();

        $modelValues = array();

        foreach ($formValues as $formValue) {
            $expression = new ilAssQuestionSolutionComparisonExpression();
            $expression->setExpression($formValue->getAnswertext());
            $expression->setPoints($formValue->getPoints());
            $expression->setOrderIndex($formValue->getOrder() + 1);
            $modelValues[] = $expression;
        }

        return $modelValues;
    }

    /**
     * @param $lng
     * @return mixed
     */
    protected function getTextInputLabel($lng)
    {
        return $lng->txt('tst_sol_comp_expressions');
    }

    /**
     * @param $lng
     * @return mixed
     */
    protected function getPointsInputLabel($lng)
    {
        return $lng->txt('tst_comp_points');
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return "tpl.prop_lac_expression_input.html";
    }

    protected function sanitizeSuperGlobalSubmitValue(): void
    {
        if (isset($_POST[$this->getPostVar()]) && is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilArrayUtil::stripSlashesRecursive($_POST[$this->getPostVar()], false);
        }
    }
}
