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

declare(strict_types=1);

namespace ILIAS\Test\Questions\Presentation;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ilTestPlayerCommands;
use ilTestSubmissionReviewGUI;

class QuestionsOfAttemptTable implements DataRetrieval
{
    public function __construct(
        protected readonly \ilLanguage $lng,
        protected readonly \ilCtrlInterface $ctrl,
        protected readonly UIFactory $ui_factory,
        protected readonly DataFactory $data_factory,
        protected readonly GlobalHttpState $http,
        protected readonly \ilTestPlayerAbstractGUI $parent_gui,
        protected readonly \ilObjTest $test,
        protected readonly array $data
    ) {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->getData($range, $order) as $question) {
            $title = $this->ui_factory->link()->standard($question['title'], $this->createShowQuestionLink($question['sequence']));
            $record = [
                'order' => (int) $question['order'],
                'title' => $title->withDisabled($question['disabled']),
                'description' => $question['description'],
                'points' => $question['points'],
                'postponed' => (bool) $question['postponed'],
                'answered' => (bool) $question['isAnswered'],
                'marked' => (bool) $question['marked'],
            ];
            yield $row_builder->buildDataRow((string) $question['order'], $record);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        //ignore filter bc table is not filterable
        return count($this->data);
    }

    /**
     * @return array<Component>
     */
    public function buildComponents(): array
    {
        $components = [
            $this->ui_factory->button()->standard(
                $this->lng->txt('tst_resume_test'),
                $this->ctrl->getLinkTarget($this->parent_gui, ilTestPlayerCommands::SHOW_QUESTION)
            )
        ];

        $button_text = $this->lng->txt('finish_test');
        // Examview enabled & !reviewed & requires_confirmation? test_submission_overview (review gui)
        if ($this->parent_gui->getObject()->getMainSettings()->getFinishingSettings()->getShowAnswerOverview()) {
            $components[] = $this->ui_factory->button()->standard(
                $button_text,
                $this->ctrl->getLinkTargetByClass(ilTestSubmissionReviewGUI::class, 'show')
            );
        } else {
            $finish_test_modal = $this->parent_gui->buildFinishTestModal();
            $components[] = $this->ui_factory->button()->standard($button_text, '')
                ->withOnClick($finish_test_modal->getShowSignal());
            $components[] = $finish_test_modal;
        }

        $components[] = $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('question_summary'),
            $this->getColumns(),
        )
            ->withRequest($this->http->request())
            ->withId('listofquestions');

        return $components;
    }

    protected function getData(Range $range, Order $order): array
    {
        // ignore order bc table is not sortable
        return array_slice($this->data, $range->getStart(), $range->getLength());
    }

    protected function createShowQuestionLink(int $sequence): string
    {
        $this->ctrl->setParameter($this->parent_gui, 'sequence', $sequence);
        $this->ctrl->setParameter($this->parent_gui, 'pmode', '');
        return $this->ctrl->getLinkTarget($this->parent_gui, ilTestPlayerCommands::SHOW_QUESTION);
    }

    /**
     * @return array<Column>
     */
    protected function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $icon_factory = $this->ui_factory->symbol()->icon();
        $icon_checked = $icon_factory->custom('assets/images/standard/icon_checked.svg', $this->lng->txt('yes'));
        $icon_unchecked = $icon_factory->custom('assets/images/standard/icon_unchecked.svg', $this->lng->txt('no'));
        $icon_marked = $icon_factory->custom('assets/images/object/marked.svg', $this->lng->txt('tst_question_marked'));

        $columns = [
            'order' => $column_factory->number($this->lng->txt('tst_qst_order')),
            'title' => $column_factory->link($this->lng->txt('tst_question')),
            'description' => $column_factory->text($this->lng->txt('description')),
            'postponed' => $column_factory->boolean(ucfirst($this->lng->txt('postponed')), $this->lng->txt('yes'), ''),
            'points' => $column_factory->number($this->lng->txt('tst_maximum_points'))->withUnit($this->lng->txt('points_short')),
            'answered' => $column_factory->boolean($this->lng->txt('answered'), $icon_checked, $icon_unchecked),
            'marked' => $column_factory->boolean($this->lng->txt('tst_question_marker'), $icon_marked, ''),
        ];

        $optional_columns = [
            'description' => $this->isShowDescriptionEnabled(),
            'postponed' => $this->isPostponingEnabled(),
            'points' => $this->isShowPointsEnabled(),
            'marked' => $this->isShowMarkerEnabled(),
        ];

        $list = [];
        foreach ($columns as $key => $column) {
            if (isset($optional_columns[$key]) && !$optional_columns[$key]) {
                continue;
            }
            $list[$key] = $column->withIsOptional(false, true)->withIsSortable(false);
        }
        return $list;
    }

    protected function isShowDescriptionEnabled(): bool
    {
        return $this->test->getListOfQuestionsDescription();
    }

    protected function isPostponingEnabled(): bool
    {
        return $this->test->isPostponingEnabled();
    }

    protected function isShowPointsEnabled(): bool
    {
        return !$this->test->getTitleOutput();
    }

    protected function isShowMarkerEnabled(): bool
    {
        return $this->test->getShowMarker();
    }

}
