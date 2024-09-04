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

use DateTimeImmutable;
use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ilLanguage;
use ilObjTest;
use ilTestParticipant;
use ilTestParticipantAccessFilterFactory;
use ilTestParticipantList;
use ilUIService;
use Psr\Http\Message\ServerRequestInterface;

use function dump;
use function sprintf;

class ParticipantTable implements DataRetrieval
{
    private const ID = 'participant_table';
    private const ATTEMPT_NOT_STARTED = 'not_started';
    private const ATTEMPT_RUNNING = 'running';
    private const ATTEMPT_FINISHED_BY_PARTICIPANT = 'finished_by_participant';
    private const ATTEMPT_FINISHED_BY_ADMINISTRATOR = 'finished_by_administrator';
    private const ATTEMPT_FINISHED_BY_DURATION = 'finished_by_duration';
    private const ATTEMPT_FINISHED_BY_CRONJOB = 'finished_by_cronjob';

    private ilTestParticipantList|Generator|null $records = null;
    /**
     * @var array
     */
    private array $table_actions = [];

    private ?ilObjTest $test_object = null;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilUIService $ui_service,
        private readonly ilLanguage $lng,
        private readonly DataFactory $data_factory,
        private readonly RequestDataCollector $test_request,
        private readonly ilTestParticipantAccessFilterFactory $participant_access_filter,
        private readonly ParticipantRepository $repository,
    ) {
    }


    public function execute(URLBuilder $url_builder)
    {
        [$url_builder, $table_action_token] = $url_builder->acquireParameter([self::ID], 'action');

        $action = $this->test_request->strVal($table_action_token->getName());
        $table_action = $this->table_actions[$action];
        $table_action->execute($url_builder->withParameter($table_action_token, $table_action->getActionId()));
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $filter = $this->getFilterComponent(
            '', //@TODO action
            $this->test_request->getRequest()
        );
        $table = $this->getTableComponent(
            $this->test_request->getRequest(),
            $this->ui_service->filter()->getData($filter),
        );

        [$url_builder, $table_action_token] = $url_builder->acquireParameter([self::ID], 'action');

        foreach ($this->table_actions as $table_action) {
            $table = $table->withActions(
                $table_action->getActions(
                    $url_builder->withParameter($table_action_token, $table_action->getActionId())
                )
            );
        }

        return [
            $filter,
            $table
        ];
    }

    public function withTestObject(?ilObjTest $test_object): self
    {
        $clone = clone $this;
        $clone->test_object = $test_object;

        foreach ($this->table_actions as $table_action) {
            $clone->table_actions[$table_action->getActionId()] = $table_action->withTestObject($test_object);
        }

        return $clone;
    }

    public function withTableAction(ParticipantTableModalAction $table_action): self
    {
        $clone = clone $this;
        $clone->table_actions[$table_action->getActionId()] = $table_action->withTestObject($this->test_object);
        return $clone;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->getViewControlledRecords($filter_data, $range, $order) as $record) {
            $date_format = $this->data_factory
                ->dateFormat()
                ->withTime24($this->data_factory->dateFormat()->germanShort());

            $last_access = $this->test_object->_getLastAccess($record->getActiveId());
            $started_at = $this->test_object->getStartingTimeOfUser($record->getActiveId());
            $extra_time = $this->test_object->getExtraTime($record->getActiveId());
            $total_duration = $this->test_object->getProcessingTimeInSeconds($record->getActiveId()) / 60;
            $working_time = ceil($this->test_object->getCompleteWorkingTimeOfParticipant($record->getActiveId()) / 60);

            $remaining_duration = max(0, $total_duration - $working_time);

            $row = [
                'name' => $record->getLastname(),
                'login' => $record->getLogin(),
                'started_at' => $started_at ? $date_format->applyTo((new DateTimeImmutable())->setTimestamp($started_at)) : "",
                'status_of_attempt' => $this->lng->txt($this->resolveStatusOfAttempt($record, $started_at)),
                'last_access' => $last_access ? $date_format->applyTo(new DateTimeImmutable($last_access)) : "",
                'ip_range' => $record->getClientIpTo() !== "" || $record->getClientIpFrom() !== ""
                    ? sprintf("%s - %s", $record->getClientIpFrom(), $record->getClientIpTo())
                    : "",
                'total_attempts' => $record->getTries(),
                'extra_time' => $extra_time > 0 ? sprintf('%d min', $extra_time) : "",
                'total_duration' => $total_duration > 0 ? sprintf('%d min', $total_duration) : "",
                'remaining_duration' => sprintf('%d min', $remaining_duration),
            ];

            yield $this->onDataRow(
                $row_builder->buildDataRow((string) $record->getActiveId(), $row),
                $record
            );
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->repository->countParticipants($this->test_object->getTestId());
    }

    protected function onDataRow(DataRow $row, mixed $record): DataRow
    {
        foreach ($this->table_actions as $table_action) {
            $row = $table_action->onDataRow($row, $record);
        }
        return $row;
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
            'running' => $this->lng->txt('running'),
            'finished_by_participant' => $this->lng->txt('finished_by_participant'),
            'finished_by_administrator' => $this->lng->txt('finished_by_administrator'),
            'finished_by_duration' => $this->lng->txt('finished_by_duration'),
            'finished_by_cronjob' => $this->lng->txt('finished_by_cronjob')
        ];

        return [
            'name' => [$field_factory->text($this->lng->txt('name')), true],
            'login' => [$field_factory->text($this->lng->txt('login')), true],
            'ip_range' => [$field_factory->text($this->lng->txt('client_ip_range')), true],
            'solution' => [$field_factory->select($this->lng->txt('solutions'), $solution_options), true],
            'extra_time' => [$field_factory->select($this->lng->txt('extratime'), $yes_no_all_options), true],
            'status_of_attempt' => [$field_factory->select($this->lng->txt('status_of_attempt'), $status_of_attempt_options), true],
        ];
    }

    private function getTableComponent(ServerRequestInterface $request, array $filter)
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('list_of_participants'),
            $this->getColumns(),
            $this
        )
            ->withId(self::ID)
            ->withRequest($request)
            ->withFilter($filter)
        ;
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        return [
            'name' => $column_factory->text($this->lng->txt('name')),
            'login' => $column_factory->text($this->lng->txt('login')),
            'ip_range' => $column_factory->text($this->lng->txt('client_ip_range'))->withIsOptional(true),
            'started_at' => $column_factory->text($this->lng->txt('tst_started')),
            'total_attempts' => $column_factory->text($this->lng->txt('total_attempts'))->withIsOptional(true),
            'extra_time' => $column_factory->text($this->lng->txt('extratime'))->withIsOptional(true),
            'total_duration' => $column_factory->text($this->lng->txt('total_duration'))->withIsOptional(true),
            'remaining_duration' => $column_factory->text($this->lng->txt('remaining_duration'))->withIsOptional(true),
            'status_of_attempt' => $column_factory->text($this->lng->txt('status_of_attempt')),
            'last_access' => $column_factory->text($this->lng->txt('last_access')),
        ];
    }

    private function getViewControlledRecords(?array $filter_data, Range $range, Order $order)
    {
        return $this->loadRecords($filter_data);
    }

    private function loadRecords(?array $filter)
    {
        if ($this->records !== null) {
            return $this->records;
        }

        $this->records = $this->repository->loadParticipants($this->test_object->getTestId());

        //        $this->records = $this->test_object->getActiveParticipantList()->getAccessFilteredList(
        //            $this->participant_access_filter->getManageParticipantsUserFilter($this->test_object->getRefId())
        //        );

        return $this->records;
    }

    private function resolveStatusOfAttempt(ilTestParticipant $record, int|bool $started_at): string
    {
        if (!$started_at && !$record->hasUnfinishedPasses() && !$record->isTestFinished()) {
            return self::ATTEMPT_NOT_STARTED;
        }

        if ($started_at && $record->hasUnfinishedPasses()) {
            return self::ATTEMPT_RUNNING;
        }

        return self::ATTEMPT_FINISHED_BY_PARTICIPANT;

        // @TODO How to evaluate how the test pass was finished
        #return self::ATTEMPT_FINISHED_BY_ADMINISTRATOR;

    }
}
