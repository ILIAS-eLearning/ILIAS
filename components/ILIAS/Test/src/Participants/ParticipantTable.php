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
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Results\Data\StatusOfAttempt;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;

class ParticipantTable implements DataRetrieval
{
    private const ID = 'pt';
    private ?iterable $records = null;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilUIService $ui_service,
        private readonly Language $lng,
        private readonly \ilTestAccess $test_access,
        private readonly RequestDataCollector $test_request,
        private readonly \ilTestParticipantAccessFilterFactory $participant_access_filter,
        private readonly ParticipantRepository $repository,
        private readonly ResultsDataFactory $results_data_factory,
        private readonly ResultsPresentationSettings $results_presentation_settings,
        private readonly \ilObjUser $current_user,
        private readonly \ilObjTest $test_object,
        private readonly ParticipantTableActions $table_actions
    ) {
    }

    public function execute(URLBuilder $url_builder): ?Modal
    {
        return $this->table_actions->execute(...$this->acquireParameters($url_builder));
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
        $reset_time_on_new_attempt = $this->test_object->getResetProcessingTime();

        $current_user_timezone = new \DateTimeZone($this->current_user->getTimeZone());

        /** @var \ILIAS\Test\Participants\Participant $record */
        foreach ($this->getViewControlledRecords($filter_data, $range, $order) as $record) {
            $total_duration = $record->getTotalDuration($processing_time);
            $status_of_attempt = $record->getAttemptOverviewInformation()?->getStatusOfAttempt() ?? StatusOfAttempt::NOT_YET_STARTED;

            $row = [
                'name' => $this->test_object->buildName($record->getUserId(), $record->getLastname(), $record->getFirstname()),
                'login' => $record->getLogin(),
                'matriculation' => $record->getMatriculation(),
                'status_of_attempt' => $this->lng->txt($status_of_attempt->value),
                'id_of_attempt' => $record->getAttemptOverviewInformation()?->getExamId(),
                'ip_range' => $record->getClientIpTo() !== '' || $record->getClientIpFrom() !== ''
                    ? sprintf('%s - %s', $record->getClientIpFrom(), $record->getClientIpTo())
                    : '',
                'total_attempts' => $record->getAttemptOverviewInformation()?->getNrOfAttempts() ?? 0,
                'extra_time' => $record->getExtraTime() > 0 ? sprintf('%d min', $record->getExtraTime()) : '',
                'total_duration' => $total_duration > 0 ? sprintf('%d min', $total_duration / 60) : '',
                'remaining_duration' => sprintf('%d min', $record->getRemainingDuration($processing_time, $reset_time_on_new_attempt) / 60),
            ];

            $first_access = $record->getAttemptOverviewInformation()?->getStartedDate();
            if ($first_access !== null) {
                $row['attempt_started_at'] = $first_access->setTimezone($current_user_timezone);
            }

            $last_access = $record->getLastAccess();
            if ($last_access !== null) {
                $row['last_access'] = $last_access->setTimezone($current_user_timezone);
            }
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
                $row['percent_of_available_points'] = $record->getAttemptOverviewInformation()?->getReachedPointsInPercent();
            }

            if ($status_of_attempt->isFinished()) {
                $row['test_passed'] = $record->getAttemptOverviewInformation()?->hasPassingMark() ?? false;
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
            ParticipantTableActions::ROW_ID_PARAMETER,
            ParticipantTableActions::ACTION_PARAMETER,
            ParticipantTableActions::ACTION_TYPE_PARAMETER
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
            'status_of_attempt' => fn(string $value, Participant $record) =>
                ($value === StatusOfAttempt::NOT_YET_STARTED->value && $record->getAttemptOverviewInformation()?->getStatusOfAttempt() === null) ||
                $value === $record->getAttemptOverviewInformation()?->getStatusOfAttempt()->value,
            'test_passed' => fn(string $value, Participant $record) => $value === 'true'
                ? $record->getAttemptOverviewInformation()?->hasPassingMark() === true
                : $record->getAttemptOverviewInformation()?->hasPassingMark() !== true
        ];
    }

    /**
     * @return array<string, \Closure>
     */
    private function getPostLoadOrderFields(): array
    {
        $processing_time = $this->test_object->getProcessingTimeInSeconds();
        $reset_time_on_new_attempt = $this->test_object->getResetProcessingTime();

        return [
            'attempt_started_at' => static fn(Participant $a, Participant $b) => $a->getFirstAccess() <=> $b->getFirstAccess(),
            'total_duration' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getTotalDuration($processing_time) <=> $b->getTotalDuration($processing_time),
            'remaining_duration' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getRemainingDuration($processing_time, $reset_time_on_new_attempt)
                <=> $b->getRemainingDuration($processing_time, $reset_time_on_new_attempt),
            'last_access' => static fn(Participant $a, Participant $b) => $a->getLastAccess() <=> $b->getLastAccess(),
            'status_of_attempt' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->getStatusOfAttempt()
                <=> $b->getAttemptOverviewInformation()?->getStatusOfAttempt(),
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
            ) => $a->getAttemptOverviewInformation()?->getMark() <=> $b->getAttemptOverviewInformation()?->getMark(),
            'matriculation' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getMatriculation() <=> $b->getMatriculation(),
            'id_of_attempt' => static fn(
                Participant $a,
                Participant $b
            ) => $a->getAttemptOverviewInformation()?->getExamId() <=> $b->getAttemptOverviewInformation()?->getExamId()
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

        return $this->ui_service->filter()->standard(
            'participant_filter',
            $action,
            $filter_inputs,
            $is_input_initially_rendered,
            true,
            true
        );
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
            StatusOfAttempt::NOT_YET_STARTED->value => $this->lng->txt(StatusOfAttempt::NOT_YET_STARTED->value),
            StatusOfAttempt::RUNNING->value => $this->lng->txt(StatusOfAttempt::RUNNING->value),
            StatusOfAttempt::FINISHED_BY_UNKNOWN->value => $this->lng->txt(StatusOfAttempt::FINISHED_BY_UNKNOWN->value),
            StatusOfAttempt::FINISHED_BY_ADMINISTRATOR->value => $this->lng->txt(StatusOfAttempt::FINISHED_BY_ADMINISTRATOR->value),
            StatusOfAttempt::FINISHED_BY_CRONJOB->value => $this->lng->txt(StatusOfAttempt::FINISHED_BY_CRONJOB->value),
            StatusOfAttempt::FINISHED_BY_DURATION->value => $this->lng->txt(StatusOfAttempt::FINISHED_BY_DURATION->value),
            StatusOfAttempt::FINISHED_BY_PARTICIPANT->value => $this->lng->txt(StatusOfAttempt::FINISHED_BY_PARTICIPANT->value),
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

        $filters['test_passed'] = [
            $field_factory->select($this->lng->txt('tst_passed'), $yes_no_all_options),
            true
        ];

        return $filters;
    }

    private function getTableComponent(ServerRequestInterface $request, ?array $filter)
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
            'name' => $column_factory->text($this->lng->txt('name'))
                ->withIsSortable(!$this->test_object->getAnonymity())
        ];
        if (!$this->test_object->getAnonymity()) {
            $columns['login'] = $column_factory->text($this->lng->txt('login'))->withIsSortable(true);
        }

        $columns += [
            'matriculation' => $column_factory->text($this->lng->txt('matriculation'))
                ->withIsOptional(true, false)
                ->withIsSortable(true),
            'ip_range' => $column_factory->text($this->lng->txt('client_ip_range'))
                ->withIsOptional(true, false)
                ->withIsSortable(true),
            'attempt_started_at' => $column_factory->date(
                $this->lng->txt('tst_attempt_started'),
                $this->current_user->getDateTimeFormat()
            )->withIsSortable(true),
            'total_attempts' => $column_factory->number($this->lng->txt('total_attempts'))
                ->withIsOptional(true, false)
                ->withIsSortable(true),
        ];

        if ($this->test_object->getEnableProcessingTime()) {
            $columns['extra_time'] = $column_factory->text($this->lng->txt('extratime'))
                ->withIsOptional(true, false);
            $columns['total_duration'] = $column_factory->text($this->lng->txt('total_duration'))
                ->withIsOptional(true, false);
            $columns['remaining_duration'] = $column_factory->text($this->lng->txt('remaining_duration'))
                ->withIsOptional(true);
        }

        $columns['status_of_attempt'] = $column_factory->text($this->lng->txt('status_of_attempt'))
            ->withIsSortable(true);

        if ($this->test_object->getMainSettings()->getTestBehaviourSettings()->getExamIdInTestAttemptEnabled()) {
            $columns['id_of_attempt'] = $column_factory->text($this->lng->txt('exam_id_of_attempt'))
                ->withIsOptional(true, false)
                ->withIsSortable(true);
        }

        if ($this->test_access->checkParticipantsResultsAccess()) {
            $columns['reached_points'] = $column_factory->text($this->lng->txt('tst_reached_points'))
                ->withIsSortable(true);
            $columns['nr_of_answered_questions'] = $column_factory->text($this->lng->txt('tst_answered_questions'))
                ->withIsOptional(true, false)
                ->withIsSortable(true);
            $columns['percent_of_available_points'] = $column_factory->number($this->lng->txt('tst_percent_solved'))
                ->withUnit('%')
                ->withIsOptional(true, false)
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
            )->withIsSortable(true)
            ->withOrderingLabels(
                "{$this->lng->txt('tst_passed')}, {$this->lng->txt('yes')} {$this->lng->txt('order_option_first')}",
                "{$this->lng->txt('tst_passed')}, {$this->lng->txt('no')} {$this->lng->txt('order_option_first')}"
            );
            $columns['mark'] = $column_factory->text($this->lng->txt('tst_mark'))
                ->withIsOptional(true, false)
                ->withIsSortable(true);
        }

        $columns['last_access'] = $column_factory->date(
            $this->lng->txt('last_access'),
            $this->current_user->getDateTimeFormat()
        );

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

    private function matchFilter(Participant $record, ?array $filter): bool
    {
        if ($filter === null) {
            return true;
        }

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
