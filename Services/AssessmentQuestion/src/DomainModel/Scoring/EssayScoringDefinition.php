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
    const VAR_POINTS = 'fsd_points';
    
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
    public function __construct(?int $points, ?string $text) {
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
            self::VAR_POINTS => $this->points
        ];
    }

    public static function getValueFromPost(string $index)
    {
        return new EssayScoringDefinition(intval($_POST[$index . self::VAR_POINTS]));            
    }

    public static function deserialize(stdClass $data)
    {
        return new EssayScoringDefinition($data->points);
    }
}