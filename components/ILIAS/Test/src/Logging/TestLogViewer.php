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
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
use Psr\Http\Message\ServerRequestInterface;

class TestLogViewer
{
    private DataFactory $data_factory;

    public function __construct(
        private readonly TestLoggingRepository $logging_repository,
        private readonly TestLogger $logger,
        private readonly TitleColumnsBuilder $title_builder,
        private readonly GeneralQuestionPropertiesRepository $question_repository,
        private readonly ServerRequestInterface $request,
        private readonly RequestWrapper $request_wrapper,
        private readonly \ilUIService $ui_service,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly \ilLanguage $lng,
        private \ilGlobalTemplateInterface $tpl,
        private readonly StreamDelivery $stream_delivery,
        private readonly \ilObjUser $current_user
    ) {
        $this->data_factory = new DataFactory();
    }

    public function getLogTable(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token,
        ?int $ref_id = null
    ): array {
        $log_table = new LogTable(
            $this->logging_repository,
            $this->logger,
            $this,
            $this->title_builder,
            $this->question_repository,
            $this->ui_service,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->tpl,
            $url_builder,
            $action_parameter_token,
            $row_id_token,
            $this->stream_delivery,
            $this->current_user,
            $ref_id,
        );

        return [
            $log_table->getFilter(),
            $log_table->getTable()->withRequest($this->request)
        ];
    }

    public function executeLogTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token,
        ?int $ref_id = null
    ): void {
        $log_table = new LogTable(
            $this->logging_repository,
            $this->logger,
            $this,
            $this->title_builder,
            $this->question_repository,
            $this->ui_service,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->tpl,
            $url_builder,
            $action_parameter_token,
            $row_id_token,
            $this->stream_delivery,
            $this->current_user,
            $ref_id,
        );

        $action = $this->request_wrapper->retrieve(
            $action_parameter_token->getName(),
            $this->refinery->kindlyTo()->string()
        );

        if ($action === '') {
            return;
        }

        $affected_items = [];
        if ($this->request_wrapper->has($row_id_token->getName())) {
            $affected_items = $this->request_wrapper->retrieve(
                $row_id_token->getName(),
                $this->refinery->byTrying(
                    [
                        $this->refinery->container()->mapValues(
                            $this->refinery->kindlyTo()->string()
                        ),
                        $this->refinery->always([])
                    ]
                )
            );
        }

        $log_table->executeAction($action, $affected_items);
    }

    public function getLogExportForRefjId(int $ref_id): \ilExcel
    {
        return $this->buildExcelWorkbookForLogs(
            $this->logging_repository->getLogs(
                $this->logger->getInteractionTypes(),
                [$ref_id]
            )
        );
    }

    public function buildExcelWorkbookForLogs(\Generator $logs): \ilExcel
    {
        $workbook = new \ilExcel();
        $workbook->addSheet($this->lng->txt('history'));

        $column = 0;
        foreach ($this->getColumHeadingsForExport() as $header_cell) {
            $workbook->setCell(1, $column++, $header_cell);
        }
        $workbook->setBold('A' . 1 . ':' . $workbook->getColumnCoord($column - 1) . 1);
        $workbook->setColors('A' . 1 . ':' . $workbook->getColumnCoord($column - 1) . 1, 'C0C0C0');

        return $this->addRowsFromLogs($logs, $workbook);
    }

    private function addRowsFromLogs(
        \Generator $logs,
        \ilExcel $workbook
    ): \ilExcel {
        $row = 1;
        foreach ($logs as $log) {
            $row++;
            $column = 0;
            foreach ($log->getLogEntryAsExportRow(
                $this->lng,
                $this->title_builder,
                $this->logger->getAdditionalInformationGenerator(),
                [
                    'timezone' => new \DateTimeZone($this->current_user->getTimeZone()),
                    'date_format' => $this->buildUserDateTimeFormat()->toString()
                ]
            ) as $cell_content) {
                $workbook->setCell(
                    $row,
                    $column++,
                    $cell_content
                );
            }
        }
        return $workbook;
    }

    private function getColumHeadingsForExport(): array
    {
        return [
            $this->lng->txt('date_time'),
            $this->lng->txt('test'),
            $this->lng->txt('author'),
            $this->lng->txt('tst_participant'),
            $this->lng->txt('client_ip'),
            $this->lng->txt('question'),
            $this->lng->txt('log_entry_type'),
            $this->lng->txt('interaction_type'),
            $this->lng->txt('additional_info')
        ];
    }

    public function buildUserDateTimeFormat(): DateFormat
    {
        $user_format = $this->current_user->getDateFormat();
        if ($this->current_user->getTimeFormat() == \ilCalendarSettings::TIME_FORMAT_24) {
            return $this->data_factory->dateFormat()->amend(
                $this->data_factory->dateFormat()->withTime24($user_format)
            )->colon()->seconds()->get();
        }
        return $this->data_factory->dateFormat()->amend(
            $user_format
        )->space()->hours12()->colon()->minutes()->colon()->seconds()->meridiem()->get();
    }

    /* The following functions will be removed with ILIAS 11 */

    public function getLegacyLogExportForObjId(?int $obj_id = null): string
    {
        $log_output = $this->logging_repository->getLegacyLogsForObjId($obj_id);

        $users = [];
        $csv = [];
        $separator = ';';
        $header_row = [
            $this->lng->txt('date_time'),
            $this->lng->txt('user'),
            $this->lng->txt('log_text'),
            $this->lng->txt('question')
        ];

        $csv[] = $this->processCSVRow($header_row);
        foreach ($log_output as $log) {
            if (!array_key_exists($log['user_fi'], $users)) {
                $users[$log['user_fi']] = \ilObjUser::_lookupName((int) $log['user_fi']);
            }

            $content_row = [];
            $date = new \ilDateTime((int) $log['tstamp'], IL_CAL_UNIX);
            $content_row[] = $date->get(IL_CAL_FKT_DATE, 'Y-m-d H:i');
            $content_row[] = trim($users[$log['user_fi']]['title'] . ' '
                . $users[$log['user_fi']]['firstname'] . ' ' . $users[$log['user_fi']]['lastname']);
            $content_row[] = trim($log['logtext']);
            $content_row[] = $this->buildQuestionTitleForLegacyLog($log);
            $csv[] = $this->processCSVRow($content_row);
        }
        $csvoutput = '';
        foreach ($csv as $row) {
            $csvoutput .= implode($separator, $row) . "\n";
        }
        return $csvoutput;
    }

    public function buildQuestionTitleForLegacyLog(array $log): string
    {
        if (!$log['question_fi'] && !$log['original_fi']) {
            return '';
        }
        $title = '';
        if ($log['question_fi']) {
            $title = $this->question_repository->getForQuestionId((int) $log['question_fi'])?->getTitle();
        }

        if ($title === null && $log['original_fi']) {
            $title = $this->question_repository->getForQuestionId((int) $log['original_fi'])?->getTitle();
        }

        if ($title === null) {
            return '';
        }

        return $this->lng->txt('question') . ': ' . $title;
    }

    private function processCSVRow(
        mixed $row,
        bool $quote_all = false,
        string $separator = ";"
    ): array {
        $resultarray = [];
        foreach ($row as $rowindex => $entry) {
            $surround = false;
            if ($quote_all) {
                $surround = true;
            }
            if (is_string($entry) && strpos($entry, "\"") !== false) {
                $entry = str_replace("\"", "\"\"", $entry);
                $surround = true;
            }
            if (is_string($entry) && strpos($entry, $separator) !== false) {
                $surround = true;
            }

            if (is_string($entry)) {
                // replace all CR LF with LF (for Excel for Windows compatibility
                $entry = str_replace(chr(13) . chr(10), chr(10), $entry);
            }

            if ($surround) {
                $entry = "\"" . $entry . "\"";
            }

            $resultarray[$rowindex] = $entry;
        }
        return $resultarray;
    }
}
