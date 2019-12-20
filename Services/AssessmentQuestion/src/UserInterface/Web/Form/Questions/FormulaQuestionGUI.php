<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\FormulaEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\FormulaEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInput;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoringConfiguration;
/**
 * Class FormulaQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FormulaQuestionGUI extends QuestionFormGUI {
    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            new FormulaEditorConfiguration(),
            new FormulaScoringConfiguration());
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            FormulaEditor::readConfig(),
            FormulaScoring::readConfig());
    }
    
    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {        
        foreach (FormulaEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        foreach (FormulaScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
    
    protected function postInit() {
        global $DIC;
        $question_text = $this->getItemByPostVar(QuestionFormGUI::VAR_QUESTION);
        $question_text->setInfo($DIC->language()->txt('asq_info_question') . $this->getParsebutton());
        $question_text->setUseRte(false);
        
        $this->option_form->setInfo($DIC->language()->txt('asq_info_results'));
    }
    
    private function getParseButton() : string {
        global $DIC;
        return '<br /><input type="button" value="' . $DIC->language()->txt('asq_parse_question') . '" class="js_parse_question btn btn-default" />';
    }
    
    protected function getAnswerOptionConfiguration() {
        return [AsqTableInput::OPTION_HIDE_ADD_REMOVE => true];
    }
}