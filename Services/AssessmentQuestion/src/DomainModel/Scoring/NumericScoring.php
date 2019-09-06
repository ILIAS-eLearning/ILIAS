<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ilNumberInputGUI;

/**
 * Class NumericScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class NumericScoring extends AbstractScoring
{
    const VAR_POINTS = 'ns_points';
    const VAR_LOWER_BOUND = 'ns_lower_bound';
    const VAR_UPPER_BOUND = 'ns_upper_bound';

    function score(Answer $answer) : int
    {
        /** @var NumericScoringConfiguration $scoring_conf */
        $scoring_conf = $this->question->getPlayConfiguration()->getScoringConfiguration();

        $float_answer = floatval($answer->getValue());

        if ($float_answer !== null &&
            $scoring_conf->getLowerBound() < $float_answer &&
            $scoring_conf->getUpperBound() > $float_answer) {
            return $scoring_conf->getPoints();
        } else
        {
            return 0;
        }
    }


    /**
     * @param AbstractConfiguration|null $config
     *
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var NumericScoringConfiguration $config */
        $fields = [];

        $points = new ilNumberInputGUI('points', self::VAR_POINTS);
        $fields[] = $points;

        $lower_bound = new ilNumberInputGUI('lower bound', self::VAR_LOWER_BOUND);
        $lower_bound->allowDecimals(true);
        $fields[] = $lower_bound;

        $upper_bound = new ilNumberInputGUI('upper bound', self::VAR_UPPER_BOUND);
        $upper_bound->allowDecimals(true);
        $fields[] = $upper_bound;

        if ($config !== null) {
            $points->setValue($config->getPoints());
            $lower_bound->setValue($config->getLowerBound());
            $upper_bound->setValue($config->getUpperBound());
        }

        return $fields;
    }

    public static function readConfig()
    {
        return NumericScoringConfiguration::create(
            intval($_POST[self::VAR_POINTS]),
            floatval($_POST[self::VAR_LOWER_BOUND]),
            floatval($_POST[self::VAR_UPPER_BOUND]));
    }
}