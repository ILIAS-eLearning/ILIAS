<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

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
    public function readAnswer(): string
    {}

    public function setAnswer(string $answer): void
    {}

    public function generateHtml(): string
    {}
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return ChoiceEditorDisplayDefinition::class;
    }
}