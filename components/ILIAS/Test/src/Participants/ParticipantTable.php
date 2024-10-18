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

namespace ILIAS\Test\Participants;

use ILIAS\Test\Results\Data\Factory as ResultsDataFactory;
use ILIAS\Test\Results\Presentation\Settings as ResultsPresentationSettings;
use ILIAS\Language\Language;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

class ParticipantTable implements DataRetrieval
{
    private const ID = 'participant_table';
    private ?iterable $records = null;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilUIService $ui_service,
        private readonly Language $lng,
        private readonly \ilTestAccess $test_access,
        private readonly DataFactory $data_factory,
        private readonly RequestDataCollector $test_request,
        private readonly \ilTestParticipantAccessFilterFactory $participant_access_filter,
        private readonly ParticipantRepository $repository,
        private readonly ResultsDataFactory $results_data_factory,
        private readonly ResultsPresentationSettings $results_presentation_settings,
        private readonly \ilObjTest $test_object,
        private readonly ParticipantTableModalActions $table_actions
    ) {
    }

    public function execute(URLBuilder $url_builder)
    {
        $this->table_actions->execute(...$this->acquireParameters($url_builder));
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder, string $filter_url): array
    {
        $filter = $this->getFilterComponent($filter_url, $this->test_request->getRequest());
        $table = $this->getTableComponent(
            $this->test_request->getRequest(),
            $this->ui_service->filter()->getData($filter)
        );

        return [
            $filter,
            $table->withActions($this->table_actions->getEnabledActions(...$this->acquireParameters($url_builder)))
        ];
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->repository->countParticipants($this->test_object->getTestId(), $filter_data);
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $processing_time = $this->test_object->getProcessingTimeInSeconds();

        foreach ($this->getViewControlledRecords($filter_data, $range, $order) as $record) {
            $date_format = $this->data_factory->dateFormat()->withTime24($this->data_factory->dateFormat()->germanShort());

            $first_access = $record->getFirstAccess();
            $last_access = $record->getLastAccess();
            $total_duration = $record->getTotalDuration($processing_time);

            $row = [
                'name' => sprintf('%s, %s', $record->getLastname(), $record->getFirstname()),
                'login' => $record->getLogin(),
                'matriculation' => $record->getMatriculation(),
                'started_at' => $first_access !== null ? $date_format->applyTo($first_access) : '',
                'status_of_attempt' => $this->lng->txt($record->getStatusOfAttempt()),
                'id_of_attempt' => $record->getAttemptOverviewInformation()?->getExamId(),
                'last_access' => $last_access !== null ? $date_format->applyTo($last_access) : '',
                'ip_range' => $record->getClientIpTo() !== '' || $record->getClientIpFrom() !== ''
                    ? sprintf('%s - %s', $record->getClientIpFrom(), $record->getClientIpTo())
                    : '',
                'total_attempts' => $record->getAttempts(),
                'extra_time' => $record->getExtraTime() > 0 ? sprintf('%d min', $record->getExtraTime()) : '',
                'total_duration' => $total_duration > 0 ? sprintf('%d min', $total_duration / 60) : '',
                'remaining_duration' => sprintf('%d min', $record->getRemainingDuration($processing_time) / 60),
            ];

            if ($record->getActiveId() !== null
                && $this->test_access->checkResultsAccessForActiveId(
                    $record->getActiveId(),
                    $this->test_object->getTestId()
                ) || $record === null && $this->test_access->checkParticipantsResultsAccess()) {
                $row['reached_points'] = sprintf(
                    $this->lng->txt('tst_reached_points_of_max'),
                    $record->getAttemptOverviewInformation()?->getReachedPoints(),
                    $record->getAttemptOverviewInformation()?->getAvailablePoints()
                );
                $row['nr_of_answered_questions'] = sprintf(
                    $this->lng->txt('tst_answered_questions_of_total'),
                    $record->getAttemptOverviewInformation()?->getNrOfAnsweredQuestions(),
                    $record->getAttemptOverviewInformation()?->getNrOfTotalQuestions()
                );
                $row['percent_of_available_points'] = sprintf(
                    '%.2f%%',
                    $record->getAttemptOverviewInformation()?->getReachedPointsInPercent()
                );
                $row['test_passed'] = $record->getAttemptOverviewInformation()?->hasPassingMark();
                $row['mark'] = $record->getAttemptOverviewInformation()?->getMark();
            }

            yield $this->table_actions->onDataRow(
                $row_builder->buildDataRow((string) $record->getUserId(), $row),
                $record
            );
        }
    }

    private function acquireParameters($url_builder): array
    {
        return $url_builder->acquireParameters(
            [self::ID],
            ParticipantTableModalActions::ROW_ID_PARAMETER,
            ParticipantTableModalActions::ACTION_PARAMETER,
            ParticipantTableModalActions::ACTION_TYPE_PARAMETER,
        );
    }

    /**
     * @return array<string, \Closure>
     */
    private function getPostLoadFilters(): array
    {
        return [
            'solution' => fn(string $value, Participant $record) =>
                $value === 'true' ? $record->hasAnsweredQuestionsForScoredAttempt() : !$record->hasAnsweredQuestionsForScoredAttempt(),
            'status_of_attempt' => fn(string $value, Participant $record) => $record->getStatusOfAttempt() === $value,
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    private function getPostLoadOrderFields(): array
    {
        $processing_time = $this->test_object->getProcessingTimeInSeconds();

        return [
            'started_at' => static fn(Participant $a, Participant $b) => $a->getFirstAccess() <=> $b->getLastAccess(),
            'total_duration' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getTotalDuration($processing_time) <=> $b->getTotalDuration($processing_time),
            'remaining_duration' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getRemainingDuration($processing_time) <=> $b->getRemainingDuration($processing_time),
            'last_access' => static fn(Participant $a, Participant $b) => $a->getTestEndDate() <=> $b->getTestEndDate(),
            'status_of_attempt' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getStatusOfAttempt() <=> $b->getStatusOfAttempt(),
            'reached_points' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->getReachedPoints()
                <=> $b->getAttemptOverviewInformation()?->getReachedPoints(),
            'nr_of_answered_questions' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->getNrOfAnsweredQuestions()
                <=> $b->getAttemptOverviewInformation()?->getNrOfAnsweredQuestions(),
            'percent_of_available_points' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->getReachedPointsInPercent()
                <=> $b->getAttemptOverviewInformation()?->getReachedPointsInPercent(),
            'test_passed' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->hasPassingMark()
                <=> $b->getAttemptOverviewInformation()?->hasPassingMark(),
            'mark' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->getMark() <=> $b->getAttemptOverviewInformation()?->getMark()

        ];
    }

    private function getFilterComponent(string $action, ServerRequestInterface $request): FilterComponent
    {
        $filter_inputs = [];
        $is_input_initially_rendered = [];
        $field_factory = $this->ui_factory->input()->field();

        foreach ($this->getFilterFields($field_factory) as $filter_id => $filter) {
            [$filter_inputs[$filter_id], $is_input_initially_rendered[$filter_id]] = $filter;
        }

        $component = $this->ui_service->filter()->standard(
            'participant_filter',
            $action,
            $filter_inputs,
            $is_input_initially_rendered,
            true,
            true
        );

        return $request->getMethod() === 'POST' ? $component->withRequest($request) : $component;
    }

    /**
     * @param FieldFactory $field_factory
     *
     * @return array<string, FilterInput>
     */
    private function getFilterFields(FieldFactory $field_factory): array
    {
        $yes_no_all_options = [
            'true' => $this->lng->txt('yes'),
            'false' => $this->lng->txt('no')
        ];

        $solution_options = [
            'false' => $this->lng->txt('without_solution'),
            'true' => $this->lng->txt('with_solution')
        ];

        $status_of_attempt_options = [
            Participant::ATTEMPT_NOT_STARTED => $this->lng->txt(Participant::ATTEMPT_NOT_STARTED),
            Participant::ATTEMPT_RUNNING => $this->lng->txt(Participant::ATTEMPT_RUNNING),
            Participant::ATTEMPT_FINISHED => $this->lng->txt(Participant::ATTEMPT_FINISHED),
            #'finished_by_participant' => $this->lng->txt('finished_by_participant'),
            #'finished_by_administrator' => $this->lng->txt('finished_by_administrator'),
            #'finished_by_duration' => $this->lng->txt('finished_by_duration'),
            #'finished_by_cronjob' => $this->lng->txt('finished_by_cronjob')
        ];

        $filters = [
            'name' => [$field_factory->text($this->lng->txt('name')), true],
            'login' => [$field_factory->text($this->lng->txt('login')), true],
            'ip_range' => [$field_factory->text($this->lng->txt('client_ip_range')), true],
            'solution' => [$field_factory->select($this->lng->txt('solutions'), $solution_options), true],
        ];

        if ($this->test_object->getEnableProcessingTime()) {
            $filters['extra_time'] = [$field_factory->select($this->lng->txt('extratime'), $yes_no_all_options), true];
        }

        $filters['status_of_attempt'] = [
            $field_factory->select($this->lng->txt('status_of_attempt'), $status_of_attempt_options),
            true
        ];

        return $filters;
    }

    private function getTableComponent(ServerRequestInterface $request, array $filter)
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this->lng->txt('list_of_participants'),
                $this->getColumns(),
                $this
            )
            ->withId(self::ID)
            ->withRequest($request)
            ->withFilter($filter);
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        $columns = [
            'name' => $column_factory->text($this->lng->txt('name'))->withIsSortable(true),
            'login' => $column_factory->text($this->lng->txt('login'))->withIsSortable(true),
            'matriculation' => $column_factory->text($this->lng->txt('matriculation'))->withIsSortable(false)->withIsOptional(true),
            'ip_range' => $column_factory->text($this->lng->txt('client_ip_range'))->withIsOptional(true)->withIsSortable(true),
            'started_at' => $column_factory->text($this->lng->txt('tst_started'))->withIsSortable(true),
            'total_attempts' => $column_factory->number($this->lng->txt('total_attempts'))->withIsOptional(true)->withIsSortable(true),
        ];

        if ($this->test_object->getEnableProcessingTime()) {
            $columns['extra_time'] = $column_factory->text($this->lng->txt('extratime'))->withIsOptional(true);
            $columns['total_duration'] = $column_factory->text($this->lng->txt('total_duration'))->withIsOptional(true);
            $columns['remaining_duration'] = $column_factory->text($this->lng->txt('remaining_duration'))->withIsOptional(true);
        }

        $columns['status_of_attempt'] = $column_factory->text($this->lng->txt('status_of_attempt'))->withIsSortable(true);
        $columns['id_of_attempt'] = $column_factory->text($this->lng->txt('exam_id_of_attempt'))
            ->withIsSortable(false)->withIsOptional(true);

        if ($this->test_access->checkParticipantsResultsAccess()) {
            $columns['reached_points'] = $column_factory->text($this->lng->txt('tst_reached_points'))
                ->withIsSortable(true);
            $columns['nr_of_answered_questions'] = $column_factory->text($this->lng->txt('tst_answered_questions'))
                ->withIsOptional(true)
                ->withIsSortable(true);
            $columns['percent_of_available_points'] = $column_factory->text($this->lng->txt('tst_percent_solved'))
                ->withIsOptional(true)
                ->withIsSortable(true);
            $columns['test_passed'] = $column_factory->boolean(
                $this->lng->txt('tst_passed'),
                $this->ui_factory->symbol()->icon()->custom(
                    'assets/images/standard/icon_checked.svg',
                    $this->lng->txt('yes'),
                    'small'
                ),
                $this->ui_factory->symbol()->icon()->custom(
                    'assets/images/standard/icon_unchecked.svg',
                    $this->lng->txt('no'),
                    'small'
                )
            )->withIsSortable(true);
            $columns['mark'] = $column_factory->text($this->lng->txt('tst_mark'))
                ->withIsOptional(true)
                ->withIsSortable(true);
        }

        $columns['last_access'] = $column_factory->text($this->lng->txt('last_access'));

        return $columns;
    }

    private function loadRecords(?array $filter, Order $order): iterable
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $records = iterator_to_array(
            $this->repository->getParticipants(
                $this->test_object->getTestId(),
                $filter,
                null,
                $order
            )
        );

        $access_filter = $this->participant_access_filter->getManageParticipantsUserFilter($this->test_object->getRefId());
        $filtered_user_ids = $access_filter(array_map(
            fn(Participant $participant) => $participant->getUserId(),
            $records
        ));

        $this->records = array_filter(
            $records,
            fn(Participant $participant) => in_array($participant->getUserId(), $filtered_user_ids),
        );

        return $this->records;
    }


    /**
     * @return iterable<Participant>
     */
    private function getViewControlledRecords(?array $filter_data, Range $range, Order $order): iterable
    {
        return $this->limitRecords(
            $this->sortRecords(
                $this->filterRecords(
                    $records = $this->results_data_factory->addAttemptOverviewInformationToParticipants(
                        $this->results_presentation_settings,
                        $this->test_object,
                        $this->loadRecords($filter_data, $order)
                    ),
                    $filter_data
                ),
                $order
            ),
            $range
        );
    }

    private function filterRecords(iterable $records, ?array $filter_data): iterable
    {
        foreach ($records as $record) {
            if ($this->matchFilter($record, $filter_data)) {
                yield $record;
            }
        }
    }

    private function matchFilter(Participant $record, array $filter): bool
    {
        $post_load_filters = $this->getPostLoadFilters();
        $allow = true;

        foreach ($filter as $key => $value) {
            if (!$value) {
                continue;
            }

            $post_load_filter = $post_load_filters[$key] ?? fn() => true;
            $allow = $allow && $post_load_filter($value, $record);
        }

        return $allow;
    }

    private function sortRecords(iterable $records, Order $order): array
    {
        $post_load_order_fields = $this->getPostLoadOrderFields();
        $records = iterator_to_array($records);

        uasort($records, static function (Participant $a, Participant $b) use ($order, $post_load_order_fields) {
            foreach ($order->get() as $subject => $direction) {
                $post_load_order_field = $post_load_order_fields[$subject] ?? fn() => 0;
                $index = $post_load_order_field($a, $b);

                if ($index !== 0) {
                    return $direction === 'DESC' ? $index * -1 : $index;
                }
            }

            return 0;
        });

        return $records;
    }

    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }
}
