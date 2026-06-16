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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as RefineryFactory;

class ilAnswerFrequencyStatisticTableGUI extends ilTable2GUI
{
    protected ilLanguage $language;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected RefineryFactory $refinery;
    protected ilAssHtmlUserSolutionPurifier $purifier;
    protected ilCtrl $ctrl;
    protected int $questionIndex;
    protected bool $actionsColumnEnabled = false;
    protected string $additionalHtml = '';

    /**
     * @param object $a_parent_obj
     */
    public function __construct(
        ilTestCorrectionsGUI $a_parent_obj,
        string $a_parent_cmd,
        protected readonly assQuestion $question
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->language = $DIC['lng'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->purifier = ilHtmlPurifierFactory::getInstanceByType(
            'qpl_usersolution'
        );

        $this->setId('tstAnswerStatistic');
        $this->setPrefix('tstAnswerStatistic');
        $this->setTitle($this->language->txt('tst_corrections_answers_tbl'));

        $this->setRowTemplate('tpl.tst_corrections_answer_row.html', 'components/ILIAS/Test');

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context = '');

        $this->setDefaultOrderDirection('asc');
        $this->setDefaultOrderField('answer');
    }

    public function isActionsColumnEnabled(): bool
    {
        return $this->actionsColumnEnabled;
    }

    public function setActionsColumnEnabled(bool $actionsColumnEnabled): void
    {
        $this->actionsColumnEnabled = $actionsColumnEnabled;
    }

    public function getAdditionalHtml(): string
    {
        return $this->additionalHtml;
    }

    public function setAdditionalHtml(string $additional_html): void
    {
        $this->additionalHtml = $additional_html;
    }

    public function addAdditionalHtml(string $additional_html): void
    {
        $this->additionalHtml .= $additional_html;
    }

    public function getQuestionIndex(): int
    {
        return $this->questionIndex;
    }

    public function setQuestionIndex(int $questionIndex): void
    {
        $this->questionIndex = $questionIndex;
    }

    public function initColumns(): void
    {
        $this->addColumn($this->language->txt('tst_corr_answ_stat_tbl_header_answer'), '');
        $this->addColumn($this->language->txt('tst_corr_answ_stat_tbl_header_frequency'), '');

        foreach ($this->getData() as $row) {
            if (isset($row['addable'])) {
                $this->setActionsColumnEnabled(true);
                $this->addColumn('');
                break;
            }
        }
    }

    public function fillRow(array $a_set): void
    {
        $a_set['answer'] = $this->purifier->purify($a_set['answer']);

        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', $a_set['answer']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $a_set['frequency']);
        $this->tpl->parseCurrentBlock();

        if ($this->isActionsColumnEnabled()) {
            if (isset($a_set['addable'])) {
                $this->tpl->setCurrentBlock('actions');
                $this->tpl->setVariable('ACTIONS', $this->buildAddAnswerAction($a_set));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock('actions');
                $this->tpl->touchBlock('actions');
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    protected function buildAddAnswerAction($data): string
    {
        $data['question_id'] = $this->question->getId();
        $data['question_index'] = $this->getQuestionIndex();

        $modal = (new ilAddAnswerFormBuilder(
            $this->ui_factory,
            $this->refinery,
            $this->language,
            $this->ctrl
        ))->buildAddAnswerModal($this->question->getTitleForHTMLOutput(), $data);

        $show_modal_button = $this->ui_factory->button()->standard(
            $this->language->txt('tst_corr_add_as_answer_btn'),
            $modal->getShowSignal()
        );

        $this->addAdditionalHtml($this->ui_renderer->render($modal));

        return $this->ui_renderer->render($show_modal_button);
    }
}
