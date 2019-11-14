<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;
use stdClass;

/**
 * Class FormulaScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EssayScoringDefinition extends AnswerDefinition {
    const VAR_POINTS = 'esd_points';
    const VAR_TEXT = 'esd_text';
    
    /**
     * @var ?int
     */
    protected $points;
    
    /**
     * @var ?string;
     */
    protected $text;
    
    /**
     * @param int $type
     * @param float $min
     * @param float $max
     * @param string $unit
     * @param float $multiple_of
     * @param int $points
     */
    public function __construct(?string $text, ?int $points) {
        $this->points = $points;
        $this->text = $text;
    }

    public function getPoints()
    {
        return $this->points;
    }
    
    public function getText() 
    {
        return $this->text;
    }

    public static function getFields(QuestionPlayConfiguration $play): array
    {
        // point values will be set by essayscoring directly
        return [];
    }

    public function getValues(): array
    {
        return [
            self::VAR_POINTS => $this->points,
            self::VAR_TEXT => $this->text
        ];
    }

    public static function getValueFromPost(string $index)
    {
        $pointkey = $index . self::VAR_POINTS;
        
        return new EssayScoringDefinition(ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_TEXT]),
                                          array_key_exists($pointkey, $_POST) ? intval($_POST[$pointkey]) : 0);            
    }

    public static function deserialize(stdClass $data)
    {
        return new EssayScoringDefinition($data->text, $data->points);
    }
}