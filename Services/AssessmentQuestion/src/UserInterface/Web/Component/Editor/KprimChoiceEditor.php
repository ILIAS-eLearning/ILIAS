<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use Exception;
use ilCheckboxInputGUI;
use ilNumberInputGUI;
use ilSelectInputGUI;
use ilRadioGroupInputGUI;
use ilTextInputGUI;
use ilRadioOption;

/**
 * Class KprimChoiceEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class KprimChoiceEditor extends AbstractEditor {
    const VAR_SHUFFLE_ANSWERS = 'kce_shuffle';
    const VAR_SINGLE_LINE = 'kce_single_line';
    const VAR_THUMBNAIL_SIZE = 'kce_thumbnail';
    const VAR_LABEL_TYPE = 'kcd_label';
    const VAR_LABEL_TRUE = 'kce_label_true';
    const VAR_LABEL_FALSE = 'kce_label_false';
    const VAR_POINTS = 'kce_points';
    const VAR_HALF_POINTS = 'half_points_at';
   
    const STR_TRUE = "true";
    const STR_FALSE = "false";
    
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
    
    public function readAnswer(): string
    {}

    public function setAnswer(string $answer): void
    {}

    public function generateHtml(): string
    {}
    
    /**
     * 
     * @param AbstractConfiguration $config
     * @return array|NULL
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var KprimChoiceEditorConfiguration $config */
        
        $fields = [];
        
        $shuffle = new ilCheckboxInputGUI('shuffle', self::VAR_SHUFFLE_ANSWERS);
        $shuffle->setValue(1);
        $fields[] = $shuffle;
                
        $singleline = new ilSelectInputGUI('single line', self::VAR_SINGLE_LINE);
        $singleline->setOptions([self::STR_TRUE => 'Singleline', self::STR_FALSE => 'Multiline']);
        $fields[] = $singleline;
        
        $thumb_size = new ilNumberInputGUI('thumb size', self::VAR_THUMBNAIL_SIZE);
        $fields[] = $thumb_size;

        $optionLabel = KprimChoiceEditor::GenerateOptionLabelField($config);
        $fields[] = $optionLabel;
        
        $points = new ilNumberInputGUI('points', self::VAR_POINTS);
        $fields[] = $points;
        
        $half_points_at = new ilNumberInputGUI('half_points_at', self::VAR_HALF_POINTS);
        $fields[] = $half_points_at;
        
        if ($config !== null) {
            $shuffle->setChecked($config->isShuffleAnswers());
            $thumb_size->setValue($config->getThumbnailSize());
            $singleline->setValue($config->isSingleLine() ? self::STR_TRUE : self::STR_FALSE);
            $points->setValue($config->getPoints());
            $half_points_at->setValue($config->getHalfPointsAt());
        }
        
        return $fields;
    }
    
    private static function GenerateOptionLabelField(?AbstractConfiguration $config)
    {
        /** @var KprimChoiceEditorConfiguration $config */
        $optionLabel = new ilRadioGroupInputGUI('Option Labels', self::VAR_LABEL_TYPE);
        $optionLabel->setInfo('The configured phrases will be used as label for the options selectable by the participant.');
        $optionLabel->setRequired(true);

        $right_wrong = new ilRadioOption('right / wrong', self::LABEL_RIGHT_WRONG);
        $optionLabel->addOption($right_wrong);
          
        $plus_minus = new ilRadioOption('+ / -', self::LABEL_PLUS_MINUS);
        $optionLabel->addOption($plus_minus);
        
        $applicable = new ilRadioOption('applicable / not applicable', self::LABEL_APPLICABLE);
        $optionLabel->addOption($applicable);
        
        $adequate = new ilRadioOption('adequate / not adequate', self::LABEL_ADEQUATE);
        $optionLabel->addOption($adequate);
        
        $custom = new ilRadioOption('Userdefined Labels', self::LABEL_CUSTOM);
        $optionLabel->addOption($custom);
        $customLabelTrue = new ilTextInputGUI('Label for True', self::VAR_LABEL_TRUE);
        $custom->addSubItem($customLabelTrue);
        
        $customLabelFalse = new ilTextInputGUI('Label for False', self::VAR_LABEL_FALSE);
        $custom->addSubItem($customLabelFalse);
        
        if ($config !== null) {
            if($config->getLabelTrue() === self::STR_RIGHT && $config->getLabelFalse() === self::STR_WRONG) {
                $optionLabel->setValue(self::LABEL_RIGHT_WRONG);
            }
            else if ($config->getLabelTrue() === self::STR_PLUS && $config->getLabelFalse() === self::STR_MINUS) {
                $optionLabel->setValue(self::LABEL_PLUS_MINUS);
            }
            else if ($config->getLabelTrue() === self::STR_APPLICABLE && $config->getLabelFalse() === self::STR_NOT_APPLICABLE) {
                $optionLabel->setValue(self::LABEL_APPLICABLE);
            }
            else if ($config->getLabelTrue() === self::STR_ADEQUATE && $config->getLabelFalse() === self::STR_NOT_ADEQUATE) {
                $optionLabel->setValue(self::LABEL_ADEQUATE);
            } 
            else {
                $optionLabel->setValue(self::LABEL_CUSTOM);
                $customLabelTrue->setValue($config->getLabelTrue());
                $customLabelFalse->setValue($config->getLabelFalse());
            }
        }
        
        return $optionLabel;
    }

    /**
     * @return ?AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
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
        
        return KprimChoiceEditorConfiguration::create(
            boolval($_POST[self::VAR_SHUFFLE_ANSWERS]),
            boolval($_POST[self::VAR_SINGLE_LINE]),
            intval($_POST[self::VAR_THUMBNAIL_SIZE]),
            $label_true,
            $label_false,
            intval($_POST[self::VAR_POINTS]),
            intval($_POST[self::VAR_HALF_POINTS]));
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return ChoiceEditorDisplayDefinition::class;
    }
}