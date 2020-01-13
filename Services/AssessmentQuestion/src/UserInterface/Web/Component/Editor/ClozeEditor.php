<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ilTextAreaInputGUI;

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
        
        if ($config !== null) {
            $cloze_text->setValue($config->getClozeText());
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