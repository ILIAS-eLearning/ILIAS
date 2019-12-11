<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class for matching question pairs
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingPair
{
    protected $arrData;

    /**
    * assAnswerMatchingPair constructor
    *
    * @param string $text Definition text
    * @param string $picture Definition picture
    * @param integer $identifier Random number identifier
    */
    public function __construct($term = null, $definition = null, $points = 0.0)
    {
        $this->arrData = array(
            'term' => $term,
            'definition' => $definition,
            'points' => $points
        );
    }

    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case "term":
            case "definition":
            case "points":
                return $this->arrData[$value];
                break;
            default:
                return null;
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            case "term":
            case "definition":
            case "points":
                $this->arrData[$key] = $value;
                break;
            default:
                break;
        }
    }
}
