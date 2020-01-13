<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Questions;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ClozeScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ClozeScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ClozeEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ClozeEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;

/**
 * Class ClozeQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ClozeQuestionGUI extends QuestionFormGUI {
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {        
        return QuestionPlayConfiguration::create(
            ClozeEditor::readConfig(),
            ClozeScoring::readConfig());
    }

    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            new ClozeEditorConfiguration(),
            new ClozeScoringConfiguration());
    }

    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        foreach (ClozeEditor::generateFields($play->getEditorConfiguration()) as $field) {
            $this->addItem($field);
        }
        
        foreach (ClozeScoring::generateFields($play->getScoringConfiguration()) as $field) {
            $this->addItem($field);
        }
    }
}