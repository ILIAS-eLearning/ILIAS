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
class KprimChoiceScoring extends AbstractScoring {
    const VAR_POINTS = 'kcs_points';
    const VAR_HALF_POINTS = 'kcs_half_points_at';
    
    const STR_TRUE = "true";
    const STR_FALSE = "false";
    
    function score(Answer $answer) : int {
        $answers = json_decode($answer->getValue(), true);
        $count = 0;
        
        foreach ($this->question->getAnswerOptions()->getOptions() as $option) {
            /** @var KprimChoiceScoringDefinition $scoring_definition */
            $scoring_definition = $option->getScoringDefinition();
            $current_answer = $answers[$option->getOptionId()];
            if (!is_null($current_answer)) {
                if ($current_answer == self::STR_TRUE && $scoring_definition->isCorrect_value() ||
                    $current_answer == self::STR_FALSE && !$scoring_definition->isCorrect_value()) {
                    $count += 1;
                }
            }
        }
        /** @var KprimChoiceScoringConfiguration $scoring_conf */
        $scoring_conf = $this->question->getPlayConfiguration()->getScoringConfiguration();
        if ($count === count($answers)) {
            return $scoring_conf->getPoints();
        } 
        else if ($count >= $scoring_conf->getHalfPointsAt()) {
            return floor($scoring_conf->getPoints() / 2);
        } 
        else {
            return 0;
        }
    }
    
    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        $fields = [];
        
        $points = new ilNumberInputGUI('points', self::VAR_POINTS);
        $fields[] = $points;
        
        $half_points_at = new ilNumberInputGUI('half_points_at', self::VAR_HALF_POINTS);
        $fields[] = $half_points_at;
        
        if ($config !== null) {
            $points->setValue($config->getPoints());
            $half_points_at->setValue($config->getHalfPointsAt());
        }
        
        return $fields;
    }
    
    /**
     * @return ?AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {        
        return KprimChoiceScoringConfiguration::create(
            intval($_POST[self::VAR_POINTS]),
            intval($_POST[self::VAR_HALF_POINTS]));
    }
}