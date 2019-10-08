<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ilNumberInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyScoringDefinition;

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
        global $DIC;
        
        $fields = [];

        $points = new ilNumberInputGUI($DIC->language()->txt('asq_label_points'), self::VAR_POINTS);
        $points->setRequired(true);
        $points->setSize(2);
        $fields[self::VAR_POINTS] = $points;

        $lower_bound = new ilNumberInputGUI($DIC->language()->txt('asq_label_lower_bound'), self::VAR_LOWER_BOUND);
        $lower_bound->setRequired(true);
        $lower_bound->allowDecimals(true);
        $lower_bound->setSize(6);
        $fields[self::VAR_LOWER_BOUND] = $lower_bound;

        $upper_bound = new ilNumberInputGUI($DIC->language()->txt('asq_label_upper_bound'), self::VAR_UPPER_BOUND);
        $upper_bound->setRequired(true);
        $upper_bound->allowDecimals(true);
        $upper_bound->setSize(6);
        $fields[self::VAR_UPPER_BOUND] = $upper_bound;

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
    
    /**
     * @return string
     */
    public static function getScoringDefinitionClass(): string {
        return EmptyScoringDefinition::class;
    }
}