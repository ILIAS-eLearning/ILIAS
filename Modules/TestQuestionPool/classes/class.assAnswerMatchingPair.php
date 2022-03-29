<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class for matching question pairs
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingPair
{
    // string|assAnswerMatchingDefinition  ?
    public $term;
    public $definition;
    public float $points;

    /**
    * assAnswerMatchingPair constructor
    *
    * @param string $text Definition text
    * @param string $picture Definition picture
    * @param integer $identifier Random number identifier
    */
    public function __construct($term = null, $definition = null, $points = 0.0)
    {
        $this->term = $term;
        $this->definition = $definition;
        $this->points = $points;
    }
}
