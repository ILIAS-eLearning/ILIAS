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
 * @author  Björn Heyser <bheyser@databay.de>
 * @package Modules/Test
 */
class ilAssLacLegendGUI
{
    /** @var ilLanguage */
    protected $lng;
    /** @var ilGlobalTemplateInterface */
    protected $pageTemplate;
    /** @var iQuestionCondition*/
    private $questionOBJ;
    /** @var array<string, string[]> */
    private $examplesByQuestionType = [
        'assQuestion' => ['PercentageResultExpression', 'EmptyAnswerExpression'],
        'assSingleChoice' => ['NumberOfResultExpression'],
        'assMultipleChoice' => ['NumberOfResultExpression', 'ExclusiveResultExpression'],
        'assErrorText' => ['NumberOfResultExpression', 'ExclusiveResultExpression'],
        'assImagemapQuestion' => ['NumberOfResultExpression', 'ExclusiveResultExpression'],
        'assNumeric' => ['NumericResultExpression'],
        'assOrderingQuestion' => ['OrderingResultExpression'],
        'assOrderingHorizontal' => ['OrderingResultExpression'],
        'assMatchingQuestion' => ['MatchingResultExpression'],
        'assTextSubset' => ['StringResultExpression'],
        'assFormulaQuestion' => ['NumericResultExpression'],
        'assClozeTest' => [
            'StringResultExpression_1', 'StringResultExpression_2',
            'NumberOfResultExpression', 'NumericResultExpression'
        ],
    ];
    /** @var \ILIAS\UI\Factory */
    private $uiFactory;

    /**
     * ilAssLacLegendGUI constructor.
     * @param ilGlobalTemplateInterface $pageTemplate
     * @param ilLanguage $lng
     * @param \ILIAS\UI\Factory $uiFactory
     */
    public function __construct(
        ilGlobalTemplateInterface $pageTemplate,
        ilLanguage $lng,
        \ILIAS\UI\Factory $uiFactory
    ) {
        $this->pageTemplate = $pageTemplate;
        $this->lng = $lng;
        $this->uiFactory = $uiFactory;
        $this->questionOBJ = null;
    }

    /**
     * @return assQuestion|null
     */
    public function getQuestionOBJ(): ?iQuestionCondition
    {
        return $this->questionOBJ;
    }

    public function setQuestionOBJ(assQuestion $questionOBJ): void
    {
        $this->questionOBJ = $questionOBJ;
    }

    /**
     * @return \ILIAS\UI\Component\Modal\Modal
     */
    public function get(): \ILIAS\UI\Component\Modal\Modal
    {
        $this->pageTemplate->addCss('Modules/TestQuestionPool/templates/default/lac_legend.css');

        $tpl = $this->getTemplate();

        $this->renderCommonLegendPart($tpl);
        $this->renderQuestSpecificLegendPart($tpl);
        $this->renderQuestSpecificExamples($tpl);

        return $this->uiFactory->modal()->lightbox([
            $this->uiFactory->modal()->lightboxTextPage(
                $tpl->get(),
                $this->lng->txt('qpl_skill_point_eval_by_solution_compare')
            ),
        ]);
    }

    /**
     * @return ilTemplate
     * @throws ilTemplateException
     */
    protected function getTemplate(): ilTemplate
    {
        return new ilTemplate(
            'tpl.qpl_logical_answer_compare_legend.html',
            true,
            true,
            'Modules/TestQuestionPool'
        );
    }

    /**
     * @param ilTemplate $tpl
     */
    private function renderCommonLegendPart(ilTemplate $tpl): void
    {
        $tpl->setVariable(
            'COMMON_ELEMENTS_HEADER',
            $this->lng->txt('qpl_lac_legend_header_common')
        );

        foreach ($this->getCommonElements() as $element => $description) {
            $tpl->setCurrentBlock('common_elements');
            $tpl->setVariable('CE_ELEMENT', $element);
            $tpl->setVariable('CE_DESCRIPTION', $description);
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @param ilTemplate $tpl
     */
    private function renderQuestSpecificLegendPart(ilTemplate $tpl): void
    {
        $tpl->setVariable(
            'QUEST_SPECIFIC_ELEMENTS_HEADER',
            $this->lng->txt('qpl_lac_legend_header_quest_specific')
        );

        foreach ($this->getQuestionTypeSpecificExpressions() as $expression => $description) {
            $tpl->setCurrentBlock('quest_specific_elements');
            $tpl->setVariable('QSE_ELEMENT', $expression);
            $tpl->setVariable('QSE_DESCRIPTION', $this->lng->txt($description));
            $tpl->setVariable('QSE_OPERATORS_TXT', $this->lng->txt('qpl_lac_legend_label_operators'));
            $tpl->setVariable('QSE_OPERATORS', implode(', ', $this->getQuestionOBJ()->getOperators($expression)));
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @param ilTemplate $tpl
     */
    private function renderQuestSpecificExamples(ilTemplate $tpl): void
    {
        $tpl->setVariable(
            'QUEST_SPECIFIC_EXAMPLES_HEADER',
            $this->lng->txt('lacex_example_header')
        );

        $questionTypes = [
            'assQuestion', $this->getQuestionOBJ()->getQuestionType()
        ];

        foreach ($questionTypes as $questionType) {
            $examples = $this->getExpressionTypeExamplesByQuestionType($questionType);
            $this->renderExamples($tpl, $examples, $questionType);
        }
    }

    /**
     * @param string $questionType
     * @param string $exampleCode
     * @return string[]
     */
    private function buildLangVarsByExampleCode(string $questionType, string $exampleCode): array
    {
        $langVar = 'lacex_' . $questionType . '_' . $exampleCode;

        return [$langVar . '_e', $langVar . '_d'];
    }

    /**
     * @param ilTemplate $tpl
     * @param string $langVarE
     * @param string $langVarD
     */
    private function renderExample(ilTemplate $tpl, string $langVarE, string $langVarD): void
    {
        $tpl->setCurrentBlock('quest_specific_examples');
        $tpl->setVariable('QSEX_ELEMENT', $this->lng->txt($langVarE));
        $tpl->setVariable('QSEX_DESCRIPTION', $this->lng->txt($langVarD));
        $tpl->parseCurrentBlock();
    }

    /**
     * @return array<string, string>
     */
    private function getQuestionTypeSpecificExpressions(): array
    {
        $availableExpressionTypes = $this->getAvailableExpressionTypes();

        $expressionTypes = [];

        foreach ($this->getQuestionOBJ()->getExpressionTypes() as $expressionType) {
            $expressionTypes[$expressionType] = $availableExpressionTypes[$expressionType];
        }

        return $expressionTypes;
    }

    /**
     * @return array<string, string>
     */
    private function getCommonElements(): array
    {
        return [
            '&' => $this->lng->txt('qpl_lac_desc_logical_and'),
            '|' => $this->lng->txt('qpl_lac_desc_logical_or'),
            '!' => $this->lng->txt('qpl_lac_desc_negation'),
            '()' => $this->lng->txt('qpl_lac_desc_brackets'),
            //'Qn' => $this->lng->txt('qpl_lac_desc_res_of_quest_n'),
            //'Qn[m]' => $this->lng->txt('qpl_lac_desc_res_of_answ_m_of_quest_n'),
            'R' => $this->lng->txt('qpl_lac_desc_res_of_cur_quest'),
            'R[m]' => $this->lng->txt('qpl_lac_desc_res_of_answ_m_of_cur_quest')
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableExpressionTypes(): array
    {
        return [
            iQuestionCondition::PercentageResultExpression => 'qpl_lac_desc_compare_with_quest_res',
            iQuestionCondition::NumericResultExpression => 'qpl_lac_desc_compare_with_number',
            iQuestionCondition::StringResultExpression => 'qpl_lac_desc_compare_with_text',
            iQuestionCondition::MatchingResultExpression => 'qpl_lac_desc_compare_with_assignment',
            iQuestionCondition::OrderingResultExpression => 'qpl_lac_desc_compare_with_sequence',
            iQuestionCondition::NumberOfResultExpression => 'qpl_lac_desc_compare_with_answer_n',
            iQuestionCondition::ExclusiveResultExpression => 'qpl_lac_desc_compare_with_exact_sequence',
            iQuestionCondition::EmptyAnswerExpression => 'qpl_lac_desc_compare_answer_exist'
        ];
    }

    /**
     * @param string$questionType
     * @return string[]
     */
    private function getExpressionTypeExamplesByQuestionType(string $questionType): array
    {
        if (!isset($this->examplesByQuestionType[$questionType])) {
            return [];
        }

        return $this->examplesByQuestionType[$questionType];
    }

    /**
     * @param ilTemplate $tpl
     * @param string[] $examples
     * @param string $questionType
     */
    private function renderExamples(ilTemplate $tpl, array $examples, string $questionType): void
    {
        foreach ($examples as $exampleCode) {
            list($langVarE, $langVarD) = $this->buildLangVarsByExampleCode($questionType, $exampleCode);
            $this->renderExample($tpl, $langVarE, $langVarD);
        }
    }
}
