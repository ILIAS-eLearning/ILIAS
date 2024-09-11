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

use ILIAS\Test\RequestDataCollector;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\ResourceStorage\Services as IRSS;

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

    protected const TEST_RESULT_FILENAME = 'test_result.html';

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

    protected $external_directory_path;
    protected $client_id = CLIENT_ID;
    protected $archive_data_index;

    protected ilTestHTMLGenerator $html_generator;

    protected ?ilTestParticipantData $participant_data = null;

    public function __construct(
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilObjUser $user,
        private readonly ilTabsGUI $tabs,
        private readonly ilToolbarGUI $toolbar,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly GlobalHttpState $http,
        private readonly RefineryFactory $refinery,
        private readonly ilAccess $access,
        private readonly IRSS $irss,
        private readonly GeneralQuestionPropertiesRepository $questionrepository,
        private readonly RequestDataCollector $testrequest,
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
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $this->db->fetchAssoc($result)) {
            $outfile_lines .= "\r\n" . implode("\t", $row);
        }
        file_put_contents($this->getTestArchive() . self::DIR_SEP . self::TEST_LOG_FILENAME, $outfile_lines);

        // Generate test pass overview
        $test = new ilObjTest($this->test_obj_id, false);
        if ($this->test_ref_id !== null) {
            $test->setRefId($this->test_ref_id);
        }

        $gui = new ilParticipantsTestResultsGUI(
            $this->ctrl,
            $this->lng,
            $this->db,
            $this->user,
            $this->tabs,
            $this->toolbar,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            new ilTestParticipantAccessFilterFactory($this->access),
            $this->questionrepository,
            $this->testrequest,
            $this->http,
            $this->refinery
        );
        $gui->setTestObj($test);

        $objectiveOrientedContainer = new ilTestObjectiveOrientedContainer();
        $gui->setObjectiveParent($objectiveOrientedContainer);
        $array_of_actives = [];
        $participants = $test->getParticipants();

        foreach ($participants as $key => $value) {
            $array_of_actives[] = $key;
        }
        $output_template = $gui->createUserResults(true, false, true, $array_of_actives);

        $filename = realpath($this->getTestArchive()) . self::DIR_SEP . 'participant_pass_overview.html';
        $this->html_generator->generateHTML($output_template->get(), $filename);

        return;
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

    protected function determinePassDataPath(
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

    protected function logArchivingProcess(string $message): void
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
