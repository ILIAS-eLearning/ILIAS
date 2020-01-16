<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ClozeGapItem;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ClozeEditorConfiguration;

/**
 * Class ClozeScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ClozeScoring extends AbstractScoring {
    /**
     * @var ClozeEditorConfiguration
     */
    protected $configuration;
    
    /**
     * @param QuestionDto $question
     */
    public function __construct($question) {
        parent::__construct($question);
        
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
    }
    
    public function score(Answer $answer): AnswerScoreDto
    {
        $given_answer = json_decode($answer->getValue(), true);
        
        $reached_points = 0;
        $max_points = 0;
        
        for ($i = 1; $i <= count($this->configuration->getGaps()); $i += 1) {
            $gap_max = 0;
            
            /** @var $gap ClozeGapItem */
            foreach($this->configuration->getGaps()[$i - 1]->getItems() as $gap_item) {
                if ($gap_item->getPoints() > $gap_max) {
                    $gap_max = $gap_item->getPoints();
                }
                
                if ($given_answer[$i] === $gap_item->getText()) {
                    $reached_points += $gap_item->getPoints();
                }
            }
            
            $max_points += $gap_max;
        }
        
        return $this->createScoreDto($answer, $max_points, $reached_points, $this->getAnswerFeedbackType($reached_points,$max_points));
    }

    public static function readConfig()
    {
        return new ClozeScoringConfiguration();
    }

    public static function isComplete(Question $question): bool
    {
        return true;
    }
    
    /**
     * @return string
     */
    public static function getScoringDefinitionClass() : string
    {
        return EmptyScoringDefinition::class;
    }
    
}