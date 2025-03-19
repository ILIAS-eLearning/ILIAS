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

use ILIAS\Test\Results\Presentation\TitlesBuilder as ResultsTitleBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\ResourceStorage\Services as IRSS;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Maximilian Becker <mbecker@databay.de>
 */
class ilTestArchiver
{
    public const DIR_SEP = '/';

    public const HTML_SUBMISSION_FILENAME = 'test_submission.html';
    public const PASS_MATERIALS_PATH_COMPONENT = 'materials';
    public const QUESTION_PATH_COMPONENT_PREFIX = 'q_';

    public const TEST_BEST_SOLUTION_PATH_COMPONENT = 'best_solution';
    public const HTML_BEST_SOLUTION_FILENAME = 'best_solution.html';
    public const TEST_MATERIALS_PATH_COMPONENT = 'materials';

    private const TEST_RESULT_FILENAME = 'test_result.html';

    public const TEST_OVERVIEW_HTML_FILENAME = 'results_overview_html_v';
    public const TEST_OVERVIEW_HTML_POSTFIX = '.html';

    public const LOG_DTSGROUP_FORMAT = 'D M j G:i:s T Y';
    public const LOG_ADDITION_STRING = ' Adding ';
    public const LOG_CREATION_STRING = ' Creating ';
    public const LOG_UPDATE_STRING = ' Updating ';
    public const LOG_DELETION_STRING = ' Deleting ';

    public const TEST_LOG_FILENAME = 'test.log';
    public const DATA_INDEX_FILENAME = 'data_index.csv';
    public const ARCHIVE_LOG = 'archive.log';

    public const EXPORT_DIRECTORY = 'archive_exports';

    private string $external_directory_path;
    private string $client_id = CLIENT_ID;
    private $archive_data_index;

    protected ilTestHTMLGenerator $html_generator;

    protected ?ilTestParticipantData $participant_data = null;

    public function __construct(
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly ilObjUser $user,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly IRSS $irss,
        private readonly ServerRequestInterface $request,
        private readonly ilObjectDataCache $obj_cache,
        private readonly ilTestParticipantAccessFilterFactory $participant_access_filter_factory,
        private readonly int $test_obj_id,
        private ?int $test_ref_id = null
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $ilias = $DIC['ilias'];

        $this->html_generator = new ilTestHTMLGenerator();
        $this->external_directory_path = $ilias->ini_ilias->readVariable('clients', 'datadir');
        $this->archive_data_index = $this->readArchiveDataIndex();
    }

    public function getParticipantData(): ?ilTestParticipantData
    {
        return $this->participant_data;
    }

    public function setParticipantData(ilTestParticipantData $participant_data): void
    {
        $this->participant_data = $participant_data;
    }

    public function handInParticipantQuestionMaterial(
        int $active_fi,
        int $pass,
        int $question_fi,
        string $original_filename,
        string $file_path
    ): void {
        $this->ensureTestArchiveIsAvailable();
        $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);

        $pass_question_directory = $this->getPassDataDirectory($active_fi, $pass)
            . self::DIR_SEP . self::QUESTION_PATH_COMPONENT_PREFIX . $question_fi;
        if (!is_dir($pass_question_directory)) {
            mkdir($pass_question_directory, 0777, true);
        }

        copy($file_path, $pass_question_directory . self::DIR_SEP . $original_filename);

        $this->logArchivingProcess(
            date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING
            . $pass_question_directory . self::DIR_SEP . $original_filename
        );
    }

    public function handInParticipantMisc(
        int $active_fi,
        int $pass,
        string $original_filename,
        string $file_path
    ): void {
        $this->ensureTestArchiveIsAvailable();
        $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);
        $new_path = $this->getPassDataDirectory($active_fi, $pass) . self::DIR_SEP . $original_filename;
        copy($file_path, $new_path);
        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $new_path);
    }

    public function handInTestBestSolution(string $best_solution): void
    {
        $this->ensureTestArchiveIsAvailable();

        $best_solution_path = $this->getTestArchive() . self::DIR_SEP . self::TEST_BEST_SOLUTION_PATH_COMPONENT;
        if (!is_dir($best_solution_path)) {
            mkdir($best_solution_path, 0777, true);
        }

        $this->html_generator->generateHTML(
            $best_solution,
            $best_solution_path . self::DIR_SEP . self::HTML_BEST_SOLUTION_FILENAME
        );

        $this->logArchivingProcess(
            date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING
            . $best_solution_path . self::DIR_SEP . self::HTML_BEST_SOLUTION_FILENAME
        );

        $this->logArchivingProcess(
            date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $best_solution_path
        );
    }

    public function handInParticipantUploadedResults(
        int $active_fi,
        int $pass,
        ilObjTest $tst_obj
    ): void {
        $questions = $tst_obj->getQuestionsOfPass($active_fi, $pass);
        foreach ($questions as $question) {
            $question = $tst_obj->getQuestionDataset($question['question_fi']);
            if ($question->type_tag === 'assFileUpload') {
                $this->ensureTestArchiveIsAvailable();
                $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);
                $this->ensurePassMaterialsDirectoryIsAvailable($active_fi, $pass);
                $pass_material_directory = $this->getPassMaterialsDirectory($active_fi, $pass);
                $archive_folder = $pass_material_directory . self::DIR_SEP . $question->question_id . self::DIR_SEP;
                if (!file_exists($archive_folder)) {
                    mkdir($archive_folder, 0777, true);
                }
                $resource_id = $tst_obj->getTextAnswer($active_fi, $question->question_id, $pass);
                if ($resource_id === '') {
                    continue;
                }
                $irss_unique_id = $this->irss->manage()->find($resource_id);
                if ($irss_unique_id != null) {
                    $resource = $this->irss->manage()->getResource($irss_unique_id);
                    $information = $resource->getCurrentRevision()->getInformation();
                    $stream = $this->irss->consume()->stream($irss_unique_id);
                    // this feels unnecessary..
                    $file_stream = fopen($stream->getStream()->getMetadata('uri'), 'r');
                    $file_content = stream_get_contents($file_stream);
                    fclose($file_stream);
                    $target_destination = $archive_folder . $information->getTitle();
                    file_put_contents($target_destination, $file_content);
                }
            }
        }
    }

    public function handInBestSolutionQuestionMaterial(
        int $question_fi,
        string $orginial_filename,
        string $file_path
    ): void {
        $this->ensureTestArchiveIsAvailable();

        $best_solution_path = $this->getTestArchive() . self::DIR_SEP . self::TEST_BEST_SOLUTION_PATH_COMPONENT;
        if (!is_dir($best_solution_path)) {
            mkdir($best_solution_path, 0777, true);
        }

        $materials_path = $best_solution_path . self::DIR_SEP . self::TEST_MATERIALS_PATH_COMPONENT;
        if (!is_dir($materials_path)) {
            mkdir($materials_path, 0777, true);
        }

        $question_materials_path = $materials_path . self::DIR_SEP . self::QUESTION_PATH_COMPONENT_PREFIX . $question_fi;
        if (!is_dir($question_materials_path)) {
            mkdir($question_materials_path, 0777, true);
        }

        copy($file_path, $question_materials_path . self::DIR_SEP . $orginial_filename);

        $this->logArchivingProcess(
            date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING
            . $question_materials_path . self::DIR_SEP . $orginial_filename
        );
    }

    public function handInTestResult(int $active_fi, int $pass, string $pdf_path): void
    {
        $this->ensureTestArchiveIsAvailable();
        $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);
        $new_path = $this->getPassDataDirectory($active_fi, $pass) . self::DIR_SEP . self::TEST_RESULT_FILENAME;
        copy($pdf_path, $new_path);
        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $new_path);
    }

    protected function hasTestArchive(): bool
    {
        return is_dir($this->getTestArchive());
    }

    protected function createArchiveForTest(): void
    {
        ilFileUtils::makeDirParents($this->getTestArchive());
    }

    protected function getTestArchive(): string
    {
        $test_archive_directory = $this->external_directory_path . self::DIR_SEP . $this->client_id . self::DIR_SEP . 'tst_data'
            . self::DIR_SEP . 'archive' . self::DIR_SEP . 'tst_' . $this->test_obj_id;
        return $test_archive_directory;
    }

    protected function ensureTestArchiveIsAvailable(): void
    {
        if (!$this->hasTestArchive()) {
            $this->createArchiveForTest();
        }
        return;
    }

    public function updateTestArchive(): void
    {
        $query = 'SELECT * FROM ass_log WHERE obj_fi = ' . $this->db->quote($this->test_obj_id, 'integer');
        $result = $this->db->query($query);

        $outfile_lines = '';
        while (($row = $this->db->fetchAssoc($result)) !== null) {
            $outfile_lines .= "\r\n" . implode("\t", $row);
        }
        file_put_contents($this->getTestArchive() . self::DIR_SEP . self::TEST_LOG_FILENAME, $outfile_lines);

        // Generate test pass overview
        $test = new ilObjTest($this->test_obj_id, false);
        if ($this->test_ref_id !== null) {
            $test->setRefId($this->test_ref_id);
        }

        $array_of_actives = [];
        $participants = $test->getParticipants();

        foreach (array_keys($participants) as $key) {
            $array_of_actives[] = $key;
        }

        $filename = realpath($this->getTestArchive()) . self::DIR_SEP . 'participant_attempt_overview.html';
        $this->html_generator->generateHTML(
            $this->createUserResultsForArchive(
                $test,
                $array_of_actives
            ),
            $filename
        );
    }

    public function ensureZipExportDirectoryExists(): void
    {
        if (!$this->hasZipExportDirectory()) {
            $this->createZipExportDirectory();
        }
    }

    public function hasZipExportDirectory(): bool
    {
        return is_dir($this->getZipExportDirectory());
    }

    protected function createZipExportDirectory(): void
    {
        mkdir($this->getZipExportDirectory(), 0777, true);
    }

    public function getZipExportDirectory(): string
    {
        return $this->external_directory_path . self::DIR_SEP . $this->client_id . self::DIR_SEP . 'tst_data'
            . self::DIR_SEP . self::EXPORT_DIRECTORY . self::DIR_SEP . 'tst_' . $this->test_obj_id;
    }

    public function compressTestArchive(): void
    {
        $this->updateTestArchive();
        $this->ensureZipExportDirectoryExists();

        $zip_output_path = $this->getZipExportDirectory();
        $zip_output_filename = 'test_archive_obj_' . $this->test_obj_id . '_' . time() . '_.zip';

        ilFileUtils::zip($this->getTestArchive(), $zip_output_path . self::DIR_SEP . $zip_output_filename, true);
        return;
    }

    protected function hasPassDataDirectory(int $active_fi, int $pass): bool
    {
        return is_dir($this->getPassDataDirectory($active_fi, $pass));
    }

    protected function createPassDataDirectory(int $active_fi, int $pass): void
    {
        mkdir($this->getPassDataDirectory($active_fi, $pass), 0777, true);
        return;
    }

    private function buildPassDataDirectory($active_fi, $pass): ?string
    {
        foreach ($this->archive_data_index as $data_index_entry) {
            if ($data_index_entry != null && $data_index_entry['identifier'] == $active_fi . '|' . $pass) {
                array_shift($data_index_entry);
                return $this->getTestArchive() . self::DIR_SEP . implode(self::DIR_SEP, $data_index_entry);
            }
        }

        return null;
    }

    protected function getPassDataDirectory(int $active_fi, int $pass): ?string
    {
        $pass_data_dir = $this->buildPassDataDirectory($active_fi, $pass);

        if ($pass_data_dir !== null) {
            return $pass_data_dir;
        }

        $test_obj = new ilObjTest($this->test_obj_id, false);
        if ($test_obj->getAnonymity()) {
            $firstname = 'anonym';
            $lastname = '';
            $matriculation = '0';
        } else {
            if ($this->getParticipantData()) {
                $usr_data = $this->getParticipantData()->getUserDataByActiveId($active_fi);
                $firstname = $usr_data['firstname'];
                $lastname = $usr_data['lastname'];
                $matriculation = $usr_data['matriculation'];
            } else {

                $firstname = $this->user->getFirstname();
                $lastname = $this->user->getLastname();
                $matriculation = $this->user->getMatriculation();
            }
        }

        $this->appendToArchiveDataIndex(
            date(DATE_ISO8601),
            $active_fi,
            $pass,
            $firstname,
            $lastname,
            $matriculation
        );

        return $this->buildPassDataDirectory($active_fi, $pass);
    }

    protected function ensurePassDataDirectoryIsAvailable(int $active_fi, int $pass): void
    {
        if (!$this->hasPassDataDirectory($active_fi, $pass)) {
            $this->createPassDataDirectory($active_fi, $pass);
        }
        return;
    }

    protected function hasPassMaterialsDirectory(int $active_fi, int $pass): bool
    {
        if (is_dir($this->getPassMaterialsDirectory($active_fi, $pass))) {
            return true;
        }
        return false;
    }

    protected function createPassMaterialsDirectory(int $active_fi, int $pass): string
    {
        /**
         * Data is taken from the current user as the implementation expects the
         * first interaction of the pass takes place from the usage/behaviour of
         * the current user. (skergomard, 11.09.24: Whatever the f*** this means.)
         */
        $user = $this->user;

        if ($this->getParticipantData()) {
            $usrData = $this->getParticipantData()->getUserDataByActiveId($active_fi);
            $user = new ilObjUser();
            $user->setFirstname($usrData['firstname']);
            $user->setLastname($usrData['lastname']);
            $user->setMatriculation($usrData['matriculation']);
            $user->setFirstname($usrData['firstname']);
        }

        $this->appendToArchiveDataIndex(
            date('c'),
            $active_fi,
            $pass,
            $user->getFirstname(),
            $user->getLastname(),
            $user->getMatriculation()
        );
        $material_directory = $this->getPassMaterialsDirectory($active_fi, $pass);
        mkdir($material_directory, 0777, true);
        return $material_directory;
    }

    protected function getPassMaterialsDirectory(int $active_fi, int $pass): string
    {
        $pass_data_directory = $this->getPassDataDirectory($active_fi, $pass);
        return $pass_data_directory . self::DIR_SEP . self::PASS_MATERIALS_PATH_COMPONENT;
    }

    protected function ensurePassMaterialsDirectoryIsAvailable(int $active_fi, int $pass): void
    {
        if (!$this->hasPassMaterialsDirectory($active_fi, $pass)) {
            $this->createPassMaterialsDirectory($active_fi, $pass);
        }
    }

    protected function readArchiveDataIndex(): array
    {
        /**
         * The Archive Data Index is a csv-file containing the following columns
         * <active_fi>|<pass>|<yyyy>|<mm>|<dd>|<directory>
         */
        $data_index_file = $this->getTestArchive() . self::DIR_SEP . self::DATA_INDEX_FILENAME;

        $contents = [];

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if (@file_exists($data_index_file)) {
            $lines = explode("\n", file_get_contents($data_index_file));
            foreach ($lines as $line) {
                if (strlen($line) === 0) {
                    continue;
                }
                $line_items = explode('|', $line);
                $line_data = [];
                $line_data['identifier'] = $line_items[0] . '|' . $line_items[1];
                $line_data['yyyy'] = $line_items[2];
                $line_data['mm'] = $line_items[3];
                $line_data['dd'] = $line_items[4];
                $line_data['directory'] = $line_items[5];
                $contents[] = $line_data;
            }
        }
        return $contents;
    }

    protected function appendToArchiveDataIndex(
        string $date,
        int $active_fi,
        int $pass,
        string $user_firstname,
        string $user_lastname,
        string $matriculation
    ): void {
        $line = $this->determinePassDataPath($date, $active_fi, $pass, $user_firstname, $user_lastname, $matriculation);

        $this->archive_data_index[] = $line;
        $output_contents = '';

        foreach ($this->archive_data_index as $line_data) {
            if ($line_data['identifier'] == "|") {
                continue;
            }
            $output_contents .= implode('|', $line_data) . "\n";
        }

        file_put_contents($this->getTestArchive() . self::DIR_SEP . self::DATA_INDEX_FILENAME, $output_contents);
        $this->readArchiveDataIndex();
        return;
    }

    private function determinePassDataPath(
        string $date,
        int $active_fi,
        int $pass,
        string $user_firstname,
        string $user_lastname,
        string $matriculation
    ): array {
        $parsed_date = date_create_from_format('Y-m-d\TH:i:sP', $date);
        if (!$parsed_date) {
            throw new Exception('Invalid date format. Expected ISO 8601 format.');
        }

        $line = [
            'identifier' => $active_fi . '|' . $pass,
            'yyyy' => date_format($parsed_date, 'Y'),
            'mm' => date_format($parsed_date, 'm'),
            'dd' => date_format($parsed_date, 'd'),
            'directory' => $active_fi . '_' . $pass . '_' . $user_firstname . '_' . $user_lastname . '_' . $matriculation
        ];
        return $line;
    }

    private function createUserResultsForArchive(
        \ilObjTest $test_obj,
        array $active_ids,
    ): string {
        $template = new ilTemplate('tpl.il_as_tst_participants_result_output.html', true, true, 'components/ILIAS/Test');

        $participant_data = new ilTestParticipantData($this->db, $this->lng);
        $participant_data->setParticipantAccessFilter(
            $this->participant_access_filter_factory->getAccessResultsUserFilter($test_obj->getRefId())
        );
        $participant_data->setActiveIdsFilter($active_ids);
        $participant_data->load($test_obj->getTestId());

        $test_session_factory = new ilTestSessionFactory($test_obj, $this->db, $this->user);

        $count = 0;
        foreach ($active_ids as $active_id) {
            if (!in_array($active_id, $participant_data->getActiveIds())) {
                continue;
            }

            $count++;
            $results = '';
            if ($active_id > 0) {
                $results = $this->getResultsOfUserOutput(
                    $test_obj,
                    $test_session_factory->getSession($active_id),
                    $participant_data->getUserDataByActiveId($active_id),
                    (int) $active_id,
                    ilObjTest::_getResultPass($active_id)
                );
            }
            if ($count < count($active_ids)) {
                $template->touchBlock('break');
            }
            $template->setCurrentBlock('user_result');
            $template->setVariable('USER_RESULT', $results);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    public function getResultsOfUserOutput(
        \ilObjTest $test_obj,
        ilTestSession $test_session,
        array $participant_data,
        int $active_id,
        int $attempt
    ): string {
        $template = new ilTemplate('tpl.il_as_tst_results_participant.html', true, true, 'components/ILIAS/Test');

        $uname = "{$participant_data['firstname']} {$participant_data['lastname']}";
        if ($test_obj->getAnonymity()) {
            $uname = $this->lng->txt('anonymous');
        }

        $test_result_title_builder = new ResultsTitleBuilder($this->lng, $this->obj_cache);

        $result_array = $test_obj->getTestResult(
            $active_id,
            $attempt,
            false,
            true
        );

        $table = $this->ui_factory->table()->data(
            $this->getDataRetrievalForAttemptOverviewTable($result_array),
            $test_result_title_builder->getPassDetailsHeaderLabel($attempt + 1),
            $this->getColumnsForAttemptOverviewTable($test_obj->isOfferingQuestionHintsEnabled()),
        )->withRequest($this->request);
        $template->setVariable(
            'PASS_DETAILS',
            $this->ui_renderer->render($table)
        );

        if ($test_obj->isShowExamIdInTestResultsEnabled()) {
            $template->setCurrentBlock('exam_id_footer');
            $template->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $test_session->getActiveId(),
                $attempt
            ));
            $template->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $template->parseCurrentBlock();
        }

        $template->setCurrentBlock('participant_block_id');
        $template->setVariable('PARTICIPANT_BLOCK_ID', "participant_active_{$active_id}");
        $template->parseCurrentBlock();

        $template->setVariable('TEXT_HEADING', sprintf($this->lng->txt('tst_result_user_name'), $uname));

        if ($participant_data['matriculation'] !== '') {
            $template->setVariable('USER_DATA', "{$this->lng->txt('matriculation')}: {$participant_data['matriculation']}");
        }

        $results = $test_obj->getResultsForActiveId($active_id);
        $status = $this->lng->txt($results['passed'] ? 'passed_official' : 'failed_official');
        $template->setVariable(
            'GRADING_MESSAGE',
            "{$this->lng->txt('passed_status')}: {$status}<br>"
            . "{$this->lng->txt('tst_mark')}: {$results['mark_official']}"
        );

        $template->setVariable('PASS_FINISH_DATE_LABEL', $this->lng->txt('tst_pass_finished_on'));
        $template->setVariable(
            'PASS_FINISH_DATE_VALUE',
            (new \DateTimeImmutable('@' . ilObjTest::lookupLastTestPassAccess($active_id, $attempt)))
                ->setTimezone(new DateTimeZone($this->user->getTimeZone()))
                ->format($this->user->getDateTimeFormat()->toString())
        );

        return $template->get();
    }

    private function getColumnsForAttemptOverviewTable(
        bool $show_requested_hints_info
    ): array {
        $cf = $this->ui_factory->table()->column();
        $columns = [
            'order' => $cf->number($this->lng->txt('order')),
            'question_id' => $cf->number($this->lng->txt('question_id')),
            'title' => $cf->text($this->lng->txt('tst_question_title')),
            'reachable_points' => $cf->number($this->lng->txt('tst_maximum_points')),
            'reached_points' => $cf->number($this->lng->txt('tst_reached_points'))
        ];
        if ($show_requested_hints_info) {
            $columns['hints'] = $cf->number($this->lng->txt('tst_question_hints_requested_hint_count_header'));
        }
        $columns['solved'] = $cf->text($this->lng->txt('tst_percent_solved'));
        return $columns;
    }

    private function getDataRetrievalForAttemptOverviewTable(array $result_data): DataRetrieval
    {
        return new class ($result_data) implements DataRetrieval {
            public function __construct(
                private readonly array $result_data
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
                $i = 1;
                foreach ($this->result_data as $result) {
                    if (!isset($result['qid'])) {
                        continue;
                    }
                    yield $row_builder->buildDataRow(
                        (string) $result['qid'],
                        [
                            'order' => $i++,
                            'question_id' => $result['qid'],
                            'title' => $result['title'],
                            'reachable_points' => $result['max'],
                            'reached_points' => $result['reached'],
                            'hints' => $result['requested_hints'] ?? 0,
                            'solved' => $result['percent']
                        ]
                    );
                }
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->result_data);
            }
        };
    }

    private function logArchivingProcess(string $message): void
    {
        $archive = $this->getTestArchive() . self::DIR_SEP . self::ARCHIVE_LOG;
        if (file_exists($archive)) {
            $content = file_get_contents($archive) . "\n" . $message;
        } else {
            $content = $message;
        }

        file_put_contents($archive, $content);
    }
}
