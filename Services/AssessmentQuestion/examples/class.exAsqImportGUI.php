<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

/**
 * Class exAsqImportGUI
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author            Adrian Lüthi <al@studer-raimann.ch>
 * @author            Björn Heyser <bh@bjoernheyser.de>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      exAsqImportGUI: ilAsqQuestionAuthoringGUI
 * @ilCtrl_Calls      exAsqImportGUI: ilAsqQuestionProcessingGUI
 * @ilCtrl_IsCalledBy exAsqImportGUI: exAsqExamplesGUI
 */
class exAsqImportGUI
{

    const CMD_SHOW_IMPORT_FORM = "showImportForm";
    const CMD_PROCESS_IMPORT_QUESTIONS = "processImportQuestions";
    /**
     * @var QuestionConfig
     */
    protected $question_config;


    public function __construct()
    {
        global $DIC;

        $DIC->tabs()->activateSubTab($DIC->ctrl()->getCmd(self::CMD_SHOW_IMPORT_FORM));
    }


    /**
     * execute command
     */
    function executeCommand()
    {
        global $DIC;

        switch (strtolower($DIC->ctrl()->getNextClass())) {
            default:
                switch ($DIC->ctrl()->getCmd()) {
                    case self::CMD_PROCESS_IMPORT_QUESTIONS:
                        $this->processImportQuestions();
                        break;
                    case self::CMD_SHOW_IMPORT_FORM:
                    default:
                        $this->showImportForm();
                        break;
                }
        }
    }


    public function showImportForm()
    {
        global $DIC;

        $form_gui = new ilPropertyFormGUI();
        $form_gui->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_PROCESS_IMPORT_QUESTIONS));
        $form_gui->setTitle('Import');
        $file_input = new ilFileInputGUI('import_file', 'import_file');
        $form_gui->addItem($file_input);
        $form_gui->addCommandButton(self::CMD_PROCESS_IMPORT_QUESTIONS, self::CMD_PROCESS_IMPORT_QUESTIONS, self::CMD_PROCESS_IMPORT_QUESTIONS);
        $DIC->ui()->mainTemplate()->setContent($form_gui->getHTML());
    }


    public function processImportQuestions()
    {
        global $DIC;

        //Do not us this code in prodictive environment! It's a fast coded example
        $tmp_file = $_FILES["import_file"]["tmp_name"];
        $time_stamp = time();
        $dir = ilUtil::getDataDir() . "/asq_demo/";
        $tmp_dir = $dir . $time_stamp;
        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir, 0755, true);
        }
        $zip_archive = new ZipArchive();
        $zip_archive->open($tmp_file);

        $zip_archive->extractTo($tmp_dir);
        $zip_archive->close();

        $dir_content = scandir($tmp_dir, 1);
        //$import_directory = $tmp_dir."/".$dir_content[0];

        $files = scandir($tmp_dir);

        foreach ($files as $file) {
            $qti_file_name = $tmp_dir . "/" . $file;

            if (strpos($file, 'manifest') !== false) {
                continue;
            }
            if (strpos($file, 'Test') !== false) {
                continue;
            }

            if (strpos($file, 'xml')) {
                $xmldata = simplexml_load_file($qti_file_name);
                $authoring_service = $DIC->assessment()->questionAuthoring($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
                $question_authoring = $authoring_service->question($DIC->assessment()->entityIdBuilder()->new());
                $question_authoring->importQtiQuestion($xmldata->asXML());
            }
        }
        /*
        foreach($files as $file) {
            if(strpos($file, 'qti')) {
                $qti_file_name = $import_directory."/".$file;
            }
        }
        $xmldata = simplexml_load_file($qti_file_name);

        $authoring_service = $DIC->assessment()->questionAuthoring($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
        $question_authoring = $authoring_service->question($DIC->assessment()->entityIdBuilder()->new());
        $items = $xmldata->xpath('assessment/section/item');
        foreach($items as $item) {
            $question_authoring->importQtiQuestion($item->asXML());
        }*/
    }
}