<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;

/**
 * Class MultipleChoiceQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceQuestionGUI extends QuestionFormGUI {
    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            new MultipleChoiceEditorConfiguration(),
            new MultipleChoiceScoringConfiguration()
            );
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            MultipleChoiceEditor::readConfig(),
            MultipleChoiceScoring::readConfig());
    }
    
    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        foreach (MultipleChoiceEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        foreach (MultipleChoiceScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
    
    /**
     * @param QuestionDto $question
     * @return QuestionDto
     */
    protected function processPostQuestion(QuestionDto $question) : QuestionDto
    {
        // remove image for multilines
        if (!$question->getPlayConfiguration()->getEditorConfiguration()->isSingleLine()) {
            $processed_options = new AnswerOptions();
            
            foreach ($question->getAnswerOptions()->getOptions() as $option) {
                $processed_options->addOption(
                    new AnswerOption(
                        $option->getOptionId(),
                        new ImageAndTextDisplayDefinition($option->getDisplayDefinition()->getText(), ''), 
                        $option->getScoringDefinition()));
            }
            
            $question->setAnswerOptions($processed_options);
        }
        
        return $question;
    }
}
