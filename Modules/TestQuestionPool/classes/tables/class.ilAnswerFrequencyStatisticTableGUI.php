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

use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory as RefineryFactory;

/**
 * Class ilAnswerFrequencyStatisticTableGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @author    Stephan Kergomard <office@kergomard.ch>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAnswerFrequencyStatisticTableGUI extends ilTable2GUI
{
    protected ilLanguage $language;
    protected UIServices $ui;
    protected RefineryFactory $refinery;
    protected ilCtrl $ctrl;
    protected assQuestion $question;
    protected int $questionIndex;
    protected bool $actionsColumnEnabled = false;
    protected string $additionalHtml = '';

    /**
     * ilAnswerFrequencyStatisticTableGUI constructor.
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $question
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $question = "")
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->language = $DIC->language();
        $this->ui = $DIC->ui();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->question = $question;

        $this->setId('tstAnswerStatistic');
        $this->setPrefix('tstAnswerStatistic');
        $this->setTitle($this->language->txt('tst_corrections_answers_tbl'));

        $this->setRowTemplate('tpl.tst_corrections_answer_row.html', 'Modules/Test');

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context = '');

        $this->setDefaultOrderDirection('asc');
        $this->setDefaultOrderField('answer');
    }

    /**
     * @return bool
     */
    public function isActionsColumnEnabled(): bool
    {
        return $this->actionsColumnEnabled;
    }

    /**
     * @param bool $actionsColumnEnabled
     */
    public function setActionsColumnEnabled(bool $actionsColumnEnabled): void
    {
        $this->actionsColumnEnabled = $actionsColumnEnabled;
    }

    /**
     * @return string
     */
    public function getAdditionalHtml(): string
    {
        return $this->additionalHtml;
    }

    /**
     * @param string $additionalHtml
     */
    public function setAdditionalHtml(string $additionalHtml): void
    {
        $this->additionalHtml = $additionalHtml;
    }

    /**
     * @param string $additionalHtml
     */
    public function addAdditionalHtml(string $additionalHtml): void
    {
        $this->additionalHtml .= $additionalHtml;
    }

    /**
     * @return int
     */
    public function getQuestionIndex(): int
    {
        return $this->questionIndex;
    }

    /**
     * @param int $questionIndex
     */
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
                $this->addColumn('', '', '1%');
                break;
            }
        }
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', ilLegacyFormElementsUtil::prepareFormOutput($a_set['answer']));
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
        $ui_factory = $this->ui->factory();
        $ui_renderer = $this->ui->renderer();

        $answer_form_builder = new ilAddAnswerFormBuilder($this->parent_obj, $ui_factory, $this->refinery, $this->language, $this->ctrl);

        $data['question_id'] = $this->question->getId();
        $data['question_index'] = $this->getQuestionIndex();

        $form = $answer_form_builder->buildAddAnswerForm($data);
        $modal = $ui_factory->modal()->roundtrip('titel', $form);

        $show_modal_button = $ui_factory->button()->standard(
            $this->language->txt('tst_corr_add_as_answer_btn'),
            $modal->getShowSignal()
        );

        $this->addAdditionalHtml($ui_renderer->render($modal));

        return $ui_renderer->render($show_modal_button);
    }
}
