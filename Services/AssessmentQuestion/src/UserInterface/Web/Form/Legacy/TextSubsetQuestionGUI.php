<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\TextSubsetScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\TextSubsetScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\TextSubsetEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\TextSubsetEditorConfiguration;
use ilNumberInputGUI;

/**
 * Class TextSubsetQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TextSubsetQuestionGUI extends LegacyFormGUIBase {
    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            new TextSubsetEditorConfiguration(),
            new TextSubsetScoringConfiguration()
            );
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            TextSubsetEditor::readConfig(),
            TextSubsetScoring::readConfig());
    }
    
    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        foreach (TextSubsetEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        $max_available_points = new ilNumberInputGUI($this->lang->txt('asq_label_max_points'));
        $max_available_points->setDisabled(true);
        $max_available_points->setValue(TextSubsetScoring::calculateMaxPoints($this->initial_question));
        $max_available_points->setSize(2);
        $this->addItem($max_available_points);
        
        foreach (TextSubsetScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
}
