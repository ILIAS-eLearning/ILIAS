<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ErrorTextScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ErrorTextScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ErrorTextEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ErrorTextEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionForm;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;

/**
 * Class ErrorTextQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ErrorTextQuestionGUI extends LegacyFormGUIBase {
    /**
     * QuestionFormGUI constructor.
     *
     * @param QuestionDto $question
     */
    public function __construct($question) {
        $answer_option_config = [
            AnswerOptionForm::OPTION_HIDE_ADD_REMOVE => true,
            AnswerOptionForm::OPTION_HIDE_EMPTY => true
        ];
        
        parent::__construct($question, $answer_option_config);
    }
    
    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            ErrorTextEditorConfiguration::create('', ErrorTextEditor::DEFAULT_TEXTSIZE_PERCENT),
            new ErrorTextScoringConfiguration()
            );
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            ErrorTextEditor::readConfig(),
            ErrorTextScoring::readConfig());
    }

    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        foreach (ErrorTextEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        foreach (ErrorTextScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
}