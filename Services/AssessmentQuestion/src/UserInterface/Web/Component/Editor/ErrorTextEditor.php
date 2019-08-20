<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilTextAreaInputGUI;
use ilNumberInputGUI;
use ilTemplate;

/**
 * Class ErrorTextEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ErrorTextEditor extends AbstractEditor {
    
    const VAR_ERROR_TEXT = 'ete_error_text';
    const VAR_TEXT_SIZE = 'ete_text_size';
    
    /**
     * @var ErrorTextEditorConfiguration
     */
    private $configuration;
    /**
     * @var ?array
     */
    private $answer;
    
    public function __construct(QuestionDto $question) {
        parent::__construct($question);
        
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
    }
    
    /**
     * @return string
     */
    public function generateHtml() : string
    {
        $tpl = new ilTemplate("tpl.ErrorTextEditor.html", true, true, "Services/AssessmentQuestion");
        
        // TODO
        
        return $tpl->get();
    }
    

    /**
     * @return Answer
     */
    public function readAnswer() : string
    {
        $answer = [];
        
        //TODO
        
        return json_encode($answer);
    }
    
    /**
     * @param string $answer
     */
    public function setAnswer(string $answer) : void
    {
        $this->answer = json_decode($answer, true);
    }
    
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var ErrorTextEditorConfiguration $config */
        
        $fields = [];
        
        $error_text = new ilTextAreaInputGUI('Error Text', self::VAR_ERROR_TEXT);
        $error_text->setInfo('Please enter the error text.');
        $error_text->setRequired(true);
        $fields[] = $error_text;
        
        $text_size = new ilNumberInputGUI('Text Size', self::VAR_TEXT_SIZE);
        $text_size->setRequired(true);
        $fields[] = $text_size;
        
        if ($config !== null) {
            $error_text->setValue($config->getErrorText());
            $text_size->setValue($config->getTextSize());
        }
        
        return $fields;
    }
    
    /**
     * @return AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return ErrorTextEditorConfiguration::create(
            $_POST[self::VAR_ERROR_TEXT],
            intval($_POST[self::VAR_TEXT_SIZE]));
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}