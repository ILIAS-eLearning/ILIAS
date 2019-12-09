<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\OrderingScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\OrderingScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\OrderingEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\OrderingEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ilNumberInputGUI;
use ilTextAreaInputGUI;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyScoringDefinition;

/**
 * Class OrderingQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OrderingTextQuestionGUI extends QuestionFormGUI {
    const VAR_ORDERING_TEXT = 'otq_text';
    
    protected function canDisplayAnswerOptions() {
        return false;
    }
    
    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            new OrderingEditorConfiguration(),
            new OrderingScoringConfiguration()
            );
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            OrderingEditorConfiguration::create(false, 
                                                !empty($_POST[OrderingEditor::VAR_MINIMUM_SIZE]) ? 
                                                    intval($_POST[OrderingEditor::VAR_MINIMUM_SIZE]) : 
                                                    null),
            OrderingScoring::readConfig());
    }
    
    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        global $DIC;
        
        $text = new ilTextAreaInputGUI($DIC->language()->txt('asq_ordering_text'), self::VAR_ORDERING_TEXT);
        $this->addItem($text);
        
        $minimum_size = new ilNumberInputGUI($DIC->language()->txt('asq_label_min_size'), OrderingEditor::VAR_MINIMUM_SIZE);
        $minimum_size->setInfo($DIC->language()->txt('asq_description_min_size'));
        $minimum_size->setSize(6);
        $this->addItem($minimum_size);
        
        $config = $play->getEditorConfiguration();
        if (!$config == null) {
            $minimum_size->setValue($config->getMinimumSize());
        }
        
        if (count($this->initial_question->getAnswerOptions()->getOptions())) {
            $question_text = [];
            
            foreach ($this->initial_question->getAnswerOptions()->getOptions() as $option) {
                $question_text[] = $option->getDisplayDefinition()->getText();
            }
            
            $text->setValue(implode(' ', $question_text));
        }
        
        foreach (OrderingScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
    
    protected function readAnswerOptions(QuestionDto $question) : AnswerOptions {
        $text_input = $_POST[self::VAR_ORDERING_TEXT];

        $options = new AnswerOptions();
        
        $i = 1;
        if (!empty($text_input)) {
            $words = explode(' ', $text_input);
            
            foreach($words as $word) {
                $options->addOption(new AnswerOption($i,
                                                     new ImageAndTextDisplayDefinition($word, ''),
                                                     new EmptyScoringDefinition()));
                $i += 1;
            }
        }
        
        return $options;
    }
}