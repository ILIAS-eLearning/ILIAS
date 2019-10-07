<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ErrorTextScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ErrorTextScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\OrderingScoring;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ErrorTextEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ErrorTextEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\NumericEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\OrderingEditor;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\NumericScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\OrderingEditorConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\OrderingScoringConfiguration;

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
class OrderingQuestionGUI extends LegacyFormGUIBase {
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
            OrderingEditorConfiguration::readConfig(),
            OrderingScoringConfiguration::readConfig());
    }
    
    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        foreach (OrderingEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        foreach (OrderingScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
}