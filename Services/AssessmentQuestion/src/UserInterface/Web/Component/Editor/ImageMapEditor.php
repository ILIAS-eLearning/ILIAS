<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\ImageUploader;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqImageUpload;

/**
 * Class ImageMapEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ImageMapEditor extends AbstractEditor {
    
    const VAR_IMAGE = 'ime_image';
    const VAR_MULTIPLE_CHOICE = 'ime_multiple_choice';
    
    const STR_MULTICHOICE = 'Multichoice';
    const STR_SINGLECHOICE = 'Singlechoice';
    
    /**
     * @var ImageMapEditorConfiguration
     */
    private $configuration;
    /**
     * @var array
     */
    private $selected_answers;
    
    public function __construct(QuestionDto $question) {
        parent::__construct($question);
        
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
    }
    
    /**
     * @return string
     */
    public function generateHtml() : string
    {        
        return 'image_map';
    }
    
    /**
     * @return Answer
     */
    public function readAnswer() : string
    {
        if ($this->configuration->isMultipleChoice()) {
            $result = [];
            /** @var AnswerOption $answer_option */
            foreach ($this->answer_options as $answer_option) {
                $poststring = $this->getPostName($answer_option->getOptionId());
                if (isset($_POST[$poststring])) {
                    $result[] = $_POST[$poststring];
                }
            }
            return json_encode($result);
        } else {
            return json_encode([$_POST[$this->getPostName()]]);
        }
    }
    
    
    /**
     * @param string $answer
     */
    public function setAnswer(string $answer) : void
    {
        $this->selected_answers = json_decode($answer, true);
    }
    
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var ImageMapEditorConfiguration $config */
        global $DIC;
        
        $fields = [];
        
        $mode = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_mode'), self::VAR_MULTIPLE_CHOICE);
        $mode->addOption(new ilRadioOption($DIC->language()->txt('asq_label_single_choice'), self::STR_SINGLECHOICE));
        $mode->addOption(new ilRadioOption($DIC->language()->txt('asq_label_multiple_choice'), self::STR_MULTICHOICE));
        $fields[] = $mode;
        
        $image = new AsqImageUpload($DIC->language()->txt('asq_label_mode'), self::VAR_IMAGE);
        $image->setRequired(true);
        $fields[] = $image;
        
        if ($config !== null) {
            $mode->setValue($config->isMultipleChoice() ? self::STR_MULTICHOICE : self::STR_SINGLECHOICE);
            $image->setImagePath($config->getImage());
        }
        
        return $fields;
    }
    
    /**
     * @return AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return ImageMapEditorConfiguration::create(
            ImageUploader::getInstance()->processImage(self::VAR_IMAGE),
            $_POST[self::VAR_MULTIPLE_CHOICE] === self::STR_MULTICHOICE);
    }
}