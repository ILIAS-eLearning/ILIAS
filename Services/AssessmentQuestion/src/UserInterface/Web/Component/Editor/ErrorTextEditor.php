<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
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
        $this->answer = [];
    }
    
    /**
     * @return string
     */
    public function generateHtml() : string
    {
        $tpl = new ilTemplate("tpl.ErrorTextEditor.html", true, true, "Services/AssessmentQuestion");
        
        $tpl->setCurrentBlock('editor');
        $tpl->setVariable('ERRORTEXT_ID', $this->getPostKey());
        $tpl->setVariable('ERRORTEXT_VALUE', implode(',', $this->answer));
        $tpl->setVariable('ERRORTEXT', $this->generateErrorText());
        $tpl->parseCurrentBlock();
        
        return $tpl->get();
    }
    
    /**
     * @return string
     */
    private function getPostKey() : string {
        return $this->question->getId();
    }
    
    /**
     * @return string
     */
    private function generateErrorText() : string {
        preg_match_all('/\S+/', $this->configuration->getErrorText(), $matches);
        
        $words = $matches[0];
        
        $text = '';
        
        for ($i = 0; $i < count($words); $i++) {
            $css = 'errortext_word';
            if (in_array($i, $this->answer)) {
                $css .= ' selected';
            }
            $text .= '<span class="' . $css . '" data-index="' . $i . '">' . $words[$i] . '</span> ';
        }
        
        return $text;
    }
    
    /**
     * @return Answer
     */
    public function readAnswer() : string
    {
        $answers = explode(',', $_POST[$this->getPostKey()]);
        
        $answers = array_map(function($answer) {
            return intval($answer);
        }, $answers);
            
            return json_encode($answers);
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
        global $DIC;
        
        $fields = [];
        
        $error_text = new ilTextAreaInputGUI($DIC->language()->txt('asq_label_error_text'), self::VAR_ERROR_TEXT);
        $error_text->setInfo($DIC->language()->txt('asq_description_error_text'));
        $error_text->setRequired(true);
        $fields[] = $error_text;
        
        $text_size = new ilNumberInputGUI($DIC->language()->txt('asq_label_text_size'), self::VAR_TEXT_SIZE);
        $text_size->setRequired(true);
        $text_size->setSize(6);
        $text_size->setSuffix('%');
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
            ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_ERROR_TEXT]),
            intval($_POST[self::VAR_TEXT_SIZE]));
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}