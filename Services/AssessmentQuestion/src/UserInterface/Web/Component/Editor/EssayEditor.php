<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilTemplate;

/**
 * Class EssayEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EssayEditor extends AbstractEditor {
        
    /**
     * @var EssayEditorConfiguration
     */
    private $configuration;
    /**
     * @var string
     */
    private $answer;
    
    public function __construct(QuestionDto $question) {
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
        
        parent::__construct($question);
    }
    
    /**
     * @return string
     */
    public function generateHtml() : string
    {
        $tpl = new ilTemplate("tpl.EssayEditor.html", true, true, "Services/AssessmentQuestion");
        


        return $tpl->get();
    }
    
    /**
     * @return Answer
     */
    public function readAnswer() : string
    {
        return '';
    }
    
    /**
     * @param string $answer
     */
    public function setAnswer(string $answer) : void
    {
        $this->answer = $answer;
    }
    
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var EssayEditorConfiguration $config */
        global $DIC;
        
        $fields = [];
        
        
        return $fields;
    }
    
    /**
     * @return AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return TextSubsetEditorConfiguration::create(intval($_POST[self::VAR_REQUESTED_ANSWERS]));
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
    
    /**
     * @param Question $question
     * @return bool
     */
    public static function isComplete(Question $question): bool
    {
        // no necessary values
        return true;
    }
}