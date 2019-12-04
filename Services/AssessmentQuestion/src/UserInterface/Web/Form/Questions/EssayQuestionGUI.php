<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\EssayScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\EssayScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EssayEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EssayEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\EssayScoringDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
/**
 * Class EssayQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EssayQuestionGUI extends QuestionFormGUI {
    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            new EssayEditorConfiguration(),
            new EssayScoringConfiguration());
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            EssayEditor::readConfig(),
            EssayScoring::readConfig());
    }
    
    protected function readAnswerOptions(QuestionDto $question) : AnswerOptions {
        $count = intval($_POST[EssayScoring::VAR_ANSWERS_COUNT]);
        $selected = intval($_POST[EssayScoring::VAR_SCORING_MODE]);
        
        if ($selected !== EssayScoring::SCORING_MANUAL) {
            if ($selected === EssayScoring::SCORING_AUTOMATIC_ALL) {
                $prefix = EssayScoring::VAR_ANSWERS_ALL;
            }
            else if ($selected === EssayScoring::SCORING_AUTOMATIC_ANY) {
                $prefix = EssayScoring::VAR_ANSWERS_ANY;
            }
            else if ($selected === EssayScoring::SCORING_AUTOMATIC_ONE) {
                $prefix = EssayScoring::VAR_ANSWERS_ONE;
            }
            
            $options = new AnswerOptions();
            $i = 1; 
            
            while (array_key_exists($i . $prefix . EssayScoringDefinition::VAR_TEXT, $_POST)) {
                $options->addOption(new AnswerOption(
                        $i,
                        new EmptyDisplayDefinition(),
                        EssayScoringDefinition::getValueFromPost($i . $prefix)));
                $i += 1;
            }
        }
    
        return $options;
    }
    
    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        foreach (EssayEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        foreach (EssayScoring::generateFields(
                     $play->getScoringConfiguration(), 
                     $this->initial_question->getAnswerOptions()) as $field) {
            $this->addItem($field);
        }
    }
}