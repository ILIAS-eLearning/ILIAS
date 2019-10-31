<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;

/**
 * Class ImageMapEditorDisplayDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ImageMapEditorDisplayDefinition extends AnswerDefinition {
    const VAR_TOOLTIP = 'imedd_tooltip';
    const VAR_TYPE = 'imedd_type';
    const VAR_COORDINATES = 'imedd_coordinates';
    
    const TYPE_RECTANGLE = 1;
    const TYPE_CIRCLE = 2;
    const TYPE_POLYGON = 3;

    /**
     * @var string
     */
    protected $tooltip;
    
    /**
     * @var int
     */
    protected $type;
    
    /**
     * @var string
     */
    protected $coordinates;

    public function __construct(string $tooltip, int $type, string $coordinates) {
        $this->tooltip = $tooltip;
        $this->type = $type;
        $this->coordinates = $coordinates;
    }
    
    /**
     * @return string
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }
    
    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @return string
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }
    
    public static function getFields(QuestionPlayConfiguration $play): array {
        global $DIC;
        
        $fields = [];
        
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_tooltip'),
            AsqTableInputFieldDefinition::TYPE_TEXT,
            self::VAR_TOOLTIP
            );
        
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_type'),
            AsqTableInputFieldDefinition::TYPE_DROPDOWN,
            self::VAR_TYPE,
            [
                self::TYPE_RECTANGLE => $DIC->language()->txt('asq_option_rectangle'),
                self::TYPE_CIRCLE => $DIC->language()->txt('asq_option_circle'),
                self::TYPE_POLYGON => $DIC->language()->txt('asq_option_polygon')
            ]);
        
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_coordinates'),
            AsqTableInputFieldDefinition::TYPE_LABEL,
            self::VAR_COORDINATES
            );
        
        $fields[] = new AsqTableInputFieldDefinition(
            '',
            AsqTableInputFieldDefinition::TYPE_HIDDEN,
            self::VAR_COORDINATES
            );
        
        $fields[] = new AsqTableInputFieldDefinition(
            '',
            AsqTableInputFieldDefinition::TYPE_BUTTON,
            'btn-coordinates',
            [
                'css' => 'js_select_coordinates',
                'title' => $DIC->language()->txt('asq_label_select_coordinates')
            ]);
        
        return $fields;
    }
    
    public static function getValueFromPost($index) {
        return new ImageMapEditorDisplayDefinition(
            ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_TOOLTIP]), 
            intval($_POST[$index . self::VAR_TYPE]), 
            ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_COORDINATES]));
    }
    
    public function getValues(): array {
        return [
            self::VAR_TOOLTIP => $this->tooltip,
            self::VAR_TYPE => $this->type,
            self::VAR_COORDINATES => $this->coordinates
        ];
    }
    
    
    public static function deserialize($data) {
        return new ImageMapEditorDisplayDefinition($data->tooltip, $data->type, $data->coordinates);
    }
}