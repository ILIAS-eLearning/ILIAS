<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\KprimChoiceScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\KprimChoiceEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\KprimChoiceEditorConfiguration;
use ilCheckboxInputGUI;
use ilNumberInputGUI;
use ilSelectInputGUI;

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
    const VAR_SHUFFLE_ANSWERS = 'kce_shuffle';
    const VAR_SINGLE_LINE = 'kce_single_line';
    const VAR_THUMBNAIL_SIZE = 'kce_thumbnail';
    const VAR_LABEL_TYPE = 'kcd_label';
    const VAR_POINTS = 'kcs_points';
    const VAR_HALF_POINTS = 'kcs_half_points_at';
    
    const STR_TRUE = "true";
    const STR_FALSE = "false";
 
    const VAR_LABEL_TRUE = 'kce_label_true';
    const VAR_LABEL_FALSE = 'kce_label_false';
    const LABEL_RIGHT_WRONG = "label_rw";
    const LABEL_PLUS_MINUS = "label_pm";
    const LABEL_APPLICABLE = "label_app";
    const LABEL_ADEQUATE = "label_aed";
    const LABEL_CUSTOM = "label_custom";
    
    const STR_RIGHT = 'right';
    const STR_WRONG = 'wrong';
    const STR_PLUS = '+';
    const STR_MINUS = '-';
    const STR_APPLICABLE = 'applicable';
    const STR_NOT_APPLICABLE = 'not applicable';
    const STR_ADEQUATE = 'adequate';
    const STR_NOT_ADEQUATE = 'not adequate';
    
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
        switch ($_POST[self::VAR_LABEL_TYPE]) {
            case self::LABEL_RIGHT_WRONG:
                $label_true = self::STR_RIGHT;
                $label_false = self::STR_WRONG;
                break;
            case self::LABEL_PLUS_MINUS:
                $label_true = self::STR_PLUS;
                $label_false = self::STR_MINUS;
                break;
            case self::LABEL_APPLICABLE:
                $label_true = self::STR_APPLICABLE;
                $label_false = self::STR_NOT_APPLICABLE;
                break;
            case self::LABEL_ADEQUATE:
                $label_true = self::STR_ADEQUATE;
                $label_false = self::STR_NOT_ADEQUATE;
                break;
            case self::LABEL_CUSTOM:
                $label_true = $_POST[self::VAR_LABEL_TRUE];
                $label_false = $_POST[self::VAR_LABEL_FALSE];
                break;
        }
        
        return QuestionPlayConfiguration::create
        (
            KprimChoiceEditorConfiguration::create(
                boolval($_POST[self::VAR_SHUFFLE_ANSWERS]),
                boolval($_POST[self::VAR_SINGLE_LINE]),
                intval($_POST[self::VAR_THUMBNAIL_SIZE]),
                $label_true,
                $label_false),
            KprimChoiceScoringConfiguration::create(
                intval($_POST[self::VAR_POINTS]),
                intval($_POST[self::VAR_HALF_POINTS]))
        );
    }

    protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void
    {
        /** @var KprimChoiceEditorConfiguration $editor_config */
        $editor_config = $play->getEditorConfiguration();
        
        $shuffle = new ilCheckboxInputGUI($this->lang->txt('asq_label_shuffle'), self::VAR_SHUFFLE_ANSWERS);
        $shuffle->setValue(1);
        $this->addItem($shuffle);
        
        $thumb_size = new ilNumberInputGUI($this->lang->txt('asq_label_thumb_size'), self::VAR_THUMBNAIL_SIZE);
        $thumb_size->setInfo('asq_description_thumb_size');
        $this->addItem($thumb_size);
        
        $singleline = new ilSelectInputGUI($this->lang->txt('asq_label_editor'), self::VAR_SINGLE_LINE);
        $singleline->setOptions([
            self::STR_TRUE => $this->lang->txt('asq_option_single_line'),
            self::STR_FALSE => $this->lang->txt('asq_option_multi_line')]);
        
        $this->addItem($singleline);
        
        $optionLabel = KprimChoiceEditor::GenerateOptionLabelField($editor_config);
        $this->addItem($optionLabel);
        
        if ($editor_config !== null) {
            $shuffle->setChecked($editor_config->isShuffleAnswers());
            $thumb_size->setValue($editor_config->getThumbnailSize());
            $singleline->setValue($editor_config->isSingleLine() ? self::STR_TRUE : self::STR_FALSE);
        }
        
        /** @var KprimChoiceScoringConfiguration $scoring_config */
        $scoring_config = $play->getScoringConfiguration();
        
        $points = new ilNumberInputGUI($this->lang->txt('asq_label_points'), self::VAR_POINTS);
        $points->setRequired(true);
        $this->addItem($points);
        
        $half_points_at = new ilNumberInputGUI($this->lang->txt('asq_label_half_points'), self::VAR_HALF_POINTS);
        $half_points_at->setInfo($this->lang->txt('asq_description_half_points'));
        $this->addItem($half_points_at);
        
        if ($scoring_config !== null) {
            $points->setValue($scoring_config->getPoints());
            $half_points_at->setValue($scoring_config->getHalfPointsAt());
        }
    }
}
