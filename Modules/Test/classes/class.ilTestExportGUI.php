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
    private \ILIAS\Test\InternalRequestService $testrequest;

    public function __construct($a_parent_gui, $a_main_obj = null)
    {
        global $DIC;
        $component_factory = $DIC["component.factory"];
        $this->testrequest = $DIC->test()->internal()->request();
        parent::__construct($a_parent_gui, $a_main_obj);

        #$this->addFormat('xml', $a_parent_gui->lng->txt('ass_create_export_file'), $this, 'createTestExport');
        $this->addFormat('xml', $DIC->language()->txt('ass_create_export_file'));
        $this->addFormat('xmlres', $DIC->language()->txt('ass_create_export_file_with_results'), $this, 'createTestExportWithResults');
        $this->addFormat('csv', $DIC->language()->txt('ass_create_export_test_results'), $this, 'createTestResultsExport');
        $this->addFormat('arc', $DIC->language()->txt('ass_create_export_test_archive'), $this, 'createTestArchiveExport');
        foreach ($component_factory->getActivePluginsInSlot("texp") as $plugin) {
            $plugin->setTest($this->obj);
            $this->addFormat(
                $plugin->getFormat(),
                $plugin->getFormatLabel(),
                $plugin,
                'export'
            );
        }
    }

    /**
     * @return ilTestExportTableGUI
     */
    protected function buildExportTableGUI(): ilExportTableGUI
    {
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

        $expFactory = new ilTestExportFactory($this->obj);
        $test_exp = $expFactory->getExporter('xml');
        $test_exp->setResultExportingEnabledForTestExport(true);
        $test_exp->buildExportFile();
        $this->tpl->setOnScreenMessage('success', $lng->txt('exp_file_created'), true);
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

        $expFactory = new ilTestExportFactory($this->obj);
        $test_exp = $expFactory->getExporter('results');
        $test_exp->buildExportFile();
        $this->tpl->setOnScreenMessage('success', $lng->txt('exp_file_created'), true);
        $ilCtrl->redirectByClass('iltestexportgui');
    }

    public function createTestArchiveExport()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        if ($ilAccess->checkAccess("write", "", $this->obj->getRefId())) {
            // prepare generation before contents are processed (for mathjax)
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);

            $evaluation = new ilTestEvaluation($ilDB, $this->obj->getTestId());
            $allActivesPasses = $evaluation->getAllActivesPasses();

            $participantData = new ilTestParticipantData($ilDB, $lng);
            $participantData->setActiveIdsFilter(array_keys($allActivesPasses));
            $participantData->load($this->obj->getTestId());

            $archiveService = new ilTestArchiveService($this->obj);
            $archiveService->setParticipantData($participantData);
            $archiveService->archivePassesByActives($allActivesPasses);

            $test_id = $this->obj->getId();
            $test_ref = $this->obj->getRefId();
            $archive_exp = new ilTestArchiver($test_id, $test_ref);

            $scoring = new ilTestScoring($this->obj);
            $best_solution = $scoring->calculateBestSolutionForTest();

            $tmpFileName = ilFileUtils::ilTempnam();
            if (!is_dir($tmpFileName)) {
                ilFileUtils::makeDirParents($tmpFileName);
            }

            $directory_name = realpath($tmpFileName);
            $file_name = $directory_name . DIRECTORY_SEPARATOR . 'Best_Solution.pdf';

            $generator = new ilTestPDFGenerator();
            $generator->generatePDF($best_solution, ilTestPDFGenerator::PDF_OUTPUT_FILE, $file_name, PDF_USER_RESULT);
            $archive_exp->handInTestBestSolution($best_solution, $file_name);
            ilFileUtils::delDir($directory_name);

            $archive_exp->updateTestArchive();
            $archive_exp->compressTestArchive();
        } else {
            $this->tpl->setOnScreenMessage('info', "cannot_export_archive", true);
        }
        $ilCtrl->redirectByClass('iltestexportgui');
    }

    public function listExportFiles(): void
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
            $si = new ilSelectInputGUI($lng->txt("type"), "format");
            $si->setOptions($options);
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->addFormButton($lng->txt("exp_create_file"), "createExportFile");
        } else {
            $format = $this->getFormats();
            $format = $format[0];
            $ilToolbar->addFormButton($lng->txt("exp_create_file") . " (" . $format["txt"] . ")", "create_" . $format["key"]);
        }

        $archiver = new ilTestArchiver($this->getParentGUI()->getTestObject()->getId());
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
                    'timestamp' => $file_arr[0],
                    'type' => $this->getExportTypeFromFileName($exp_file)
                ));
            }
        }

        if (count($archive_files) > 0) {
            foreach ($archive_files as $exp_file) {
                if ($exp_file == '.' || $exp_file == '..') {
                    continue;
                }
                $file_arr = explode("_", $exp_file);
                array_push($data, array(
                                    'file' => $exp_file,
                                    'size' => filesize($archive_dir . "/" . $exp_file),
                                    'timestamp' => $file_arr[4],
                                    'type' => $this->getExportTypeFromFileName($exp_file)
                ));
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

        $table->setData($data);
        $tpl->setContent($table->getHTML());
    }

    private function getExportTypeFromFileName(string $export_file)
    {
        $extension = strtoupper(pathinfo($export_file, PATHINFO_EXTENSION));
        if (in_array($extension, ['XLSX', 'CSV', 'XLS'])) {
            return $this->lng->txt('results');
        }
        return $extension;
    }

    public function download(): void
    {
        /**
         * @var $lng ilLanguage
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $file = array();
        if ($this->testrequest->isset('file') && $this->testrequest->raw('file')) {
            $file = array($this->testrequest->raw('file'));
        }

        if ($file === array()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt('no_checkbox'), true);
            $ilCtrl->redirect($this, 'listExportFiles');
        }

        if (count($file) > 1) {
            $this->tpl->setOnScreenMessage('info', $lng->txt('select_max_one_item'), true);
            $ilCtrl->redirect($this, 'listExportFiles');
        }

        $archiver = new ilTestArchiver($this->getParentGUI()->getTestObject()->getId());

        $filename = basename($file[0]);
        $exportFile = $this->obj->getExportDirectory() . '/' . $filename;
        $archiveFile = $archiver->getZipExportDirectory() . '/' . $filename;

        if (file_exists($exportFile)) {
            ilFileDelivery::deliverFileLegacy($exportFile, $filename);
        }

        if (file_exists($archiveFile)) {
            ilFileDelivery::deliverFileLegacy($archiveFile, $filename);
        }

        $ilCtrl->redirect($this, 'listExportFiles');
    }

    /**
     * Delete files
     */
    public function delete(): void
    {
        /**
         * @var $lng ilLanguage
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $archiver = new ilTestArchiver($this->getParentGUI()->getTestObject()->getId());
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
                ilFileUtils::delDir($exp_dir);
            }
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt('msg_deleted_export_files'), true);
        $ilCtrl->redirect($this, 'listExportFiles');
    }
}
