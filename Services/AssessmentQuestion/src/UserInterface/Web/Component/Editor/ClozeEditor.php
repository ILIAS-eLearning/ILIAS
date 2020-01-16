<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilTextAreaInputGUI;
use ilSelectInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInput;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;
use ilFormSectionHeaderGUI;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\ClozeScoringConfiguration;

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
    
    /**
     * @var ClozeEditorConfiguration
     */
    private $configuration;
    /**
     * @var array
     */
    private $answers;
    
    public function __construct(QuestionDto $question) {
        $this->answers = [];
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
        
        parent::__construct($question);
    }
    
    public function readAnswer(): string
    {
        $this->answers = [];
        
        for ($i = 1; $i <= count($this->configuration->getGaps()); $i += 1) {
            $this->answers[$i] = ilAsqHtmlPurifier::getInstance()->purify($_POST[$this->getPostVariable($i)]);
        }
        
        return json_encode($this->answers);
    }

    public static function readConfig()
    {
        return ClozeEditorConfiguration::create(
            ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_CLOZE_TEXT]),
            []);
    }

    public function setAnswer(string $answer): void
    {
        $this->answers = json_decode($answer, true);
    }

    public function generateHtml(): string
    {
        $output = $this->configuration->getClozeText();
        
        for ($i = 1; $i <= count($this->configuration->getGaps()); $i += 1) {
            $gap_config = $this->configuration->getGaps()[$i - 1];
            
            if ($gap_config->getType() === ClozeGapConfiguration::TYPE_DROPDOWN) {
                $output = $this->createDropdown($i, $gap_config, $output);
            }
            else if ($gap_config->getType() === ClozeGapConfiguration::TYPE_NUMBER) {
                // TODO implement number
                $output = $this->createText($i, $gap_config, $output);
            }
            else if ($gap_config->getType() === ClozeGapConfiguration::TYPE_TEXT) {
                $output = $this->createText($i, $gap_config, $output);
            }
        }
        
        return $output;
    }
    
    /**
     * @param int $index
     * @param ClozeGapConfiguration $gap_config
     * @param string $output
     * @return string
     */
    private function createDropdown(int $index, ClozeGapConfiguration $gap_config, string $output) : string{
        $name = '{' . $index . '}';
        
        $html = sprintf('<select length="20" name="%s">%s</select>',
            $this->getPostVariable($index),
            $this->createOptions($gap_config->getItems(), $index));
        
        return str_replace($name, $html, $output);
    }
    
    /**
     * @param ClozeGapItem[] $gapItems
     * @return string
     */
    private function createOptions(array $gap_items, int $index) : string {
        return implode(array_map(
            function(ClozeGapItem $gap_item) use ($index) {
                return sprintf('<option value="%1$s" %2$s>%1$s</option>', 
                               $gap_item->getText(),
                               $gap_item->getText() === $this->answers[$index] ? 'selected="selected"' : '');
            }, 
            $gap_items
        ));
    }
    
    /**
     * @param int $index
     * @param ClozeGapConfiguration $gap_config
     * @param string $output
     * @return string
     */
    private function createText(int $index, ClozeGapConfiguration $gap_config, string $output) : string {
        $name = '{' . $index . '}';
        
        $html = sprintf('<input type="text" length="20" name="%s" value="%s" />',
            $this->getPostVariable($index),
            $this->answers[$index] ?? '');
        
        return str_replace($name, $html, $output);
    }

    /**
     * @param int $index
     * @return string
     */
    private function getPostVariable(int $index) {
        return $index . $this->question->getId();
    }
    
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