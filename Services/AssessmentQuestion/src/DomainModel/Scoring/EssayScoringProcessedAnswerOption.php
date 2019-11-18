<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

/**
 * Class EssayScoringProcessedAnswerOption
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EssayScoringProcessedAnswerOption { 
    /**
     * @var string[]
     */
    private $words;
    
    /**
     * @var int
     */
    private $points;
    
    public function __construct(EssayScoringDefinition $def, bool $is_case_insensitive) {
        $this->points = $def->getPoints();
        
        $text = $def->getText();
        
        if ($is_case_insensitive) {
            $text = strtoupper($text);
        }
        
        // ignore punctuation
        $this->words = explode(' ', preg_replace("#[[:punct:]]#", "", $text));
    }
    /**
     * @return string[]
     */
    public function getWords() : array
    {
        return $this->words;
    }

    /**
     * @return number
     */
    public function getPoints() : int
    {
        return $this->points;
    }
}