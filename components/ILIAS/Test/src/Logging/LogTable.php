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

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\FileDelivery\Delivery\StreamDelivery;

class LogTable implements Table\DataRetrieval
{
    public const QUERY_PARAMETER_NAME_SPACE = ['tst', 'log'];
    public const ACTION_TOKEN_STRING = 'action';
    public const ENTRY_TOKEN_STRING = 'le';

    public const COLUMN_DATE_TIME = 'date_and_time';
    public const COLUMN_CORRESPONDING_TEST = 'corresponding_test';
    public const COLUMN_ADMIN = 'admin';
    public const COLUMN_PARTICIPANT = 'participant';
    public const COLUMN_SOURCE_IP = 'ip';
    public const COLUMN_QUESTION = 'question';
    public const COLUMN_LOG_ENTRY_TYPE = 'log_entry_type';
    public const COLUMN_INTERACTION_TYPE = 'interaction_type';

    public const ACTION_ID_SHOW_ADDITIONAL_INFO = 'show_additional_information';
    private const ACTION_ID_EXPORT = 'export';
    private const ACTION_ID_DELETE = 'delete';

    private const FILTER_FIELD_PERIOD = 'period';
    private const FILTER_FIELD_TEST_TITLE = 'test_title';
    private const FILTER_FIELD_QUESTION_TITLE = 'question_title';
    private const FILTER_FIELD_ADMIN = 'admin_name';
    private const FILTER_FIELD_PARTICIPANT = 'participant_name';
    private const FILTER_FIELD_IP = 'ip';
    private const FILTER_FIELD_LOG_ENTRY_TYPE = 'log_entry_type';
    private const FILTER_FIELD_INTERACTION_TYPE = 'interaction_type';

    private const ACTION_CONFIRM_DELETE = 'confirm_delete';
    private const ACTION_DELETE = 'delete';
    private const ACTION_ADDITIONAL_INFORMATION = 'add_info';
    private const ACTION_EXPORT_AS_CSV = 'csv_export';

    private const EXPORT_FILE_NAME = '_test_log_export';

    /**
     * @var array<string, string|array>
     */
    private ?array $filter_data = null;
    private ?Filter $filter = null;

    public function __construct(
        private readonly TestLoggingRepository $logging_repository,
        private readonly TestLogger $logger,
        private readonly TestLogViewer $log_viewer,
        private readonly TitleColumnsBuilder $title_builder,
        private readonly GeneralQuestionPropertiesRepository $question_repo,
        private readonly \ilUIService $ui_service,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly \ilLanguage $lng,
        private \ilGlobalTemplateInterface $tpl,
        private readonly URLBuilder $url_builder,
        private readonly URLBuilderToken $action_parameter_token,
        private readonly URLBuilderToken $row_id_token,
        private readonly StreamDelivery $stream_delivery,
        private readonly \ilObjUser $current_user,
        private readonly ?int $ref_id = null
    ) {
        $this->lng->loadLanguageModule('dateplaner');
    }

    public function getTable(): Table\Data
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('history'),
            $this->getColumns()
        )->withActions($this->getActions());
    }

    public function getFilter(): Filter
    {
        $this->initializeFilterAndData();
        return $this->filter;
    }

    private function initializeFilterAndData(): void
    {
        if ($this->filter === null) {
            $this->initializeFilter();
        }

        if ($this->filter_data === null) {
            $this->filter_data = $this->ui_service->filter()->getData($this->filter) ?? [];
        }
    }

    private function initializeFilter(): void
    {
        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            self::FILTER_FIELD_PERIOD => $field_factory->duration($this->lng->txt('cal_period'))
                ->withUseTime(true)
                ->withFormat($this->log_viewer->buildUserDateTimeFormat())
        ];
        if ($this->ref_id === null) {
            $filter_inputs[self::FILTER_FIELD_TEST_TITLE] = $field_factory->text($this->lng->txt('test'));
        }

        $filter_inputs += [
            self::FILTER_FIELD_ADMIN => $field_factory->text($this->lng->txt('author')),
            self::FILTER_FIELD_PARTICIPANT => $field_factory->text($this->lng->txt('tst_participant')),
            self::FILTER_FIELD_IP => $field_factory->text($this->lng->txt('client_ip')),
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

        $log_table_filter_id = 'log_table_filter_id';
        if ($this->ref_id !== null) {
            $log_table_filter_id .= "_{$this->ref_id}";
        }

        $this->filter = $this->ui_service->filter()->standard(
            $log_table_filter_id,
            $this->url_builder->buildURI()->__toString(),
            $filter_inputs,
            $active,
            true,
            true
        );
    }


    private function getColumns(): array
    {
        $f = $this->ui_factory->table()->column();

        $columns = [
            self::COLUMN_DATE_TIME => $f->date($this->lng->txt('date_time'), $this->log_viewer->buildUserDateTimeFormat()),
            self::COLUMN_CORRESPONDING_TEST => $f->link($this->lng->txt('test'))->withIsOptional(true, true),
            self::COLUMN_ADMIN => $f->text($this->lng->txt('author'))->withIsOptional(true, true),
            self::COLUMN_PARTICIPANT => $f->text($this->lng->txt('tst_participant'))->withIsOptional(true, true)
        ];

        if ($this->logger->isIPLoggingEnabled()) {
            $columns[self::COLUMN_SOURCE_IP] = $f->text($this->lng->txt('client_ip'))->withIsOptional(true, true);
        }

        return $columns + [
            self::COLUMN_QUESTION => $f->link($this->lng->txt('question'))->withIsOptional(true, true),
            self::COLUMN_LOG_ENTRY_TYPE => $f->text($this->lng->txt('log_entry_type'))->withIsOptional(true, true),
            self::COLUMN_INTERACTION_TYPE => $f->text($this->lng->txt('interaction_type'))->withIsOptional(true, true)
        ];
    }

    public function getRows(
        Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        [
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ] = $this->prepareFilterData($this->filter_data);

        $environment = [
            'timezone' => new \DateTimeZone($this->current_user->getTimeZone()),
            'date_format' => $this->log_viewer->buildUserDateTimeFormat()->toString()
        ];

        foreach ($this->logging_repository->getLogs(
            $this->logger->getInteractionTypes(),
            $test_filter,
            $range,
            $order,
            $from_filter,
            $to_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ) as $interaction) {
            yield $interaction->getLogEntryAsDataTableRow(
                $this->lng,
                $this->title_builder,
                $row_builder,
                $environment
            );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        [
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ] = $this->prepareFilterData($this->filter_data);

        return $this->logging_repository->getLogsCount(
            $this->logger->getInteractionTypes(),
            $test_filter,
            $from_filter,
            $to_filter,
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
            self::ACTION_EXPORT_AS_CSV => $this->exportTestUserInteractions($affected_items),
            self::ACTION_CONFIRM_DELETE => $this->showConfirmTestUserInteractionsDeletion($affected_items),
            self::ACTION_DELETE => $this->deleteTestUserInteractions($affected_items)
        };
    }

    private function showAdditionalDetails(string $affected_item): void
    {
        $log = $this->logging_repository->getLog($affected_item);
        if ($log === null) {
            $this->showErrorModal($this->lng->txt('no_checkbox'));
        }

        $environment = [
            'timezone' => new \DateTimeZone($this->current_user->getTimeZone()),
            'date_format' => $this->log_viewer->buildUserDateTimeFormat()->toString()
        ];

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->roundtrip(
                $this->lng->txt('additional_info'),
                $log->getParsedAdditionalInformation(
                    $this->logger->getAdditionalInformationGenerator(),
                    $this->ui_factory,
                    $environment
                )
            )
        );
        exit;
    }

    private function showConfirmTestUserInteractionsDeletion(array $affected_items): void
    {
        if ($affected_items === []) {
            $this->showErrorModal($this->lng->txt('no_checkbox'));
        }

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt('confirmation'),
                $this->lng->txt('confirm_log_deletion'),
                $this->url_builder
                    ->withParameter($this->action_parameter_token, self::ACTION_DELETE)
                    ->withParameter($this->row_id_token, $affected_items)
                    ->buildURI()->__toString()
            )
        );
        exit;
    }

    private function deleteTestUserInteractions(array $affected_items): void
    {
        if ($this->ref_id !== null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('log_deletion_not_allowed'));
            return;
        }

        $this->logging_repository->deleteLogs($affected_items);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('logs_deleted'));
    }

    private function exportTestUserInteractions(array $affected_items): void
    {
        if ($affected_items === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_checkbox'));
            return;
        }

        $this->log_viewer->buildExcelWorkbookForLogs(
            $this->buildLogsFromAffectedItems($affected_items)
        )->sendToClient(date('Y-m-d') . self::EXPORT_FILE_NAME);
    }

    private function buildLogsFromAffectedItems(array $affected_items): \Generator
    {
        if ($affected_items[0] !== 'ALL_OBJECTS') {
            return $this->logging_repository->getLogsByUniqueIdentifiers($affected_items);
        }

        $this->initializeFilterAndData();
        [
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        ] = $this->prepareFilterData($this->filter_data);
        return $this->logging_repository->getLogs(
            $this->logger->getInteractionTypes(),
            $this->ref_id !== null ? [$this->ref_id] : null,
            null,
            null,
            $from_filter,
            $to_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $log_entry_type_filter,
            $interaction_type_filter
        );
    }

    private function getActions(): array
    {
        $af = $this->ui_factory->table()->action();
        $actions = [
            self::ACTION_ID_SHOW_ADDITIONAL_INFO => $af->single(
                $this->lng->txt('additional_info'),
                $this->url_builder->withParameter(
                    $this->action_parameter_token,
                    self::ACTION_ADDITIONAL_INFORMATION
                ),
                $this->row_id_token
            )->withAsync(),
            self::ACTION_ID_EXPORT => $af->multi(
                $this->lng->txt('export'),
                $this->url_builder->withParameter(
                    $this->action_parameter_token,
                    self::ACTION_EXPORT_AS_CSV
                ),
                $this->row_id_token
            )
        ];
        if ($this->ref_id !== null) {
            return $actions;
        }
        return $actions + [
            self::ACTION_ID_DELETE => $af->standard(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter(
                    $this->action_parameter_token,
                    self::ACTION_CONFIRM_DELETE
                ),
                $this->row_id_token
            )->withAsync()
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildLogEntryTypesOptionsForFilter(): array
    {
        $lang_prefix = TestUserInteraction::LANG_VAR_PREFIX;
        $log_entry_types = $this->logger->getLogEntryTypes();
        $log_entry_options = [];
        foreach ($log_entry_types as $log_entry_type) {
            $log_entry_options [$log_entry_type] = $this->lng->txt($lang_prefix . $log_entry_type);
        }
        asort($log_entry_options);
        return $log_entry_options;
    }

    /**
     * @return array<string, string>
     */
    private function buildInteractionTypesOptionsForFilter(): array
    {
        $lang_prefix = TestUserInteraction::LANG_VAR_PREFIX;
        $interaction_types = array_reduce(
            $this->logger->getInteractionTypes(),
            fn(array $et, array $it): array => [...$et, ...$it],
            []
        );

        $interaction_options = [];
        foreach ($interaction_types as $interaction_type) {
            $interaction_options[$interaction_type] = $this->lng->txt($lang_prefix . $interaction_type);
        }
        asort($interaction_options);
        return $interaction_options;
    }

    private function prepareFilterData(array $filter_array): array
    {
        $from_filter = null;
        $to_filter = null;
        $test_filter = $this->ref_id !== null ? [$this->ref_id] : null;
        $pax_filter = null;
        $admin_filter = null;
        $question_filter = null;

        if (!empty($filter_array[self::FILTER_FIELD_PERIOD][0])) {
            $from_filter = (new \DateTimeImmutable(
                $filter_array[self::FILTER_FIELD_PERIOD][0],
                new \DateTimeZone($this->current_user->getTimeZone())
            ))->getTimestamp();
        }

        if (!empty($filter_array[self::FILTER_FIELD_PERIOD][1])) {
            $to_filter = (new \DateTimeImmutable(
                $filter_array[self::FILTER_FIELD_PERIOD][1],
                new \DateTimeZone($this->current_user->getTimeZone())
            ))->getTimestamp();
        }

        if (!empty($filter_array[self::FILTER_FIELD_TEST_TITLE])) {
            $test_filter = array_reduce(
                \ilObject::_getIdsForTitle($filter_array[self::FILTER_FIELD_TEST_TITLE], 'tst', true) ?? [],
                static fn(array $ref_ids, int $obj_id) => array_merge(
                    $ref_ids,
                    \ilObject::_getAllReferences($obj_id)
                ),
                $test_filter ?? []
            );
        }

        if (!empty($filter_array[self::FILTER_FIELD_ADMIN])) {
            $admin_query = new \ilUserQuery();
            $admin_query->setTextFilter($filter_array[self::FILTER_FIELD_ADMIN]);
            $admin_filter = $this->extractIdsFromUserQuery(
                $admin_query->query()
            );
        }

        if (!empty($filter_array[self::FILTER_FIELD_PARTICIPANT])) {
            $pax_query = new \ilUserQuery();
            $pax_query->setTextFilter($filter_array[self::FILTER_FIELD_PARTICIPANT]);
            $pax_filter = $this->extractIdsFromUserQuery(
                $pax_query->query()
            );
        }

        if (!empty($filter_array[self::FILTER_FIELD_QUESTION_TITLE])) {
            $question_filter = $this->question_repo->searchQuestionIdsByTitle(
                $filter_array[self::FILTER_FIELD_QUESTION_TITLE]
            );
        }

        return [
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            !empty($filter_array[self::FILTER_FIELD_IP]) ? $filter_array[self::FILTER_FIELD_IP] : null,
            $filter_array[self::FILTER_FIELD_LOG_ENTRY_TYPE] ?? null,
            $filter_array[self::FILTER_FIELD_INTERACTION_TYPE] ?? null
        ];
    }

    private function showErrorModal(string $message): void
    {
        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->roundtrip(
                $this->lng->txt('error'),
                $this->ui_factory->messageBox()->failure($message)
            )
        );
        exit;
    }

    private function extractIdsFromUserQuery(array $response): array
    {
        if (!isset($response['set'])) {
            return [];
        }

        return array_map(
            static fn(array $v): int => $v['usr_id'],
            $response['set']
        );
    }
}
