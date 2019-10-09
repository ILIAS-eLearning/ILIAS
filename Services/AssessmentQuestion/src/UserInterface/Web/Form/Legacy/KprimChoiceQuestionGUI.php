<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\KprimChoiceScoring;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\KprimChoiceScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\KprimChoiceEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\KprimChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionForm;
use ilCheckboxInputGUI;

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
class KprimChoiceQuestionGUI extends LegacyFormGUIBase {
    
    const HALFPOINTS_AT = 3;
    
    /**
     * QuestionFormGUI constructor.
     *
     * @param QuestionDto $question
     */
    public function __construct($question) {
        global $DIC;
        
        while (count($question->getAnswerOptions()->getOptions()) < 4) {
            $question->getAnswerOptions()->addOption(null);
        }
        
        $answer_option_config = [
            AnswerOptionForm::OPTION_ORDER => true,
            AnswerOptionForm::OPTION_HIDE_ADD_REMOVE => true
        ];
        
        parent::__construct($question, $answer_option_config);
        
        $this->option_form->setInfo($DIC->language()->txt('asq_kprim_information'));
    }

    protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create
        (
            new KprimChoiceEditorConfiguration(),
            new KprimChoiceScoringConfiguration()
            );
    }
    
    protected function readPlayConfiguration(): QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            KprimChoiceEditor::readConfig(),
            KprimChoiceScoring::readConfig());
    }

    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        $fields = array_merge(KprimChoiceEditor::generateFields($play->getEditorConfiguration()),
                              KprimChoiceScoring::generateFields($play->getScoringConfiguration()));
        
        /** @var $old \ilTextInputGUI */
        $old = $fields[KprimChoiceScoring::VAR_HALF_POINTS];
        $new = new ilCheckboxInputGUI($old->getTitle(), KprimChoiceScoring::VAR_HALF_POINTS);
        $new->setValue(self::HALFPOINTS_AT);
        $new->setInfo($old->getInfo());
        $new->setChecked($play->getScoringConfiguration()->getHalfPointsAt() === self::HALFPOINTS_AT);
        $fields[KprimChoiceScoring::VAR_HALF_POINTS] = $new;
        
        foreach ($fields as $field) {
            $this->addItem($field);
        }
    }
}
