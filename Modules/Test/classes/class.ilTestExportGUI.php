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

/**
 * Export User Interface Class
 *
 * @author       Michael Jansen <mjansen@databay.de>
 * @author       Maximilian Becker <mbecker@databay.de>
 *
 * @version      $Id$
 *
 * @ingroup      ModulesTest
 *
 * @ilCtrl_Calls ilTestExportGUI: ilParticipantsTestResultsGUI
 */
class ilTestExportGUI extends ilExportGUI
{
    public function __construct($a_parent_gui, $a_main_obj = null)
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        parent::__construct($a_parent_gui, $a_main_obj);

        #$this->addFormat('xml', $a_parent_gui->lng->txt('ass_create_export_file'), $this, 'createTestExport');
        $this->addFormat('xml', $a_parent_gui->lng->txt('ass_create_export_file'));
        $this->addFormat('xmlres', $a_parent_gui->lng->txt('ass_create_export_file_with_results'), $this, 'createTestExportWithResults');
        $this->addFormat('csv', $a_parent_gui->lng->txt('ass_create_export_test_results'), $this, 'createTestResultsExport');
        $this->addFormat('arc', $a_parent_gui->lng->txt('ass_create_export_test_archive'), $this, 'createTestArchiveExport');
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'Test', 'texp');
        foreach ($pl_names as $pl) {
            /**
             * @var $plugin ilTestExportPlugin
             */
            $plugin = ilPluginAdmin::getPluginObject(IL_COMP_MODULE, 'Test', 'texp', $pl);
            $plugin->setTest($this->obj);
            $this->addFormat(
                $plugin->getFormat(),
                $plugin->getFormatLabel(),
                $plugin,
                'export'
            );
        }
    }

    protected function buildExportTableGUI() : ilTestExportTableGUI
    {
        require_once 'Modules/Test/classes/tables/class.ilTestExportTableGUI.php';
        $table = new ilTestExportTableGUI($this, 'listExportFiles', $this->obj);
        return $table;
    }

    /**
     * Create test export file
     */
    public function createTestExportWithResults()
    {
        /**
         * @var $lng ilLanguage
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        require_once 'Modules/Test/classes/class.ilTestExportFactory.php';
        $expFactory = new ilTestExportFactory($this->obj);
        $test_exp = $expFactory->getExporter('xml');
        $test_exp->setResultExportingEnabledForTestExport(true);
        $test_exp->buildExportFile();
        ilUtil::sendSuccess($lng->txt('exp_file_created'), true);
        $ilCtrl->redirectByClass('iltestexportgui');
    }

    /**
     * Create results export file
     */
    public function createTestResultsExport()
    {
        /**
         * @var $lng ilLanguage
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        require_once 'Modules/Test/classes/class.ilTestExportFactory.php';
        $expFactory = new ilTestExportFactory($this->obj);
        $test_exp = $expFactory->getExporter('results');
        $test_exp->buildExportFile();
        ilUtil::sendSuccess($lng->txt('exp_file_created'), true);
        $ilCtrl->redirectByClass('iltestexportgui');
    }

    public function createTestArchiveExport()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        if ($ilAccess->checkAccess("write", "", $this->obj->ref_id)) {
            // prepare generation before contents are processed (for mathjax)
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);

            require_once 'Modules/Test/classes/class.ilTestEvaluation.php';
            $evaluation = new ilTestEvaluation($ilDB, $this->obj->getTestId());
            $allActivesPasses = $evaluation->getAllActivesPasses();

            $participantData = new ilTestParticipantData($ilDB, $lng);
            $participantData->setActiveIdsFilter(array_keys($allActivesPasses));
            $participantData->load($this->obj->getTestId());

            require_once 'Modules/Test/classes/class.ilTestArchiveService.php';
            $archiveService = new ilTestArchiveService($this->obj);
            $archiveService->setParticipantData($participantData);
            $archiveService->archivePassesByActives($allActivesPasses);

            include_once("./Modules/Test/classes/class.ilTestArchiver.php");
            $test_id = $this->obj->getId();
            $archive_exp = new ilTestArchiver($test_id);

            require_once './Modules/Test/classes/class.ilTestScoring.php';
            $scoring = new ilTestScoring($this->obj);
            $best_solution = $scoring->calculateBestSolutionForTest();

            $tmpFileName = ilUtil::ilTempnam();
            if (!is_dir($tmpFileName)) {
                ilUtil::makeDirParents($tmpFileName);
            }

            $directory_name = realpath($tmpFileName);
            $file_name = $directory_name . DIRECTORY_SEPARATOR . 'Best_Solution.pdf';

            require_once './Modules/Test/classes/class.ilTestPDFGenerator.php';
            $generator = new ilTestPDFGenerator();
            $generator->generatePDF($best_solution, ilTestPDFGenerator::PDF_OUTPUT_FILE, $file_name, PDF_USER_RESULT);
            $archive_exp->handInTestBestSolution($best_solution, $file_name);
            ilUtil::delDir($directory_name);

            $archive_exp->updateTestArchive();
            $archive_exp->compressTestArchive();
        } else {
            ilUtil::sendInfo("cannot_export_archive", true);
        }
        $ilCtrl->redirectByClass('iltestexportgui');
    }

    public function listExportFiles()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));

        if (count($this->getFormats()) > 1) {
            foreach ($this->getFormats() as $f) {
                $options[$f["key"]] = $f["txt"];
            }
            include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
            $si = new ilSelectInputGUI($lng->txt("type"), "format");
            $si->setOptions($options);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->addFormButton($lng->txt("exp_create_file"), "createExportFile");
        } else {
            $format = $this->getFormats();
            $format = $format[0];
            $ilToolbar->addFormButton($lng->txt("exp_create_file") . " (" . $format["txt"] . ")", "create_" . $format["key"]);
        }

        require_once 'class.ilTestArchiver.php';
        $archiver = new ilTestArchiver($this->getParentGUI()->object->getId());
        $archive_dir = $archiver->getZipExportDirectory();
        $archive_files = array();

        if (file_exists($archive_dir) && is_dir($archive_dir)) {
            $archive_files = scandir($archive_dir);
        }

        $export_dir = $this->obj->getExportDirectory();
        $export_files = $this->obj->getExportFiles($export_dir);
        $data = array();
        if (count($export_files) > 0) {
            foreach ($export_files as $exp_file) {
                $file_arr = explode("__", $exp_file);
                if ($file_arr[0] == $exp_file) {
                    continue;
                }

                array_push($data, array(
                    'file' => $exp_file,
                    'size' => filesize($export_dir . "/" . $exp_file),
                    'timestamp' => $file_arr[0]
                ));
            }
        }

        if (count($archive_files) > 0) {
            foreach ($archive_files as $exp_file) {
                if ($exp_file == '.' || $exp_file == '..') {
                    continue;
                }
                $file_arr = explode("_", $exp_file);

                $data[] = [
                    'file' => $exp_file,
                    'size' => filesize($archive_dir . "/" . $exp_file),
                    'timestamp' => $file_arr[4],
                ];
            }
        }

        $table = $this->buildExportTableGUI();
        $table->setSelectAllCheckbox("file");
        foreach ($this->getCustomColumns() as $c) {
            $table->addCustomColumn($c["txt"], $c["obj"], $c["func"]);
        }

        foreach ($this->getCustomMultiCommands() as $c) {
            $table->addCustomMultiCommand($c["txt"], "multi_" . $c["func"]);
        }

        $table->resetFormats();
        foreach ($this->formats as $format) {
            $table->addFormat($format['key']);
        }

        $table->setData($data);
        $this->tpl->setOnScreenMessage('info', $lng->txt('no_manual_feedback_export_info'), true);
        $tpl->setContent($table->getHTML());
    }

    public function download()
    {
        /**
         * @var $lng ilLanguage
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if (isset($_GET['file']) && $_GET['file']) {
            $_POST['file'] = array($_GET['file']);
        }

        if (!isset($_POST['file'])) {
            ilUtil::sendInfo($lng->txt('no_checkbox'), true);
            $ilCtrl->redirect($this, 'listExportFiles');
        }

        if (count($_POST['file']) > 1) {
            ilUtil::sendInfo($lng->txt('select_max_one_item'), true);
            $ilCtrl->redirect($this, 'listExportFiles');
        }

        require_once 'class.ilTestArchiver.php';
        $archiver = new ilTestArchiver($this->getParentGUI()->object->getId());

        $filename = basename($_POST["file"][0]);
        $exportFile = $this->obj->getExportDirectory() . '/' . $filename;
        $archiveFile = $archiver->getZipExportDirectory() . '/' . $filename;

        if (file_exists($exportFile)) {
            ilUtil::deliverFile($exportFile, $filename);
        }

        if (file_exists($archiveFile)) {
            ilUtil::deliverFile($archiveFile, $filename);
        }

        $ilCtrl->redirect($this, 'listExportFiles');
    }

    /**
     * Delete files
     */
    public function delete()
    {
        /**
         * @var $lng ilLanguage
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        require_once 'class.ilTestArchiver.php';
        $archiver = new ilTestArchiver($this->getParentGUI()->object->getId());
        $archiveDir = $archiver->getZipExportDirectory();

        $export_dir = $this->obj->getExportDirectory();
        foreach ($_POST['file'] as $file) {
            $file = basename($file);
            $dir = substr($file, 0, strlen($file) - 4);

            if (!strlen($file) || !strlen($dir)) {
                continue;
            }

            $exp_file = $export_dir . '/' . $file;
            $arc_file = $archiveDir . '/' . $file;
            $exp_dir = $export_dir . '/' . $dir;
            if (@is_file($exp_file)) {
                unlink($exp_file);
            }
            if (@is_file($arc_file)) {
                unlink($arc_file);
            }
            if (@is_dir($exp_dir)) {
                ilUtil::delDir($exp_dir);
            }
        }
        ilUtil::sendSuccess($lng->txt('msg_deleted_export_files'), true);
        $ilCtrl->redirect($this, 'listExportFiles');
    }
}
