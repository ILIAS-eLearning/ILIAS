<?php
namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\EmptyScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MatchingEditor;

/**
 * Class MultipleChoiceScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MatchingScoring extends AbstractScoring
{
    public function score(Answer $answer): AnswerScoreDto
    {
        $given_matches = explode(";" ,$answer->getValue());
        
        $matches = [];
        $max_score = 0;
        
        foreach ($this->question->getPlayConfiguration()->getEditorConfiguration()->getMatches() as $match) {
            $key = $match[MatchingEditor::VAR_MATCH_DEFINITION] . '-' . $match[MatchingEditor::VAR_MATCH_TERM];
            $value = intval($match[MatchingEditor::VAR_MATCH_POINTS]);
            $max_score += $value;
            $matches[$key] = $value;
        };
        
        $score = 0;
        
        foreach ($given_matches as $given_match) {
            if(array_key_exists($given_match, $matches)) {
                $score += $matches[$given_match];
            }
        }
        
        
        return $this->createScoreDto($answer, $max_score, $score, $this->getAnswerFeedbackType($score,$max_score));
    }

    public static function readConfig()
    {
        return new MatchingScoringConfiguration();
    }  
    
    /**
     * @return string
     */
    public static function getScoringDefinitionClass() : string
    {
        return EmptyScoringDefinition::class;
    }
    
    public static function isComplete(Question $question): bool
    {
        return false;
    }
}