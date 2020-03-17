<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestArchiver
 *
 * Helper class to deal with the generation and maintenance of test archives.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
class ilTestArchiver
{
    #region Constants / Config

    const DIR_SEP = '/';

    const HTML_SUBMISSION_FILENAME = 'test_submission.html';
    const PDF_SUBMISSION_FILENAME = 'test_submission.pdf';
    const PASS_MATERIALS_PATH_COMPONENT = 'materials';
    const QUESTION_PATH_COMPONENT_PREFIX = 'q_';

    const TEST_BEST_SOLUTION_PATH_COMPONENT = 'best_solution';
    const HTML_BEST_SOLUTION_FILENAME = 'best_solution.html';
    const PDF_BEST_SOLUTION_FILENAME = 'best_solution.pdf';
    const TEST_MATERIALS_PATH_COMPONENT = 'materials';

    const TEST_RESULT_FILENAME = 'test_result_v';
    const TEST_RESULT_POSTFIX = '.pdf';

    const TEST_OVERVIEW_PDF_FILENAME = 'results_overview_html_v';
    const TEST_OVERVIEW_PDF_POSTFIX = '.pdf';

    const TEST_OVERVIEW_HTML_FILENAME = 'results_overview_pdf_v';
    const TEST_OVERVIEW_HTML_POSTFIX = '.html';

    const LOG_DTSGROUP_FORMAT = 'D M j G:i:s T Y';
    const LOG_ADDITION_STRING = ' Adding ';
    const LOG_CREATION_STRING = ' Creating ';
    const LOG_UPDATE_STRING = ' Updating ';
    const LOG_DELETION_STRING = ' Deleting ';

    const TEST_LOG_FILENAME = 'test.log';
    const DATA_INDEX_FILENAME = 'data_index.csv';
    const ARCHIVE_LOG = 'archive.log';

    const EXPORT_DIRECTORY = 'archive_exports';

    #endregion

    /*
     * Test-Archive Schema:
     *
     * <external directory>/<client>/tst_data/archive/tst_<obj_id>/
     * 		- archive_data_index.dat
     * 		- archive_log.log
     *
     * 		- test_log.log
     *
     * 		- test_results_v<n>.pdf
     * 		- test_results_v<n>.csv
     *
     * 		-> best_solution/
     * 			best_solution_v<n>.pdf
     * 			-> /materials/q_<question_fi>/<n>_<filename>
     *
     * 		-> <year>/<month>/<day>/<ActiveFi>_<Pass>[_<Lastname>][_<Firstname>][_<Matriculation>]/
     * 			-> test_submission.pdf
     * 			-> test_submission.html
     * 			-> test_submission.sig (et al)
     * 			-> test_result_v<n>.pdf
     * 			-> /materials_v<n>/<question_fi>/<n>_<filename>
     */

    #region Properties

    protected $external_directory_path;	/** @var $external_directory_path string External directory base path  */
    protected $client_id;			 	/** @var $client_id string Client id of the current client */
    protected $test_obj_id;				/** @var $test_obj_id integer Object-ID of the test, the archiver is instantiated for */
    protected $archive_data_index;		/** @var $archive_data_index array[string[]] Archive data index as associative array */

    protected $ilDB;					/** @var $ilDB ilDBInterface */
    
    /**
     * @var ilTestParticipantData
     */
    protected $participantData;

    #endregion

    /**
     * Returns a new ilTestArchiver object
     *
     * @param $test_obj_id integer Object-ID of the test, the archiver is instantiated for.
     */
    public function __construct($test_obj_id)
    {
        /** @var $ilias ILIAS */
        global $DIC;
        $ilias = $DIC['ilias'];
        $this->external_directory_path = $ilias->ini_ilias->readVariable('clients', 'datadir');
        $this->client_id = $ilias->client_id;
        $this->test_obj_id = $test_obj_id;
        $this->ilDB = $ilias->db;

        $this->archive_data_index = $this->readArchiveDataIndex();
        
        $this->participantData = null;
    }
    
    /**
     * @return ilTestParticipantData
     */
    public function getParticipantData()
    {
        return $this->participantData;
    }
    
    /**
     * @param ilTestParticipantData $participantData
     */
    public function setParticipantData($participantData)
    {
        $this->participantData = $participantData;
    }

    #region API methods

    /**
     * Hands in a participants test submission ("a completed test") for archiving.
     *
     * The archive takes an html-string and a path to a PDF-file and saves it according to the archives
     * general structure. The test is identified by active_fi and pass number, allowing to store relevant
     * files even for anonymous tests.
     *
     * @param $active_fi	integer	Active-FI of the test participant
     * @param $pass			integer	Pass-number of the actual test
     * @param $html_string	string	HTML-string of the test submission
     * @param $pdf_path		string	Path to a pdf representation of the test submission.
     */
    public function handInParticipantSubmission($active_fi, $pass, $pdf_path, $html_string)
    {
        $this->ensureTestArchiveIsAvailable();
        $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);

        $pdf_new_path = $this->getPassDataDirectory($active_fi, $pass) . self::DIR_SEP
            . self::PDF_SUBMISSION_FILENAME;
        copy($pdf_path, $pdf_new_path);
        # /home/mbecker/public_html/ilias/trunk-primary/extern/default/tst_data/archive/tst_350/2013/09/19/80_1_root_user_/test_submission.pdf
        $html_new_path = $this->getPassDataDirectory($active_fi, $pass) . self::DIR_SEP
            . self::HTML_SUBMISSION_FILENAME;
        file_put_contents($html_new_path, $html_string);

        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $pdf_new_path);
        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $html_new_path);
    }

    /**
     * Hands in a particpants question material, such as an upload or other binary content.
     *
     * @param $active_fi			integer Active-FI of the test participant
     * @param $pass					integer	Pass-number of the actual test
     * @param $question_fi			integer Question-FI of the question, the file is to be stored for.
     * @param $original_filename	string  Original filename of the material to be stored.
     * @param $file_path			string	Location of the file to be archived
     */
    public function handInParticipantQuestionMaterial($active_fi, $pass, $question_fi, $original_filename, $file_path)
    {
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

    /**
     * Hands in a participants file, which is relevant for archiving but an unspecified type.
     *
     * Examples for such are signature files, remarks, feedback or the like.
     *
     * @param $active_fi			integer Active-FI of the test participant
     * @param $pass					integer	Pass-number of the actual test
     * @param $original_filename	string  Original filename of the material to be stored.
     * @param $file_path			string	Location of the file to be archived
     */
    public function handInParticipantMisc($active_fi, $pass, $original_filename, $file_path)
    {
        $this->ensureTestArchiveIsAvailable();
        $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);
        $new_path = $this->getPassDataDirectory($active_fi, $pass) . self::DIR_SEP . $original_filename;
        copy($file_path, $new_path);
        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $new_path);
    }

    /**
     * Hands in the best solution for a test.
     *
     * @param $html_string	string	HTML-string of the test submission
     * @param $pdf_path		string	Path to a pdf representation of the test submission.
     */
    public function handInTestBestSolution($html_string, $pdf_path)
    {
        $this->ensureTestArchiveIsAvailable();
        
        $best_solution_path = $this->getTestArchive() . self::DIR_SEP . self::TEST_BEST_SOLUTION_PATH_COMPONENT;
        if (!is_dir($best_solution_path)) {
            mkdir($best_solution_path, 0777, true);
        }

        file_put_contents($best_solution_path . self::DIR_SEP . self::HTML_BEST_SOLUTION_FILENAME, $html_string);

        copy($pdf_path, $best_solution_path . self::DIR_SEP . self::PDF_BEST_SOLUTION_FILENAME);

        $this->logArchivingProcess(
            date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING
                . $best_solution_path . self::DIR_SEP . self::HTML_BEST_SOLUTION_FILENAME
        );

        $this->logArchivingProcess(
            date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING
                . $best_solution_path . self::DIR_SEP . self::PDF_BEST_SOLUTION_FILENAME
        );
    }

    /**
     * Hands in a file related to a question in context of the best solution.
     *
     * @param $question_fi			integer QuestionFI of the question, material is to be stored for.
     * @param $orginial_filename	string	Original filename of the material to be stored.
     * @param $file_path			string  Path to the material to be stored.
     */
    public function handInBestSolutionQuestionMaterial($question_fi, $orginial_filename, $file_path)
    {
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

    /**
     * Hands in an individual test result for a pass.
     *
     * @param $active_fi	integer ActiveFI of the participant.
     * @param $pass			integer	Pass of the test.
     * @param $pdf_path		string 	Path to the PDF containing the result.
     *
     * @return void
     */
    public function handInTestResult($active_fi, $pass, $pdf_path)
    {
        $this->ensureTestArchiveIsAvailable();
        $this->ensurePassDataDirectoryIsAvailable($active_fi, $pass);
        $new_path = $this->getPassDataDirectory($active_fi, $pass) . self::DIR_SEP
            . self::TEST_RESULT_FILENAME . ($this->countFilesInDirectory($this->getPassDataDirectory($active_fi, $pass), self::TEST_RESULT_FILENAME))
            . self::TEST_RESULT_POSTFIX;
        copy($pdf_path, $new_path);
        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $new_path);
    }

    /**
     * Hands in a test results overview.
     *
     * @param $html_string	string HTML of the test results overview.
     * @param $pdf_path		string Path
     */
    public function handInTestResultsOverview($html_string, $pdf_path)
    {
        $this->ensureTestArchiveIsAvailable();
        $new_pdf_path = $this->getTestArchive() . self::DIR_SEP
            . self::TEST_OVERVIEW_PDF_FILENAME
            . $this->countFilesInDirectory($this->getTestArchive(), self::TEST_OVERVIEW_PDF_FILENAME) . self::TEST_OVERVIEW_PDF_POSTFIX;
        copy($pdf_path, $new_pdf_path);
        $html_path = $this->getTestArchive() . self::DIR_SEP . self::TEST_OVERVIEW_HTML_FILENAME
            . $this->countFilesInDirectory($this->getTestArchive(), self::TEST_OVERVIEW_HTML_FILENAME) . self::TEST_OVERVIEW_HTML_POSTFIX;
        file_put_contents($html_path, $html_string);

        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $new_pdf_path);
        $this->logArchivingProcess(date(self::LOG_DTSGROUP_FORMAT) . self::LOG_ADDITION_STRING . $html_path);
    }

    #endregion

    #region TestArchive
    // The TestArchive lives here: <external directory>/<client>/tst_data/archive/tst_<obj_id>/

    /**
     * Returns if the archive directory structure for the test the object is created for exists.
     *
     * @return bool $hasTestArchive True, if the archive directory structure exists.
     */
    protected function hasTestArchive()
    {
        return is_dir($this->getTestArchive());
    }

    /**
     * Creates the directory for the test archive.
     */
    protected function createArchiveForTest()
    {
        ilUtil::makeDirParents($this->getTestArchive());
        //mkdir( $this->getTestArchive(), 0777, true );
    }

    /**
     * Returns the (theoretical) path to the archive directory of the test, this object is created for.
     *
     * @return string $test_archive Path to this tests archive directory.
     */
    protected function getTestArchive()
    {
        $test_archive_directory = $this->external_directory_path . self::DIR_SEP . $this->client_id . self::DIR_SEP . 'tst_data'
            . self::DIR_SEP . 'archive' . self::DIR_SEP . 'tst_' . $this->test_obj_id;
        return $test_archive_directory;
    }

    /**
     * Ensures the availability of the test archive directory.
     *
     * Checks if the directory exists and creates it if necessary.
     *
     * @return void
     */
    protected function ensureTestArchiveIsAvailable()
    {
        if (!$this->hasTestArchive()) {
            $this->createArchiveForTest();
        }
        return;
    }

    /**
     * Replaces the test-log with the current one.
     *
     * @return void
     */
    public function updateTestArchive()
    {
        $query = 'SELECT * FROM ass_log WHERE obj_fi = ' . $this->ilDB->quote($this->test_obj_id, 'integer');
        $result = $this->ilDB->query($query);

        $outfile_lines = '';
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $this->ilDB->fetchAssoc($result)) {
            $outfile_lines .= "\r\n" . implode("\t", $row);
        }
        file_put_contents($this->getTestArchive() . self::DIR_SEP . self::TEST_LOG_FILENAME, $outfile_lines);

        // Generate test pass overview
        $test = new ilObjTest($this->test_obj_id, false);
        require_once 'Modules/Test/classes/class.ilParticipantsTestResultsGUI.php';
        $gui = new ilParticipantsTestResultsGUI();
        $gui->setTestObj($test);
        require_once 'Modules/Test/classes/class.ilTestObjectiveOrientedContainer.php';
        $objectiveOrientedContainer = new ilTestObjectiveOrientedContainer();
        $gui->setObjectiveParent($objectiveOrientedContainer);
        $array_of_actives = array();
        $participants = $test->getParticipants();

        foreach ($participants as $key => $value) {
            $array_of_actives[] = $key;
        }
        $output_template = $gui->createUserResults(true, false, true, $array_of_actives);

        require_once 'Modules/Test/classes/class.ilTestPDFGenerator.php';
        $filename = realpath($this->getTestArchive()) . self::DIR_SEP . 'participant_pass_overview.pdf';
        ilTestPDFGenerator::generatePDF($output_template->get(), ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename, PDF_USER_RESULT);
        
        return;
    }

    public function ensureZipExportDirectoryExists()
    {
        if (!$this->hasZipExportDirectory()) {
            $this->createZipExportDirectory();
        }
    }

    /**
     * Returns if the export directory for zips exists.
     *
     * @return bool
     */
    public function hasZipExportDirectory()
    {
        return is_dir($this->getZipExportDirectory());
    }

    protected function createZipExportDirectory()
    {
        mkdir($this->getZipExportDirectory(), 0777, true);
    }

    /**
     * Return the export directory, where zips are placed.
     *
     * @return string
     */
    public function getZipExportDirectory()
    {
        return $this->external_directory_path . self::DIR_SEP . $this->client_id . self::DIR_SEP . 'tst_data'
            . self::DIR_SEP . self::EXPORT_DIRECTORY . self::DIR_SEP . 'tst_' . $this->test_obj_id;
    }

    /**
     * Generate the test archive for download.
     *
     * @return void
     */
    public function compressTestArchive()
    {
        $this->updateTestArchive();
        $this->ensureZipExportDirectoryExists();
        
        $zip_output_path = $this->getZipExportDirectory();
        $zip_output_filename = 'test_archive_obj_' . $this->test_obj_id . '_' . time() . '_.zip';
        
        ilUtil::zip($this->getTestArchive(), $zip_output_path . self::DIR_SEP . $zip_output_filename, true);
        return;
    }

    #endregion

    #region PassDataDirectory
    // The pass data directory contains all data relevant for a participants pass.
    // In addition to the test-archive-directory, this directory lives here:
    // .../<year>/<month>/<day>/<ActiveFi>_<Pass>[_<Lastname>][_<Firstname>][_<Matriculation>]/
    // Lastname, Firstname and Matriculation are not mandatory in the directory name.

    /**
     * Checks if the directory for pass data is available.
     *
     * @param $active_fi	integer ActiveFI of the pass.
     * @param $pass			integer Pass-number of the pass.
     *
     * @return bool $hasPassDataDirectory True, if the pass data directory exists.
     */
    protected function hasPassDataDirectory($active_fi, $pass)
    {
        $pass_data_dir = $this->getPassDataDirectory($active_fi, $pass);
        return is_dir($this->getPassDataDirectory($active_fi, $pass));
    }

    /**
     * Creates pass data directory
     *
     * @param $active_fi integer ActiveFI of the participant.
     * @param $pass		 integer Pass number of the test.
     *
     * @return void
     */
    protected function createPassDataDirectory($active_fi, $pass)
    {
        mkdir($this->getPassDataDirectory($active_fi, $pass), 0777, true);
        return;
    }
    
    private function buildPassDataDirectory($active_fi, $pass)
    {
        foreach ($this->archive_data_index as $data_index_entry) {
            if ($data_index_entry != null && $data_index_entry['identifier'] == $active_fi . '|' . $pass) {
                array_shift($data_index_entry);
                return $this->getTestArchive() . self::DIR_SEP . implode(self::DIR_SEP, $data_index_entry);
            }
        }
        
        return null;
    }

    /**
     * Returns the pass data directory.
     *
     * @param $active_fi integer ActiveFI of the participant.
     * @param $pass	     integer Pass number of the test.
     *
     * @return string $pass_data_directory Path to the pass data directory.
     */
    protected function getPassDataDirectory($active_fi, $pass)
    {
        $passDataDir = $this->buildPassDataDirectory($active_fi, $pass);
        
        if (!$passDataDir) {
            if ($this->getParticipantData()) {
                $usrData = $this->getParticipantData()->getUserDataByActiveId($active_fi);
                $user = new ilObjUser();
                $user->setFirstname($usrData['firstname']);
                $user->setLastname($usrData['lastname']);
                $user->setMatriculation($usrData['matriculation']);
                $user->setFirstname($usrData['firstname']);
            } else {
                global $DIC;
                $ilUser = $DIC['ilUser'];
                $user = $ilUser;
            }
            
            $this->appendToArchiveDataIndex(
                date(DATE_ISO8601),
                $active_fi,
                $pass,
                $user->getFirstname(),
                $user->getLastname(),
                $user->getMatriculation()
            );
            
            $passDataDir = $this->buildPassDataDirectory($active_fi, $pass);
        }
        
        return $passDataDir;
    }

    /**
     * Ensures the availability of the participant data directory.
     *
     * Checks if the directory exists and creates it if necessary.
     *
     * @param $active_fi	integer Active-FI of the test participant
     * @param $pass			integer Pass-number of the actual test
     *
     * @return void
     */
    protected function ensurePassDataDirectoryIsAvailable($active_fi, $pass)
    {
        if (!$this->hasPassDataDirectory($active_fi, $pass)) {
            $this->createPassDataDirectory($active_fi, $pass);
        }
        return;
    }

    #endregion

    #region PassMaterialsDirectory

    /**
     * Returns if the pass materials directory exists for a given pass.
     *
     * @param $active_fi	integer ActiveFI for the participant.
     * @param $pass			integer Pass number.
     *
     * @return bool			$hasPassmaterialsDirectory True, if the directory exists.
     */
    protected function hasPassMaterialsDirectory($active_fi, $pass)
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if (@is_dir($this->getPassMaterialsDirectory($active_fi, $pass))) {
            return true;
        }
        return false;
    }

    /**
     * Creates pass materials directory.
     *
     * @param $active_fi	integer	ActiveFI of the participant.
     * @param $pass			integer Pass number of the test.
     *
     * @return void
     */
    protected function createPassMaterialsDirectory($active_fi, $pass)
    {
        // Data are taken from the current user as the implementation expects the first interaction of the pass
        // takes place from the usage/behaviour of the current user.
        
        if ($this->getParticipantData()) {
            $usrData = $this->getParticipantData()->getUserDataByActiveId($active_fi);
            $user = new ilObjUser();
            $user->setFirstname($usrData['firstname']);
            $user->setLastname($usrData['lastname']);
            $user->setMatriculation($usrData['matriculation']);
            $user->setFirstname($usrData['firstname']);
        } else {
            global $DIC;
            $ilUser = $DIC['ilUser'];
            $user = $ilUser;
        }

        $this->appendToArchiveDataIndex(
            date('Y'),
            $active_fi,
            $pass,
            $user->getFirstname(),
            $user->getLastname(),
            $user->getMatriculation()
        );
        mkdir($this->getPassMaterialsDirectory($active_fi, $pass), 0777, true);
    }

    /**
     * Returns the pass materials directory.
     *
     * @param $active_fi	integer ActiveFI of the participant.
     * @param $pass			integer Pass number.
     *
     * @return string $pass_materials_directory Path to the pass materials directory.
     */
    protected function getPassMaterialsDirectory($active_fi, $pass)
    {
        $pass_data_directory = $this->getPassMaterialsDirectory($active_fi, $pass);
        return $pass_data_directory . self::DIR_SEP . self::PASS_MATERIALS_PATH_COMPONENT;
    }

    /**
     * Ensures the availability of the pass materials directory.
     *
     * Checks if the directory exists and creates it if necessary.
     *
     * @param $active_fi	integer Active-FI of the test participant
     * @param $pass			integer Pass-number of the actual test
     *
     */
    protected function ensurePassMaterialsDirectoryIsAvailable($active_fi, $pass)
    {
        if (!$this->hasPassMaterialsDirectory($active_fi, $pass)) {
            $this->createPassMaterialsDirectory($active_fi, $pass);
        }
    }

    #endregion

    /**
     * Reads the archive data index.
     *
     * @return array[array] $archive_data_index Archive data index.
     */
    protected function readArchiveDataIndex()
    {
        /**
         * The Archive Data Index is a csv-file containing the following columns
         * <active_fi>|<pass>|<yyyy>|<mm>|<dd>|<directory>
         */
        $data_index_file = $this->getTestArchive() . self::DIR_SEP . self::DATA_INDEX_FILENAME;

        $contents = array();

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if (@file_exists($data_index_file)) {
            $lines = explode("\n", file_get_contents($data_index_file));
            foreach ($lines as $line) {
                $line_items = explode('|', $line);
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

    /**
     * Appends a line to the archive data index.
     *
     * @param $date             string  Date for the directories path.
     * @param $active_fi        integer ActiveFI of the participant.
     * @param $pass             integer Pass number of the participant.
     * @param $user_firstname	string	User firstname.
     * @param $user_lastname	string  User lastname.
     * @param $matriculation    string Matriculation number of the user.
     *
     * @return void
     */
    protected function appendToArchiveDataIndex($date, $active_fi, $pass, $user_firstname, $user_lastname, $matriculation)
    {
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

    /**
     * Determines the pass data path.
     *
     * @param $date
     * @param $active_fi
     * @param $pass
     * @param $user_firstname
     * @param $user_lastname
     * @param $matriculation
     *
     * @return array
     */
    protected function determinePassDataPath($date, $active_fi, $pass, $user_firstname, $user_lastname, $matriculation)
    {
        $date = date_create_from_format(DATE_ISO8601, $date);
        $line = array(
            'identifier' => $active_fi . '|' . $pass,
            'yyyy' => date_format($date, 'Y'),
            'mm' => date_format($date, 'm'),
            'dd' => date_format($date, 'd'),
            'directory' => $active_fi . '_' . $pass . '_' . $user_firstname . '_' . $user_lastname . '_' . $matriculation
        );
        return $line;
    }

    /**
     * Logs to the archive log.
     *
     * @param $message string Complete log message.
     *
     * @return void
     */
    protected function logArchivingProcess($message)
    {
        $archive = $this->getTestArchive() . self::DIR_SEP . self::ARCHIVE_LOG;
        if (file_exists($archive)) {
            $content = file_get_contents($archive) . "\n" . $message;
        } else {
            $content = $message;
        }

        file_put_contents($archive, $content);
    }

    /**
     * Returns the count of files in a directory, eventually matching the given, optional, pattern.
     *
     * @param      		  $directory
     * @param null|string $pattern
     *
     * @return integer
     */
    protected function countFilesInDirectory($directory, $pattern = null)
    {
        $filecount = 0;

        /** @noinspection PhpAssignmentInConditionInspection */
        if ($handle = opendir($directory)) {
            while (($file = readdir($handle)) !== false) {
                if (!in_array($file, array( '.', '..' )) && !is_dir($directory . $file)) {
                    if ($pattern && strpos($file, $pattern) === 0) {
                        $filecount++;
                    }
                }
            }
        }
        return $filecount;
    }
}
