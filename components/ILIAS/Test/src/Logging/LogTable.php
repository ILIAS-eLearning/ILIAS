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

namespace ILIAS\Test\Logging;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class LogTable implements Table\DataRetrieval
{
    public function __construct(
        private readonly TestLoggingRepository $logging_repository,
        private readonly TestLogger $logger,
        private readonly UIFactory $ui_factory,
        private readonly DataFactory $data_factory,
        private readonly \ilLanguage $lng,
        private readonly URLBuilder $url_builder,
        private readonly URLBuilderToken $action_parameter_token,
        private readonly URLBuilderToken $row_id_token,
        private readonly int $ref_id
    ) {
    }

    public function getTable(): Table\Data
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('history'),
            $this->getColums(),
            $this
        )->withActions($this->getActions());
    }

    /**
     * Filters should be part of the Table; for now, since they are not fully
     * integrated, they are rendered and applied seperately
     */
    public function getFilter(\ilUIService $ui_service): Filter
    {
        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'from' => $field_factory->text($this->lng->txt('from')),
            'until' => $field_factory->text($this->lng->txt('until'))
        ];
        if ($this->ref_id === null) {
            $filter_inputs['test_title'] = $field_factory->text($this->lng->txt('test_title'));
        }

        $filter_inputs += [
            'author' => $field_factory->text($this->lng->txt('author')),
            'participant' => $field_factory->text($this->lng->txt('participant')),
            'ip' => $field_factory->text($this->lng->txt('ip')),
            'question_title' => $field_factory->text($this->lng->txt('question_title')),
            'log_entry_type' => $field_factory->select(
                $this->lng->txt('log_entry_type'),
                $this->buildLogEntryTypesOptionsForFilter()
            ),
            'interaction_type' => $field_factory->select(
                $this->lng->txt('interaction_type'),
                $this->buildInteractionTypesOptionsForFilter()
            ),
        ];

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $ui_service->filter()->standard(
            "question_table_filter_id",
            $this->url_builder->buildURI()->__toString(),
            $filter_inputs,
            $active,
            true,
            true
        );
        return $filter;
    }


    public function getColums(): array
    {
        $f = $this->ui_factory->table()->column();
        $df = $this->data_factory->dateFormat();
        $date_format = $df->withTime24($this->data_factory->dateFormat()->germanShort());

        return  [
            'date_and_time' => $f->date($this->lng->txt('date_and_time'), $date_format),
            'corresponding_test' => $f->text($this->lng->txt('test'))->withIsOptional(true, true),
            'author' => $f->text($this->lng->txt('author'))->withIsOptional(true, true),
            'participant' => $f->text($this->lng->txt('participant'))->withIsOptional(true, true),
            'ip' => $f->text($this->lng->txt('ip'))->withIsOptional(true, true),
            'question' => $f->text($this->lng->txt('question'))->withIsOptional(true, true),
            'log_entry_type' => $f->text($this->lng->txt('log_entry_type'))->withIsOptional(true, true),
            'interaction_type' => $f->text($this->lng->txt('interaction_type'))->withIsOptional(true, true)
        ];
    }

    public function getRows(
        Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->logging_repository->getLogs(
            $range,
            $order,
            $filter_data,
            $this->ref_id
        ) as $interaction) {
            $interaction->getLogEntryAsDataTableRow();
            $row_id = (string)$record['question_id'];
            $record['created'] = (new \DateTimeImmutable())->setTimestamp($record['created']);
            $record['tstamp'] = (new \DateTimeImmutable())->setTimestamp($record['tstamp']);
            $lifecycle = \ilAssQuestionLifecycle::getInstance($record['lifecycle']);
            $record['lifecycle'] = $lifecycle->getTranslation($this->lng);

            $title = $record['title'];
            $to_question = $this->url_builder
                ->withParameter($this->action_parameter_token, 'preview')
                ->withParameter($this->row_id_token, $row_id)
                ->buildURI()->__toString();
            if (!(bool) $record['complete']) {
                $title .= ' (' . $this->lng->txt('warning_question_not_complete') . ')';
            }
            $record['title'] = $this->ui_factory->link()->standard($title, $to_question);

            $record['taxonomies'] = implode('', $taxonomies);

            yield $row_builder->buildDataRow($row_id, $record)
                ->withDisabledAction('move', $no_write_access)
                ->withDisabledAction('copy', $no_write_access)
                ->withDisabledAction('delete', $no_write_access)
                ->withDisabledAction('feedback', $no_write_access)
                ->withDisabledAction('hints', $no_write_access)
            ;
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return 0;
        $this->setParentObjId($this->parent_obj_id);
        $this->load();
        return count($this->getQuestionDataArray());
    }

    protected function getActions(): array
    {
        $af = $this->ui_factory->table()->action();
        return [
            $af->single(
                $this->lng->txt('show_additional_information'),
                $this->url_builder,
                $this->row_id_token
            )->withAsync(),
            $af->standard(
                $this->lng->txt('export_as_csv'),
                $this->url_builder,
                $this->row_id_token
            ),
            $af->standard(
                $this->lng->txt('delete'),
                $this->url_builder,
                $this->row_id_token
            )->withAsync()
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildLogEntryTypesOptionsForFilter(): array
    {
        $log_entry_types = $this->logger->getLogEntryTypes();
        $log_entry_options = [];
        foreach ($log_entry_types as $log_entry_type) {
            $log_entry_options [$log_entry_type] = $log_entry_type;
        }
        return $log_entry_options;
    }

    /**
     * @return array<string, string>
     */
    private function buildInteractionTypesOptionsForFilter(): array
    {
        $interaction_types = $this->logger->getInteractionTypes();
        $interaction_options = [];
        foreach ($interaction_types as $interaction_type) {
            $interaction_options[$interaction_type] = $this->lng->txt($interaction_type);
        }

        return $interaction_options;
    }

    protected function postOrder(array $list, \ILIAS\Data\Order $order): array
    {
        [$aspect, $direction] = $order->join('', function ($i, $k, $v) {
            return [$k, $v];
        });
        usort($list, static function (array $a, array $b) use ($aspect): int {
            if (is_numeric($a[$aspect]) || is_bool($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }
            if (is_array($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }

            $aspect_a = '';
            $aspect_b = '';
            if ($a[$aspect] !== null) {
                $aspect_a = $a[$aspect];
            }
            if ($b[$aspect] !== null) {
                $aspect_b = $b[$aspect];
            }

            return strcmp($aspect_a, $aspect_b);
        });

        if ($direction === $order::DESC) {
            $list = array_reverse($list);
        }
        return $list;
    }
}
