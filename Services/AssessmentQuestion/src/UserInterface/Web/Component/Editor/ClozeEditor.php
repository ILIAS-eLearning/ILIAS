<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ilTextAreaInputGUI;
use ilSelectInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInput;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;
use ilFormSectionHeaderGUI;

/**
 * Class ClozeEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ClozeEditor extends AbstractEditor {
    const VAR_CLOZE_TEXT = 'cze_text';
    const VAR_GAP_TYPE = 'cze_gap_type';
    const VAR_GAP_ITEMS = 'cze_gap_items';
    
    public function readAnswer(): string
    {}

    public static function readConfig()
    {
        return ClozeEditorConfiguration::create(
            ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_CLOZE_TEXT]),
            []);
    }

    public function setAnswer(string $answer): void
    {}

    public function generateHtml(): string
    {}

    public static function isComplete(Question $question): bool
    {
        return true;
    }

    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var ClozeEditorConfiguration $config */
        global $DIC;
        
        $fields = [];
        
        $cloze_text = new ilTextAreaInputGUI($DIC->language()->txt('asq_label_cloze_text'), self::VAR_CLOZE_TEXT);
        $cloze_text->setRequired(true);
        $fields[self::VAR_CLOZE_TEXT] = $cloze_text;
        
        for ($i = 1; $i <= count($config->getGaps()); $i += 1) {
            $fields = array_merge($fields, ClozeEditor::createGapFields($i, $config->getGaps()[$i - 1]));
        }
        
        if ($config !== null) {
            $cloze_text->setValue($config->getClozeText());
        }
        else {
            $fields = array_merge($fields, ClozeEditor::createGapFields(1));
        }

        return $fields;
    }
    
    private static function createGapFields(int $index, ClozeGapConfiguration $gap = null) {
        global $DIC;

        $fields = [];
        
        $spacer = new ilFormSectionHeaderGUI();
        $spacer->setTitle('');
        $fields[] = $spacer;
        
        $gap_type = new ilSelectInputGUI($DIC->language()->txt('asq_label_gap_type'), $index . self::VAR_GAP_TYPE);
        $gap_type->setOptions([ 
            ClozeGapConfiguration::TYPE_DROPDOWN => $DIC->language()->txt('asq_label_gap_type_dropdown'),
            ClozeGapConfiguration::TYPE_TEXT => $DIC->language()->txt('asq_label_gap_type_text'),
            ClozeGapConfiguration::TYPE_NUMBER => $DIC->language()->txt('asq_label_gap_type_number')
        ]);
        $fields[$index . self::VAR_GAP_TYPE] = $gap_type;
        
        $items = is_null($gap) ? [] : $gap->getItemsArray();
        
        $gap_items = new AsqTableInput($DIC->language()->txt('asq_label_gap_items'), $index .self::VAR_GAP_ITEMS, $items, [
            new AsqTableInputFieldDefinition(
                $DIC->language()->txt('asq_header_value'),
                AsqTableInputFieldDefinition::TYPE_TEXT,
                ClozeGapItem::VAR_TEXT),
            new AsqTableInputFieldDefinition(
                $DIC->language()->txt('asq_header_points'),
                AsqTableInputFieldDefinition::TYPE_TEXT,
                ClozeGapItem::VAR_POINTS)
        ]);
        $fields[$index .self::VAR_GAP_ITEMS] = $gap_items;
        
        if (!is_null($gap)) {
            $gap_type->setValue($gap->getType());
        }
        
        return $fields;
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}