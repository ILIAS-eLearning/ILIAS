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

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class LogTable implements Table\DataRetrieval
{
    public const COLUMN_DATE_TIME = 'date_and_time';
    public const COLUMN_CORRESPONDING_TEST = 'corresponding_test';
    public const COLUMN_ADMIN = 'admin';
    public const COLUMN_PARTICIPANT = 'participant';
    public const COLUMN_SOURCE_IP = 'ip';
    public const COLUMN_QUESTION = 'question';
    public const COLUMN_LOG_ENTRY_TYPE = 'log_entry_type';
    public const COLUMN_INTERACTION_TYPE = 'interaction_type';


    private const FILTER_FIELD_TIME_FROM = 'from';
    private const FILTER_FIELD_TIME_TO = 'until';
    private const FILTER_FIELD_TEST_TITLE = 'test_title';
    private const FILTER_FIELD_QUESTION_TITLE = 'question_title';
    private const FILTER_FIELD_ADMIN = 'admin_name';
    private const FILTER_FIELD_PARTICIPANT = 'participant_name';
    private const FILTER_FIELD_IP = 'ip';
    private const FILTER_FIELD_LOG_ENTRY_TYPE = 'log_entry_type';
    private const FILTER_FIELD_INTERACTION_TYPE = 'interaction_type';

    private const ACTION_DELETE = 'delete';
    private const ACTION_ADDITIONAL_INFORMATION = 'show_additional_information';
    private const ACTION_EXPORT_AS_CSV = 'export_as_csv';

    public function __construct(
        private readonly TestLoggingRepository $logging_repository,
        private readonly TestLogger $logger,
        private readonly GeneralQuestionPropertiesRepository $question_repo,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly DataFactory $data_factory,
        private readonly \ilLanguage $lng,
        private readonly StaticURLServices $static_url,
        private readonly URLBuilder $url_builder,
        private readonly URLBuilderToken $action_parameter_token,
        private readonly URLBuilderToken $row_id_token,
        private readonly \ilObjUser $current_user,
        private readonly ?int $ref_id = null
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
            self::FILTER_FIELD_TIME_FROM => $field_factory->text($this->lng->txt('from')),
            self::FILTER_FIELD_TIME_TO => $field_factory->text($this->lng->txt('until'))
        ];
        if ($this->ref_id === null) {
            $filter_inputs[self::FILTER_FIELD_TEST_TITLE] = $field_factory->text($this->lng->txt('test_title'));
        }

        $filter_inputs += [
            self::FILTER_FIELD_ADMIN => $field_factory->text($this->lng->txt('author')),
            self::FILTER_FIELD_PARTICIPANT => $field_factory->text($this->lng->txt('participant')),
            self::FILTER_FIELD_IP => $field_factory->text($this->lng->txt('ip')),
            self::FILTER_FIELD_QUESTION_TITLE => $field_factory->text($this->lng->txt('question_title')),
            self::FILTER_FIELD_LOG_ENTRY_TYPE => $field_factory->multiSelect(
                $this->lng->txt('log_entry_type'),
                $this->buildLogEntryTypesOptionsForFilter()
            ),
            self::FILTER_FIELD_INTERACTION_TYPE => $field_factory->multiSelect(
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

        return [
            self::COLUMN_DATE_TIME => $f->date($this->lng->txt('date_and_time'), $date_format),
            self::COLUMN_CORRESPONDING_TEST => $f->link($this->lng->txt('test'))->withIsOptional(true, true),
            self::COLUMN_ADMIN => $f->text($this->lng->txt('author'))->withIsOptional(true, true),
            self::COLUMN_PARTICIPANT => $f->text($this->lng->txt('participant'))->withIsOptional(true, true),
            self::COLUMN_SOURCE_IP => $f->text($this->lng->txt('ip'))->withIsOptional(true, true),
            self::COLUMN_QUESTION => $f->text($this->lng->txt('question'))->withIsOptional(true, true),
            self::COLUMN_LOG_ENTRY_TYPE => $f->text($this->lng->txt('log_entry_type'))->withIsOptional(true, true),
            self::COLUMN_INTERACTION_TYPE => $f->text($this->lng->txt('interaction_type'))->withIsOptional(true, true)
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
        list(
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ) = $this->prepareFilterData($filter_data);

        $environment = [
            'timezone' => new \DateTimeZone($this->current_user->getTimeZone())
        ];

        foreach ($this->logging_repository->getLogs(
            $this->logger->getInteractionTypes(),
            $range,
            $order,
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ) as $interaction) {
            yield $interaction->getLogEntryAsDataTableRow(
                $this->lng,
                $this->static_url,
                $this->question_repo,
                $this->ui_factory,
                $this->ui_renderer,
                $row_builder,
                $environment
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        list(
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ) = $this->prepareFilterData($filter_data);

        return $this->logging_repository->getLogsCount(
            $this->logger->getInteractionTypes(),
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        );
    }

    public function executeAction(
        string $action,
        array $affected_items
    ): void {
        match ($action) {
            self::ACTION_ADDITIONAL_INFORMATION => $this->showAdditionalDetails($affected_items[0]),
            self::ACTION_EXPORT_AS_CSV => $this->exportTestUserInteractionsAsCSV($affected_items),
            self::ACTION_DELETE => $this->deleteTestUserInteractions($affected_items)
        };
        exit;
    }

    protected function showAdditionalDetails(string $affected_item): void
    {
        $log = $this->logging_repository->getLog($affected_item);
        if ($log === null) {

        }

        $environment = [
            'timezone' => new \DateTimeZone($this->current_user->getTimeZone())
        ];

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->roundtrip(
                $this->lng->txt('additional_information'),
                $this->ui_factory->legacy(
                    $log->getParsedAdditionalInformation(
                        $this->lng,
                        $this->static_url,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $environment
                    )
                )
            )
        );
    }

    protected function deleteTestUserInteractions(array $affected_items): void
    {
        if ($affected_items === []) {
            $this->showErrorModal($this->lng->txt('no_checkbox'));
            exit;
        }

        if ($affected_items === 'ALL_ITEMS') {
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                'all',
                $this->lng->txt('all')
            );
        } else {
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                json_encode($items),
                $this->lng->txt('selected')
            );
        }

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt('confirmation'),
                $this->lng->txt('test_confirm_log_deletion')
            )->withAffectedItems($items)
        );
    }

    protected function exportTestUserInteractionsAsCSV(): void
    {

    }

    protected function getActions(): array
    {
        $af = $this->ui_factory->table()->action();
        return [
            $af->single(
                $this->lng->txt('show_additional_information'),
                $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_ADDITIONAL_INFORMATION),
                $this->row_id_token
            )->withAsync(),
            $af->multi(
                $this->lng->txt('export_as_csv'),
                $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_EXPORT_AS_CSV),
                $this->row_id_token
            ),
            $af->standard(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_DELETE),
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
        $interaction_types = array_reduce(
            $this->logger->getInteractionTypes(),
            fn(array $et, array $it): array => $et + $it,
            []
        );

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

    private function prepareFilterData(array $filter_array): array
    {
        $from_filter = null;
        $to_filter = null;
        $test_filter = $this->ref_id !== null ? [$this->ref_id] : null;
        $pax_filter = null;
        $admin_filter = null;
        $question_filter = null;

        if (isset($filter_array[self::FILTER_FIELD_TIME_FROM])) {
            $from_filter = (new \DateTimeImmutable($filter_array[self::FILTER_FIELD_TIME_FROM]))->getTimestamp();
        }

        if (isset($filter_array[self::FILTER_FIELD_TIME_TO])) {
            $to_filter = (new \DateTimeImmutable(filter_array[self::FILTER_FIELD_TIME_TO]))->getTimestamp();
        }

        if (isset($filter_array[self::FILTER_FIELD_TEST_TITLE])) {
            $test_filter = array_reduce(
                \ilObject::_getIdsForTitle($filter_array[self::FILTER_FIELD_TEST_TITLE], 'tst', true),
                static fn(array $ref_ids, int $obj_id) => $ref_ids + \ilObject::_getAllReferences($obj_id),
                $test_filter
            );
        }

        if (isset($filter_array[self::FILTER_FIELD_ADMIN])) {
            $admin_query = new \ilUserQuery();
            $admin_query->setTextFilter($filter_array[self::FILTER_FIELD_ADMIN]);
            $admin_filter = $admin_query->query();
        }

        if (isset($filter_array[self::FILTER_FIELD_PARTICIPANT])) {
            $pax_query = new \ilUserQuery();
            $pax_query->setTextFilter($filter_array[self::FILTER_FIELD_PARTICIPANT]);
            $pax_filter = $pax_query->query();
        }

        if (isset($filter_array[self::FILTER_FIELD_QUESTION_TITLE])) {
            $question_filter = $this->question_repo->searchQuestionsByName($filter_array[self::FILTER_FIELD_PARTICIPANT]);
        }

        return [
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter['set'] ?? null,
            $pax_filter['set'] ?? null,
            $question_filter,
            $filter_array[self::FILTER_FIELD_IP] ?? null,
            $filter_array[self::FILTER_FIELD_LOG_ENTRY_TYPE] ?? null,
            $filter_array[self::FILTER_FIELD_INTERACTION_TYPE] ?? null
        ];
    }
}
