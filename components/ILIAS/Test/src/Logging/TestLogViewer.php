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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class TestLogViewer
{
    private DataFactory $data_factory;

    public function __construct(
        private readonly TestLoggingRepository $logging_repository,
        private readonly TestLogger $logger,
        private readonly GeneralQuestionPropertiesRepository $question_repo,
        private readonly ServerRequestInterface $request,
        private readonly RequestWrapper $request_wrapper,
        private readonly StaticURLServices $static_url,
        private readonly \ilUIService $ui_service,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly \ilLanguage $lng,
        private readonly \ilObjUser $current_user
    ) {
        $this->data_factory = new DataFactory();
    }

    public function getLogTable(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token,
        int $ref_id = null
    ): array {
        $log_table = new LogTable(
            $this->logging_repository,
            $this->logger,
            $this->question_repo,
            $this->ui_factory,
            $this->ui_renderer,
            $this->data_factory,
            $this->lng,
            $this->static_url,
            $url_builder,
            $action_parameter_token,
            $row_id_token,
            $this->current_user,
            $ref_id,
        );

        $filter = $log_table->getFilter($this->ui_service);
        $filter_data = $this->ui_service->filter()->getData($filter);
        return [
            $filter,
            $log_table->getTable()->withRequest($this->request)->withFilter($filter_data)
        ];
    }

    public function executeLogTableAction(
        URLBuilder $url_builder,
        URLBuilderToken $action_parameter_token,
        URLBuilderToken $row_id_token,
        int $ref_id = null
    ): array {
        $log_table = new LogTable(
            $this->logging_repository,
            $this->logger,
            $this->question_repo,
            $this->ui_factory,
            $this->ui_renderer,
            $this->data_factory,
            $this->lng,
            $this->static_url,
            $url_builder,
            $action_parameter_token,
            $row_id_token,
            $this->current_user,
            $ref_id,
        );

        $action = $this->request_wrapper->retrieve(
            $action_parameter_token->getName(),
            $this->refinery->kindlyTo()->string()
        );

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

    public function getLegacyLogExportForObjId(?int $obj_id = null): string
    {
        $log_output = $this->logging_repository->getLegacyLogsForObjId($obj_id);

        $users = [];
        $csv = [];
        $separator = ';';
        $header_row = [
            $this->lng->txt('assessment_log_datetime'),
            $this->lng->txt('user'),
            $this->lng->txt('assessment_log_text'),
            $this->lng->txt('question')
        ];

        $csv[] = $this->processCSVRow($header_row);
        foreach ($log_output as $log) {
            if (!array_key_exists($log['user_fi'], $users)) {
                $users[$log['user_fi']] = \ilObjUser::_lookupName((int) $log['user_fi']);
            }
            $title = '';
            if ($log['question_fi']) {
                $title = $this->lng->txt('assessment_log_question') . ': '
                    . $this->questionrepository->getForQuestionId((int) $log['question_fi'])->getTitle();
            }

            if ($title === '' && $log['original_fi']) {
                $title = $this->lng->txt('assessment_log_question') . ': '
                    . $this->questionrepository->getForQuestionId((int) $log['original_fi'])->getTitle();
            }

            $content_row = [];
            $date = new \ilDateTime((int) $log['tstamp'], IL_CAL_UNIX);
            $content_row[] = $date->get(IL_CAL_FKT_DATE, 'Y-m-d H:i');
            $content_row[] = trim($users[$log['user_fi']]['title'] . ' '
                . $users[$log['user_fi']]['firstname'] . ' ' . $users[$log['user_fi']]['lastname']);
            $content_row[] = trim($log['logtext']);
            $content_row[] = $title;
            $csv[] = $this->processCSVRow($content_row);
        }
        $csvoutput = '';
        foreach ($csv as $row) {
            $csvoutput .= implode($separator, $row) . "\n";
        }
        return $csvoutput;
    }

    public function processCSVRow(
        array $row
    ): array {
        $result_row = [];
        foreach ($row as $colindex => $entry) {
            if (strpos($entry, '"') !== false) {
                $entry = str_replace('"', '""', $entry);

            }
            $entry = str_replace(chr(13) . chr(10), chr(10), $entry);
            $result_row[$colindex] = mb_convert_encoding('"' . $entry . '"', 'ISO-8859-1', 'UTF-8');
        }
        return $result_row;
    }

}
