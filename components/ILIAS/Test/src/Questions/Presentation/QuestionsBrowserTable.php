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

use ILIAS\Taxonomy\DomainService as TaxonomyService;
use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Link\Standard;
use ILIAS\UI\Component\Table\Action\Standard as TableAction;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ilTestQuestionBrowserTableGUI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data;

class QuestionsBrowserTable implements DataRetrieval
{
    public const ACTION_INSERT = 'insert';

    public function __construct(
        private readonly string $table_id,
        private readonly \ilObjUser $current_user,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrl $ctrl,
        private readonly DataFactory $data_factory,
        private readonly \ilAssQuestionList $question_list,
        private readonly TaxonomyService $taxonomy,
        private readonly string $parent_title
    ) {
    }

    public function getComponent(ServerRequestInterface $request, ?array $filter): Data
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('list_of_questions'),
            $this->getColumns(),
        )->withId($this->table_id)
        ->withActions($this->getActions())
        ->withRequest($request)
        ->withFilter($filter);
    }

    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $icon_factory = $this->ui_factory->symbol()->icon();
        $iconYes = $icon_factory->custom('assets/images/standard/icon_checked.svg', 'yes');
        $iconNo = $icon_factory->custom('assets/images/standard/icon_unchecked.svg', 'no');

        $columns = [
            'title' => $column_factory->text(
                $this->lng->txt('tst_question_title')
            )->withIsOptional(false, true),
            'description' => $column_factory->text(
                $this->lng->txt('description')
            )->withIsOptional(true, true),
            'question_type' => $column_factory->text(
                $this->lng->txt('tst_question_type')
            )->withIsOptional(false, true),
            'points' => $column_factory->number(
                $this->lng->txt('points')
            )->withIsOptional(false, true),
            'author' => $column_factory->text(
                $this->lng->txt('author')
            )->withIsOptional(true, false),
            'lifecycle' => $column_factory->text(
                $this->lng->txt('qst_lifecycle')
            )->withIsOptional(true, false),
            'parent_title' => $column_factory->link(
                $this->lng->txt($this->parent_title)
            )->withIsOptional(false, true),
            'taxonomies' => $column_factory->text(
                $this->lng->txt('qpl_settings_subtab_taxonomies')
            )->withIsOptional(false, true),
            'feedback' => $column_factory->boolean(
                $this->lng->txt('tst_feedback'),
                $iconYes,
                $iconNo
            )->withIsOptional(true, false),
            'created' => $column_factory->date(
                $this->lng->txt('created'),
                $this->current_user->getDateTimeFormat()
            )->withIsOptional(true, false),
            'tstamp' => $column_factory->date(
                $this->lng->txt('updated'),
                $this->current_user->getDateTimeFormat()
            )->withIsOptional(true, false)
        ];

        return array_map(static fn(Column $column): Column => $column->withIsSortable(true), $columns);
    }

    public function getActions(): array
    {
        return [self::ACTION_INSERT => $this->getInsertAction()];
    }

    private function getInsertAction(): TableAction
    {
        $url_builder = new URLBuilder($this->data_factory->uri(
            ILIAS_HTTP_PATH . "/{$this->ctrl->getLinkTargetByClass(
                ilTestQuestionBrowserTableGUI::class,
                ilTestQuestionBrowserTableGUI::CMD_INSERT_QUESTIONS
            )}"
        ));

        [$url_builder, $row_id_token] = $url_builder->acquireParameters(['qlist'], 'q_id');

        return $this->ui_factory->table()->action()->standard(
            $this->lng->txt('tst_insert_in_test'),
            $url_builder,
            $row_id_token
        );
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        $timezone = new \DateTimeZone($this->current_user->getTimeZone());
        foreach ($this->loadRecords($filter_data ?? [], $order, $range) as $record) {
            $question_id = $record['question_id'];

            $record['question_type'] = $record['question_type'];
            $record['complete'] = (bool) $record['complete'];
            $record['lifecycle'] = \ilAssQuestionLifecycle::getInstance($record['lifecycle'])->getTranslation($this->lng) ?? '';

            $record['created'] = (new \DateTimeImmutable("@{$record['created']}"))->setTimezone($timezone);
            $record['tstamp'] = (new \DateTimeImmutable("@{$record['tstamp']}"))->setTimezone($timezone);
            $record['taxonomies'] = $this->resolveTaxonomiesRowData($record['obj_fi'], $question_id);
            $record['parent_title'] = $this->getParentObjectLink($record);

            yield $row_builder->buildDataRow((string) $question_id, $record);
        }
    }

    private function getParentObjectLink(array $record): Standard
    {
        [$parent_class, $parent_command] = match($record['parent_type']) {
            'qpl' => [\ilObjQuestionPoolGUI::class, \ilObjQuestionPoolGUI::DEFAULT_CMD],
            'tst' => [\ilObjTestGUI::class, \ilObjTestGUI::SHOW_QUESTIONS_CMD],
            default => throw new \RuntimeException("Unsupported parent type {$record['parent_type']}"),
        };

        $this->ctrl->setParameterByClass($parent_class, 'ref_id', $record['parent_ref_id']);
        $link = $this->ui_factory->link()->standard(
            $record['parent_title'],
            $this->ctrl->getLinkTargetByClass($parent_class, $parent_command),
        );
        $this->ctrl->setParameterByClass($parent_class, 'ref_id', null);

        return $link;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): int {
        $filter_data ??= [];
        $this->addFiltersToQuestionList($filter_data);
        return $this->question_list->getTotalRowCount($additional_viewcontrol_data, $filter_data, $additional_parameters);
    }

    public function loadRecords(array $filters = [], ?Order $order = null, ?Range $range = null): array
    {
        $this->addFiltersToQuestionList($filters);

        $this->question_list->setOrder($order);
        $this->question_list->setRange($range);
        $this->question_list->load();

        return $this->question_list->getQuestionDataArray();
    }

    private function addFiltersToQuestionList(array $filters): void
    {
        foreach (array_filter($filters) as $key => $filter) {
            if ($key === 'commented') {
                $this->question_list->setCommentFilter((int) $filter);
                continue;
            }

            $this->question_list->addFieldFilter($key, $filter);
        }
    }

    private function resolveTaxonomiesRowData(int $obj_fi, int $questionId): string
    {
        $available_taxonomy_ids = $this->taxonomy->getUsageOfObject($obj_fi);
        $data = $this->loadTaxonomyAssignmentData($obj_fi, $questionId, $available_taxonomy_ids);

        $taxonomies = [];

        foreach ($data as $taxonomyId => $taxData) {
            $taxonomies[] = \ilObject::_lookupTitle($taxonomyId);
            $taxonomies[] = $this->ui_renderer->render(
                $this->ui_factory->listing()->unordered(
                    array_map(static function ($node) {
                        return \ilTaxonomyNode::_lookupTitle($node['node_id']);
                    }, $taxData)
                )
            );
        }

        return implode('', $taxonomies);
    }

    private function loadTaxonomyAssignmentData(int $parentObjId, int $questionId, array $available_taxonomy_ids): array
    {
        $taxonomyAssignmentData = [];

        foreach ($available_taxonomy_ids as $taxId) {
            $taxTree = new \ilTaxonomyTree($taxId);
            $assignments = (new \ilTaxNodeAssignment(
                'qpl',
                $parentObjId,
                'quest',
                $taxId
            ))->getAssignmentsOfItem($questionId);

            foreach ($assignments as $assData) {
                $taxId = $assData['tax_id'];
                if (!isset($taxonomyAssignmentData[$taxId])) {
                    $taxonomyAssignmentData[$taxId] = [];
                }

                $nodeId = $assData['node_id'];
                $assData['node_lft'] = $taxTree->getNodeData($nodeId)['lft'];
                $taxonomyAssignmentData[$taxId][$nodeId] = $assData;
            }
        }

        return $taxonomyAssignmentData;
    }
}
