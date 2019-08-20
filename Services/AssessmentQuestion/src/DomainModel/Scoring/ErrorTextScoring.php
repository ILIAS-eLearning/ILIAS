<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ilNumberInputGUI;

/**
 * Class KprimChoiceScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ErrorTextScoring extends AbstractScoring {
    const VAR_POINTS_WRONG = 'ets_points_wrong';
    
    function score(Answer $answer) : int {
        return 42;
    }
    
    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var ErrorTextScoringConfiguration $config */
        
        $fields = [];
        
        $points_wrong = new ilNumberInputGUI('points wrong', self::VAR_POINTS_WRONG);
        $fields[] = $points_wrong;
        
        if ($config !== null) {
            $points_wrong->setValue($config->getPointsWrong());
        }
        
        return $fields;
    }
    
    /**
     * @return ?AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return ErrorTextScoringConfiguration::create(intval($_POST[self::VAR_POINTS_WRONG]));
    }
}