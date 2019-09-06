<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ilNumberInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyScoringDefinition;

/**
 * Class OrderingScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OrderingScoring extends AbstractScoring
{
    const VAR_POINTS = 'os_points';

    function score(Answer $answer) : int
    {
        return 42;
    }


    /**
     * @param AbstractConfiguration|null $config
     *
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var OrderingScoringConfiguration $config */
        $fields = [];

        $points = new ilNumberInputGUI('points', self::VAR_POINTS);
        $points->setRequired(true);
        $fields[] = $points;

        if ($config !== null) {
            $points->setValue($config->getPoints());
        }

        return $fields;
    }

    public static function readConfig()
    {
        return NumericScoringConfiguration::create(
            intval($_POST[self::VAR_POINTS]));
    }
    
    /**
     * @return string
     */
    public static function getScoringDefinitionClass(): string {
        return EmptyScoringDefinition::class;
    }
}