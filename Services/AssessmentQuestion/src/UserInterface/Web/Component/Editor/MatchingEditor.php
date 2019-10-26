<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ilSelectInputGUI;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
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
class MatchingEditor extends AbstractEditor {
    const VAR_SHUFFLE = 'me_shuffle';
    const VAR_THUMBNAIL = 'me_thumbnail';
    const VAR_MATCHING_MODE = 'me_matching';
    const VAR_DEFINITIONS = 'me_definitions';
    const VAR_TERMS = 'me_terms';
    const VAR_PAIRS = 'me_pairs';
    
    public function readAnswer(): string
    {}

    public function setAnswer(string $answer): void
    {}

    public function generateHtml(): string
    {}
    
    /**
     * @param AbstractConfiguration|null $config
     *
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        global $DIC;
        
        $fields = [];
        /** @var MatchingEditorConfiguration $config */

        $shuffle_answers = new ilSelectInputGUI($DIC->language()->txt('asq_label_shuffle_answers'), self::VAR_SHUFFLE);
        $shuffle_answers->setOptions([
            MatchingEditorConfiguration::SHUFFLE_NONE => $DIC->language()->txt('asq_option_shuffle_none'),
            MatchingEditorConfiguration::SHUFFLE_DEFINITIONS => $DIC->language()->txt('asq_option_shuffle_definitions'),
            MatchingEditorConfiguration::SHUFFLE_TERMS => $DIC->language()->txt('asq_option_shuffle_terms'),
            MatchingEditorConfiguration::SHUFFLE_BOTH => $DIC->language()->txt('asq_option_shuffle_both')
        ]);
        $fields[] = $shuffle_answers;
        
        $thumbnail = new ilNumberInputGUI($DIC->language()->txt('asq_label_thumbnail'), self::VAR_THUMBNAIL);
        $thumbnail->setRequired(true);
        $fields[] = $thumbnail;
        
        $matching_mode = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_matching_mode'), self::VAR_MATCHING_MODE);
        $matching_mode->addOption(new ilRadioOption($DIC->language()->txt('asq_option_one_to_one'), 
                                                    MatchingEditorConfiguration::MATCHING_ONE_TO_ONE));
        $matching_mode->addOption(new ilRadioOption($DIC->language()->txt('asq_option_many_to_one'),
                                                    MatchingEditorConfiguration::MATCHING_MANY_TO_ONE));
        $matching_mode->addOption(new ilRadioOption($DIC->language()->txt('asq_option_many_to_one'),
                                                    MatchingEditorConfiguration::MATCHING_MANY_TO_MANY));
        $fields[] = $matching_mode;
        
        return $fields;
    }
    
    public static function readConfig()
    {}
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}