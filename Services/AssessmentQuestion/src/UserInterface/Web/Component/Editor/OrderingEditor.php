<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilCheckboxInputGUI;

/**
 * Class OrderingEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OrderingEditor extends AbstractEditor {
    const VAR_VERTICAL = "oe_vertical";
    const VAR_MINIMUM_SIZE = "oe_minimum_size";
    const VAR_GEOMETRY = "oe_geometry";

    /**
     * @var OrderingEditorConfiguration
     */
    private $configuration;
    /**
     * @var ?array
     */
    private $answer;
    
    public function __construct(QuestionDto $question) {
        parent::__construct($question);
        
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
    }

    /**
     * @return string
     */
    public function generateHtml() : string
    {
        return '';
    }
    
    public function readAnswer(): string
    {
        return '';
    }
    
    public function setAnswer(string $answer): void
    {
        $this->answer = [];
    }
    
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var OrderingEditorConfiguration $config */
        
        $fields = [];
        
        $is_vertical = new ilCheckboxInputGUI('is vertical', self::VAR_VERTICAL);
        $is_vertical->setValue(true);
        $fields[] = $is_vertical;
        
        $mimimum_size = new ilNumberInputGUI('min size', self::VAR_MINIMUM_SIZE);
        $fields[] = $mimimum_size;
        
        $geometry = new ilNumberInputGUI('geometry', self::VAR_GEOMETRY);
        $geometry->setRequired(true);
        $fields[] = $geometry;
        
        if ($config !== null) {
            $is_vertical->setChecked($config->isVertical());
            $mimimum_size->setValue($config->getMinimumSize());
            $geometry->setValue($config->getGeometry());
        }
        else {
            //TODO sane default? why required?
            $geometry->setValue(100);
        }
        
        return $fields;
    }
    
    public static function readConfig()
    {
        return OrderingEditorConfiguration::create(
            boolval($_POST[self::VAR_VERTICAL]), 
            intval($_POST[self::VAR_MINIMUM_SIZE]), 
            intval($_POST[self::VAR_GEOMETRY]));
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return ImageAndTextDisplayDefinition::class;
    }
}