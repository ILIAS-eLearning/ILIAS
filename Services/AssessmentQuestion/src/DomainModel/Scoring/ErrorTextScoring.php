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
        $score = 0;
        
        $selected_words = json_decode($answer->getValue(), true);
        
        foreach ($selected_words as $selected_word) {
            $wrong = true;
            
            foreach ($this->question->getAnswerOptions()->getOptions() as $option) {
                /** @var ErrorTextScoringDefinition $scoring_definition */
                $scoring_definition = $option->getScoringDefinition();
                
                if ($scoring_definition->getWrongWordIndex() === $selected_word) {
                    
                    $multi_error = false;
                    
                    for ($i = 1; $i < $scoring_definition->getWrongWordLength(); $i++) {
                        
                        $current = $scoring_definition->getWrongWordIndex() + $i;
                        
                        if (!in_array($current, $selected_words)) {
                            $multi_error = true;
                            break;
                        }
                    }
                    
                    if (!$multi_error) {
                        $score += $scoring_definition->getPoints();
                        $wrong = false;
                        break;                        
                    }
                }
            }
            
            if ($wrong) {
                $score += $this->question->getPlayConfiguration()->getScoringConfiguration()->getPointsWrong();
            }
        }
        
        return $score;
    }
    
    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var ErrorTextScoringConfiguration $config */
        global $DIC;
        
        $fields = [];
        
        $points_wrong = new ilNumberInputGUI($DIC->language()->txt('asq_label_points_wrong'), self::VAR_POINTS_WRONG);
        $points_wrong->setSize(6);
        $points_wrong->setInfo($DIC->language()->txt('asq_info_points_wrong'));
        $fields[self::VAR_POINTS_WRONG] = $points_wrong;
        
        if ($config !== null) {
            $points_wrong->setValue($config->getPointsWrong());
        }
        
        return $fields;
    }
    
    /**
     * @return ?AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return ErrorTextScoringConfiguration::create(empty($_POST[self::VAR_POINTS_WRONG]) ? null : intval($_POST[self::VAR_POINTS_WRONG]));
    }
}