<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSolutionComparisonExpression.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilLogicalAnswerComparisonExpressionInputGUI extends ilAnswerWizardInputGUI
{
    public function setValues($modelValues)
    {
        $formValues = array();

        foreach ($modelValues as $modelValue) {
            $formValues[] = new ASS_AnswerSimple(
                $modelValue->getExpression(),
                $modelValue->getPoints(),
                $modelValue->getOrderIndex() - 1
            );
        }

        if (!count($formValues)) {
            $formValues[] = new ASS_AnswerSimple('', 0, 1);
        }

        parent::setValues($formValues);
    }

    public function getValues()
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
    protected function getTemplate()
    {
        return "tpl.prop_lac_expression_input.html";
    }
    
    protected function sanitizeSuperGlobalSubmitValue()
    {
        if (isset($_POST[$this->getPostVar()]) && is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()], false);
        }
    }
}
