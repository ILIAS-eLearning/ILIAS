<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilTextInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ilTemplate;
use ILIAS\AssessmentQuestion\UserInterface\Web\ImageUploader;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class FileUploadEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FileUploadEditor extends AbstractEditor {
    
    const VAR_MAX_UPLOAD = 'fue_max_upload';
    const VAR_ALLOWED_EXTENSIONS = 'fue_extensions';
    const VAR_UPLOAD_TYPE = 'fue_type';
    
    const UPLOADPATH = 'asq/answers/';
    
    
    /**
     * @var FileUploadEditorConfiguration
     */
    private $configuration;
    /**
     * @var array
     */
    private $selected_answers;
    
    public function __construct(QuestionDto $question) {
        parent::__construct($question);
        
        $this->selected_answers = [];
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
    }
    
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var FileUploadEditorConfiguration $config */
        global $DIC;
        
        $fields = [];
        
        $max_upload = new ilNumberInputGUI($DIC->language()->txt('asq_label_max_upload'), self::VAR_MAX_UPLOAD);
        $max_upload->setInfo($DIC->language()->txt('asq_description_max_upload'));
        $fields[] = $max_upload;
        
        $allowed_extensions = new ilTextInputGUI($DIC->language()->txt('asq_label_allowed_extensions'), 
                                                 self::VAR_ALLOWED_EXTENSIONS);
        $allowed_extensions->setInfo($DIC->language()->txt('asq_description_allowed_extensions'));
        $fields[] = $allowed_extensions;
        
        $typ = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_upload_type'), self::VAR_UPLOAD_TYPE);
        $typ->addOption(new ilRadioOption($DIC->language()->txt('asq_label_single_file'), FileUploadEditorConfiguration::AMOUNT_ONE));
        $typ->addOption(new ilRadioOption($DIC->language()->txt('asq_label_many_file'), FileUploadEditorConfiguration::AMOUNT_MANY));
        $fields[] = $typ;
        
        if ($config !== null) {
            $max_upload->setValue($config->getMaximumSize());
            $allowed_extensions->setValue($config->getAllowedExtensions());
            $typ->setValue($config->getUploadType());
        }
        else {
            $typ->setValue(FileUploadEditorConfiguration::AMOUNT_ONE);
        }
        
        return $fields;
    }
    
    public function readAnswer(): string
    {
        global $DIC;
        
        if ($DIC->upload()->hasUploads() && !$DIC->upload()->hasBeenProcessed()) {
            $this->UploadNewFile();
        }
        
        $this->deleteOldFiles();
        
        return json_encode($this->selected_answers);
    }
    
    private function UploadNewFile() {
        global $DIC;
        
        $DIC->upload()->process();
        
        foreach ($DIC->upload()->getResults() as $result)
        {
            $folder = self::UPLOADPATH . $this->question->getId() . '/';
            $pathinfo = pathinfo($result->getName());
            
            if ($result && $result->getStatus()->getCode() === ProcessingStatus::OK && 
                $this->checkAllowedExtension($pathinfo['extension'])) {
                $DIC->upload()->moveOneFileTo(
                    $result,
                    $folder,
                    Location::WEB,
                    $pathinfo['basename']);
                
                $this->selected_answers[] = ILIAS_HTTP_PATH . '/' .
                                            ILIAS_WEB_DIR . '/' .
                                            CLIENT_ID .  '/' .
                                            $folder .
                                            $pathinfo['basename'];
            }
        }
    }

    private function deleteOldFiles() {
        $answers = $this->selected_answers;
        
        foreach ($answers as $key => $value) {
            if (array_key_exists($this->getPostVar() . $key, $_POST)) {
                unset($this->selected_answers[$key]);
            }
        }
    }
    
    /**
     * @param string $extension
     * @return bool
     */
    private function checkAllowedExtension(string $extension) :bool {
        return empty($this->configuration->getAllowedExtensions()) ||
               in_array($extension, explode(',', $this->configuration->getAllowedExtensions()));
    }
    
    public static function readConfig() : FileUploadEditorConfiguration
    {
        $max_upload = intval($_POST[self::VAR_MAX_UPLOAD]);
        
        if ($max_upload === 0) {
            $max_upload = null;
        }
        
        return FileUploadEditorConfiguration::create($max_upload, 
                                                     str_replace(' ', '', $_POST[self::VAR_ALLOWED_EXTENSIONS]), 
                                                     intval($_POST[self::VAR_UPLOAD_TYPE]));
    }

    public function setAnswer(string $answer): void
    {
        $this->selected_answers = json_decode($answer, true);
    }

    public function generateHtml(): string
    {
        global $DIC;
        
        $tpl = new ilTemplate("tpl.FileUploadEditor.html", true, true, "Services/AssessmentQuestion");
        $tpl->setVariable('TXT_UPLOAD_FILE', $DIC->language()->txt('asq_header_upload_file'));
        $tpl->setVariable('TXT_MAX_SIZE', 
                          sprintf($DIC->language()->txt('asq_text_max_size'), 
                                  $this->configuration->getMaximumSize() ?? 777));
        $tpl->setVariable('POST_VAR', $this->getPostVar());
        
        if (!empty($this->configuration->getAllowedExtensions())) {
            $tpl->setCurrentBlock('allowed_extensions');
            $tpl->setVariable('TXT_ALLOWED_EXTENSIONS', 
                              sprintf($DIC->language()->txt('asq_text_allowed_extensions'), 
                                      $this->configuration->getAllowedExtensions()));
            $tpl->parseCurrentBlock();
            
        }
        
        if (count($this->selected_answers) > 0) {
            $tpl->setCurrentBlock('files');

            foreach ($this->selected_answers as $key => $value) {
                $tpl->setCurrentBlock('file');
                $tpl->parseCurrentBlock('FILE_ID', $this->getPostVar() . $key);
                $tpl->parseCurrentBlock('FILENAME', $value);
                $tpl->parseCurrentBlock();
            }
            
            $tpl->setVariable('HEADER_DELETE', $DIC->language()->txt('delete'));
            $tpl->setVariable('HEADER_FILENAME', $DIC->language()->txt('filename'));
            $tpl->parseCurrentBlock();
        }
        
        return $tpl->get();
    }
    
    private function getPostVar() : string {
        return $this->question->getId();
    }

    public static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}