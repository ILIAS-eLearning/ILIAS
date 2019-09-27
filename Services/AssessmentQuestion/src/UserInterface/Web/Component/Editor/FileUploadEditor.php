<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilTextInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;

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
    {}

    public static function readConfig() : FileUploadEditorConfiguration
    {
        return FileUploadEditorConfiguration::create(intval($_POST[self::VAR_MAX_UPLOAD]), 
                                                     $_POST[self::VAR_ALLOWED_EXTENSIONS], 
                                                     intval($_POST[self::VAR_UPLOAD_TYPE]));
    }

    public function setAnswer(string $answer): void
    {}

    public function generateHtml(): string
    {}
    
    public static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}