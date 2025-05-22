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
 * @package components\ILIAS/Test
 */
class ilLogicalAnswerComparisonExpressionInputGUI extends ilAnswerWizardInputGUI
{
    public function setValues($model_values): void
    {
        $form_values = [];
        foreach ($model_values as $model_value) {
            $form_values[] = new ASS_AnswerSimple(
                $model_value->getExpression(),
                $model_value->getPoints(),
                $model_value->getOrderIndex() - 1,
                -1,
                0
            );
        }

        if ($form_values === []) {
            $form_values[] = new ASS_AnswerSimple('', 0, 1);
        }

        parent::setValues($form_values);
    }

    public function getValues(): array
    {
        $form_values = parent::getValues();

        $model_values = [];
        foreach ($form_values as $form_value) {
            $expression = new ilAssQuestionSolutionComparisonExpression();
            $expression->setExpression($form_value->getAnswertext());
            $expression->setPoints($form_value->getPoints());
            $expression->setOrderIndex($form_value->getOrder() + 1);
            $model_values[] = $expression;
        }

        return $model_values;
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
        return 'tpl.prop_lac_expression_input.html';
    }

    public function getInput(): array
    {
        if (!$this->isRequestParamArray($this->getPostVar())) {
            return [];
        }
        return $this->getRequestParam(
            $this->getPostVar(),
            $this->refinery->kindlyTo()->dictOf(
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->float(),
                        $this->refinery->kindlyTo()->string()
                    ])
                )
            )
        );
    }
}
