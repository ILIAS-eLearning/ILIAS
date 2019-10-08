<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyScoringDefinition;
use ilCheckboxInputGUI;
use ilNumberInputGUI;

/**
 * Class FileUploadScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FileUploadScoring extends AbstractScoring {
    const VAR_POINTS = 'fus_points';
    const VAR_COMPLETED_ON_UPLOAD = 'fus_completed_on_upload';
    
    const CHECKED = 'checked';
    
    function score(Answer $answer) : int {
        return 42;
    }
    
    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        global $DIC;
        
        $fields = [];
        
        $points = new ilNumberInputGUI($DIC->language()->txt('asq_label_points'), self::VAR_POINTS);
        $points->setRequired(true);
        $points->setSize(2);
        $fields[self::VAR_POINTS] = $points;
        
        $completed_by_submition = new ilCheckboxInputGUI($DIC->language()->txt('asq_label_completed_by_submition'), 
                                                         self::VAR_COMPLETED_ON_UPLOAD);
        $completed_by_submition->setInfo($DIC->language()->txt('asq_description_completed_by_submition'));
        $completed_by_submition->setValue(self::CHECKED);
        $fields[self::VAR_COMPLETED_ON_UPLOAD] = $completed_by_submition;
        
        if ($config !== null) {
            $points->setValue($config->getPoints());
            $completed_by_submition->setChecked($config->isCompletedBySubmition());
        }
        
        return $fields;
    }
    
    /**
     * @return ?AbstractConfiguration|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return FileUploadScoringConfiguration::create(
            intval($_POST[self::VAR_POINTS]),
            $_POST[self::VAR_COMPLETED_ON_UPLOAD] === self::VAR_COMPLETED_ON_UPLOAD);
    }
    
    /**
     * @return string
     */
    public static function getScoringDefinitionClass(): string {
        return EmptyScoringDefinition::class;
    }
}